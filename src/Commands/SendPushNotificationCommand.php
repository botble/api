<?php

namespace Botble\Api\Commands;

use Botble\Api\Models\PushNotification;
use Botble\Api\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:push-notification:send', 'Send push notifications to mobile apps')]
class SendPushNotificationCommand extends Command
{
    protected $signature = 'cms:push-notification:send
                            {--title= : Notification title}
                            {--message= : Notification message}
                            {--type=general : Notification type (general, order, promotion, system)}
                            {--target=all : Target type (all, platform, user_type, user)}
                            {--target-value= : Target value (android/ios for platform, customer/admin for user_type, user_id for user)}
                            {--action-url= : Action URL when notification is clicked}
                            {--image-url= : Image URL for rich notifications}
                            {--data= : Additional JSON data}
                            {--schedule= : Schedule notification (Y-m-d H:i:s format)}
                            {--user-type=customer : User type when target is user (customer, admin)}
                            {--interactive : Run in interactive mode}';

    protected $description = 'Send push notifications to mobile apps with various targeting options';

    protected PushNotificationService $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        parent::__construct();
        $this->pushNotificationService = $pushNotificationService;
    }

    public function handle(): int
    {
        if ($this->option('interactive')) {
            return $this->handleInteractive();
        }

        return $this->handleNonInteractive();
    }

    protected function handleInteractive(): int
    {
        $this->info('🚀 Push Notification Sender');
        $this->line('');

        // Get notification details
        $title = $this->ask('Notification title');
        if (empty($title)) {
            $this->error('Title is required');

            return self::FAILURE;
        }

        $message = $this->ask('Notification message');
        if (empty($message)) {
            $this->error('Message is required');

            return self::FAILURE;
        }

        $type = $this->choice('Notification type', ['general', 'order', 'promotion', 'system'], 'general');

        $target = $this->choice('Target audience', ['all', 'platform', 'user_type', 'user'], 'all');

        $targetValue = null;
        if ($target === 'platform') {
            $targetValue = $this->choice('Select platform', ['android', 'ios']);
        } elseif ($target === 'user_type') {
            $targetValue = $this->choice('Select user type', ['customer', 'admin']);
        } elseif ($target === 'user') {
            $userType = $this->choice('Select user type', ['customer', 'admin']);
            $userId = $this->ask('Enter user ID');
            if (! is_numeric($userId)) {
                $this->error('User ID must be numeric');

                return self::FAILURE;
            }
            $targetValue = $userId;
        }

        $actionUrl = $this->ask('Action URL (optional)');
        $imageUrl = $this->ask('Image URL (optional)');

        $addData = $this->confirm('Add custom data?', false);
        $data = null;
        if ($addData) {
            $dataInput = $this->ask('Enter JSON data');
            if ($dataInput) {
                $data = json_decode($dataInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON data');

                    return self::FAILURE;
                }
            }
        }

        $schedule = null;
        if ($this->confirm('Schedule notification?', false)) {
            $schedule = $this->ask('Schedule time (Y-m-d H:i:s format)');
            if ($schedule && ! strtotime($schedule)) {
                $this->error('Invalid date format');

                return self::FAILURE;
            }
        }

        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_type' => $target,
            'target_value' => $targetValue,
            'action_url' => $actionUrl,
            'image_url' => $imageUrl,
            'data' => $data,
            'user_type' => $target === 'user' ? $userType : null,
        ];

        if ($schedule) {
            $notificationData['scheduled_at'] = $schedule;
        }

        return $this->sendNotification($notificationData);
    }

    protected function handleNonInteractive(): int
    {
        $title = $this->option('title');
        $message = $this->option('message');

        if (empty($title) || empty($message)) {
            $this->error('Title and message are required. Use --title and --message options or run with --interactive');

            return self::FAILURE;
        }

        $target = $this->option('target');
        $targetValue = $this->option('target-value');

        // Validate target and target-value combination
        if (in_array($target, ['platform', 'user_type', 'user']) && empty($targetValue)) {
            $this->error("Target value is required when target is '{$target}'");

            return self::FAILURE;
        }

        $data = null;
        if ($this->option('data')) {
            $data = json_decode($this->option('data'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON data');

                return self::FAILURE;
            }
        }

        $schedule = $this->option('schedule');
        if ($schedule && ! strtotime($schedule)) {
            $this->error('Invalid schedule date format');

            return self::FAILURE;
        }

        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $this->option('type'),
            'target_type' => $target,
            'target_value' => $targetValue,
            'action_url' => $this->option('action-url'),
            'image_url' => $this->option('image-url'),
            'data' => $data,
            'user_type' => $this->option('user-type'),
        ];

        if ($schedule) {
            $notificationData['scheduled_at'] = $schedule;
        }

        return $this->sendNotification($notificationData);
    }

    protected function sendNotification(array $notificationData): int
    {
        try {
            $this->line('');
            $this->info('📱 Preparing to send notification...');

            // Create notification record
            $pushNotification = PushNotification::createFromRequest($notificationData, Auth::id());

            if (isset($notificationData['scheduled_at']) && $notificationData['scheduled_at']) {
                $this->info("✅ Notification scheduled for: {$notificationData['scheduled_at']}");
                $this->info("Notification ID: {$pushNotification->id}");

                return self::SUCCESS;
            }

            // Send immediately
            $result = $this->sendBasedOnTarget($notificationData);

            $this->displayResult($result, $pushNotification);

            return $result['success'] ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");
            logger()->error('Push notification command failed', [
                'error' => $e->getMessage(),
                'data' => $notificationData,
            ]);

            return self::FAILURE;
        }
    }

    protected function sendBasedOnTarget(array $notificationData): array
    {
        $target = $notificationData['target_type'];
        $targetValue = $notificationData['target_value'];

        switch ($target) {
            case 'all':
                return $this->pushNotificationService->sendToAll($notificationData);

            case 'platform':
                return $this->pushNotificationService->sendToPlatform($targetValue, $notificationData);

            case 'user_type':
                return $this->pushNotificationService->sendToUserType($targetValue, $notificationData);

            case 'user':
                $userType = $notificationData['user_type'] ?? 'customer';

                return $this->pushNotificationService->sendToUser($userType, (int) $targetValue, $notificationData);

            default:
                throw new \InvalidArgumentException("Invalid target type: {$target}");
        }
    }

    protected function displayResult(array $result, PushNotification $pushNotification): void
    {
        $this->line('');

        if ($result['success']) {
            $this->info('✅ Notification sent successfully!');
        } else {
            $this->error('❌ Notification failed to send');
        }

        $this->table(['Metric', 'Count'], [
            ['Sent', $result['sent_count'] ?? 0],
            ['Failed', $result['failed_count'] ?? 0],
        ]);

        if (isset($result['message'])) {
            $this->line("Message: {$result['message']}");
        }

        $this->line("Notification ID: {$pushNotification->id}");
        $this->line('');
    }
}
