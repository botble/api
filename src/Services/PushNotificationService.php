<?php

namespace Botble\Api\Services;

use Botble\Api\Models\DeviceToken;
use Botble\Api\Models\PushNotification;
use Botble\Api\Models\PushNotificationRecipient;
use Carbon\Carbon;
use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected string $fcmV1Url = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';

    protected ?string $accessToken = null;

    public function sendToAll(array $notification): array
    {
        $tokens = DeviceToken::query()->active()->get();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active device tokens found',
                'sent_count' => 0,
                'failed_count' => 0,
            ];
        }

        // Save notification to database
        $pushNotification = $this->saveNotificationToDatabase($notification, 'all');

        return $this->sendToDeviceTokens($tokens, $notification, $pushNotification);
    }

    public function sendToPlatform(string $platform, array $notification): array
    {
        $tokens = DeviceToken::query()
            ->active()
            ->forPlatform($platform)
            ->get();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'message' => "No active {$platform} device tokens found",
                'sent_count' => 0,
                'failed_count' => 0,
            ];
        }

        // Save notification to database
        $pushNotification = $this->saveNotificationToDatabase($notification, 'platform', $platform);

        return $this->sendToDeviceTokens($tokens, $notification, $pushNotification);
    }

    public function sendToUserType(string $userType, array $notification): array
    {
        $tokens = DeviceToken::query()
            ->active()
            ->where('user_type', $userType)
            ->get();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'message' => "No active device tokens found for user type: {$userType}",
                'sent_count' => 0,
                'failed_count' => 0,
            ];
        }

        // Save notification to database
        $pushNotification = $this->saveNotificationToDatabase($notification, 'user_type', $userType);

        return $this->sendToDeviceTokens($tokens, $notification, $pushNotification);
    }

    public function sendToUser(string $userType, int $userId, array $notification): array
    {
        $tokens = DeviceToken::query()
            ->active()
            ->forUser($userType, $userId)
            ->get();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'message' => "No active device tokens found for user: {$userType}#{$userId}",
                'sent_count' => 0,
                'failed_count' => 0,
            ];
        }

        // Save notification to database
        $pushNotification = $this->saveNotificationToDatabase($notification, 'user', $userId);

        return $this->sendToDeviceTokens($tokens, $notification, $pushNotification);
    }

    public function sendToDeviceTokens($deviceTokens, array $notification, PushNotification $pushNotification): array
    {
        $tokens = $deviceTokens->pluck('token')->toArray();

        $projectId = setting('fcm_project_id');
        $serviceAccountPath = setting('fcm_service_account_path');

        if (empty($projectId) || empty($serviceAccountPath)) {
            $pushNotification->markAsFailed('FCM configuration is incomplete');

            return [
                'success' => false,
                'message' => 'FCM project ID or service account file is not configured',
                'sent_count' => 0,
                'failed_count' => count($tokens),
            ];
        }

        // Get access token for FCM v1 API
        try {
            $accessToken = $this->getAccessToken($serviceAccountPath);
        } catch (Exception $e) {
            $pushNotification->markAsFailed('Failed to get FCM access token: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to authenticate with FCM: ' . $e->getMessage(),
                'sent_count' => 0,
                'failed_count' => count($tokens),
            ];
        }

        // Create recipient records for tracking
        foreach ($deviceTokens as $deviceToken) {
            PushNotificationRecipient::createForUser(
                $pushNotification->id,
                $deviceToken->user_type ?? 'unknown',
                $deviceToken->user_id ?? 0,
                $deviceToken->token,
                $deviceToken->platform
            );
        }

        $sentCount = 0;
        $failedCount = 0;
        $invalidTokens = [];

        // FCM v1 API sends to individual tokens, not batches
        foreach ($deviceTokens as $deviceToken) {
            $result = $this->sendToSingleToken($deviceToken->token, $notification, $accessToken, $projectId, $deviceToken, $pushNotification);
            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
                if ($result['invalid_token']) {
                    $invalidTokens[] = $deviceToken->token;
                }
            }
        }

        // Remove invalid tokens
        if (! empty($invalidTokens)) {
            DeviceToken::query()->whereIn('token', $invalidTokens)->delete();
        }

        // Update notification status
        $pushNotification->markAsSent($sentCount, $failedCount);

        return [
            'success' => $sentCount > 0,
            'message' => $sentCount > 0
                ? "Successfully sent to {$sentCount} devices"
                : 'Failed to send to any devices',
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'removed_invalid_tokens' => count($invalidTokens),
        ];
    }

    protected function sendToSingleToken(string $token, array $notification, string $accessToken, string $projectId, $deviceToken = null, ?PushNotification $pushNotification = null): array
    {
        try {
            $url = str_replace('{project_id}', $projectId, $this->fcmV1Url);

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['message'],
                    ],
                    'data' => [
                        'title' => $notification['title'],
                        'message' => $notification['message'],
                        'action_url' => $notification['action_url'] ?? '',
                        'image_url' => $notification['image_url'] ?? '',
                        'type' => $notification['type'] ?? 'general',
                        'sent_at' => Carbon::now()->toISOString(),
                    ],
                ],
            ];

            // Add image to notification if provided
            if (! empty($notification['image_url'])) {
                $payload['message']['notification']['image'] = $notification['image_url'];
            }

            // Add Android-specific configuration
            $payload['message']['android'] = [
                'notification' => [
                    'click_action' => $notification['action_url'] ?? '',
                    'sound' => 'default',
                ],
                'priority' => 'high',
            ];

            // Add iOS-specific configuration
            $payload['message']['apns'] = [
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $notification['title'],
                            'body' => $notification['message'],
                        ],
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                ],
            ];

            if (! empty($notification['action_url'])) {
                $payload['message']['apns']['payload']['aps']['category'] = 'OPEN_URL';
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                // Update recipient status if available
                if ($deviceToken && $pushNotification) {
                    $recipient = PushNotificationRecipient::query()
                        ->where('push_notification_id', $pushNotification->id)
                        ->where('device_token', $token)
                        ->first();

                    if ($recipient) {
                        $recipient->update([
                            'status' => 'delivered',
                            'delivered_at' => Carbon::now(),
                            'fcm_response' => $response->json(),
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'invalid_token' => false,
                ];
            } else {
                $responseData = $response->json();
                $isInvalidToken = $this->isInvalidTokenError($responseData);

                // Update recipient status if available
                if ($deviceToken && $pushNotification) {
                    $recipient = PushNotificationRecipient::query()
                        ->where('push_notification_id', $pushNotification->id)
                        ->where('device_token', $token)
                        ->first();

                    if ($recipient) {
                        $recipient->markAsFailed(
                            $responseData['error']['message'] ?? 'Unknown error',
                            $responseData
                        );
                    }
                }

                Log::error('FCM v1 request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'token' => substr($token, 0, 20) . '...',
                ]);

                return [
                    'success' => false,
                    'invalid_token' => $isInvalidToken,
                ];
            }
        } catch (Exception $e) {
            Log::error('FCM v1 send error: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
                'notification' => $notification,
            ]);

            return [
                'success' => false,
                'invalid_token' => false,
            ];
        }
    }

    protected function getAccessToken(string $serviceAccountPath): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            // Check if the path is a full path or relative to storage
            if (! file_exists($serviceAccountPath)) {
                $serviceAccountPath = storage_path('app/' . $serviceAccountPath);
            }

            if (! file_exists($serviceAccountPath)) {
                throw new Exception('Service account file not found: ' . $serviceAccountPath);
            }

            $serviceAccountJson = json_decode(file_get_contents($serviceAccountPath), true);

            if (! $serviceAccountJson) {
                throw new Exception('Invalid service account JSON file');
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $serviceAccountJson
            );

            $httpHandler = HttpHandlerFactory::build();
            $token = $credentials->fetchAuthToken($httpHandler);

            if (! isset($token['access_token'])) {
                throw new Exception('Failed to get access token from service account');
            }

            $this->accessToken = $token['access_token'];

            return $this->accessToken;

        } catch (Exception $e) {
            Log::error('Failed to get FCM access token: ' . $e->getMessage());

            throw $e;
        }
    }

    protected function isInvalidTokenError(array $responseData): bool
    {
        if (! isset($responseData['error']['details'])) {
            return false;
        }

        foreach ($responseData['error']['details'] as $detail) {
            if (isset($detail['errorCode']) &&
                in_array($detail['errorCode'], ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                return true;
            }
        }

        // Also check the main error code
        if (isset($responseData['error']['status']) &&
            in_array($responseData['error']['status'], ['NOT_FOUND', 'INVALID_ARGUMENT'])) {
            return true;
        }

        return false;
    }

    public function validateNotification(array $notification): array
    {
        $errors = [];

        if (empty($notification['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($notification['title']) > 100) {
            $errors[] = 'Title must not exceed 100 characters';
        }

        if (empty($notification['message'])) {
            $errors[] = 'Message is required';
        } elseif (strlen($notification['message']) > 500) {
            $errors[] = 'Message must not exceed 500 characters';
        }

        if (! empty($notification['action_url']) && ! filter_var($notification['action_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Action URL must be a valid URL';
        }

        if (! empty($notification['image_url']) && ! filter_var($notification['image_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Image URL must be a valid URL';
        }

        return $errors;
    }

    public function getDeviceTokensCount(): array
    {
        return [
            'total' => DeviceToken::query()->active()->count(),
            'android' => DeviceToken::query()->active()->forPlatform('android')->count(),
            'ios' => DeviceToken::query()->active()->forPlatform('ios')->count(),
            'customers' => DeviceToken::query()->active()->where('user_type', 'customer')->count(),
        ];
    }

    protected function saveNotificationToDatabase(array $notification, string $targetType, ?string $targetValue = null): PushNotification
    {
        return PushNotification::createFromRequest([
            'title' => $notification['title'],
            'message' => $notification['message'],
            'type' => $notification['type'] ?? 'general',
            'target_type' => $targetType,
            'target_value' => $targetValue,
            'action_url' => $notification['action_url'] ?? null,
            'image_url' => $notification['image_url'] ?? null,
            'data' => $notification['data'] ?? null,
        ], Auth::id());
    }
}
