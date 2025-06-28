<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Forms\Settings\ApiSettingForm;
use Botble\Api\Http\Requests\ApiSettingRequest;
use Botble\Api\Http\Requests\SendNotificationRequest;
use Botble\Api\Services\PushNotificationService;
use Botble\Api\Tables\SanctumTokenTable;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiController extends SettingController
{
    public function edit(SanctumTokenTable $sanctumTokenTable)
    {
        $this->pageTitle(trans('packages/api::api.settings'));

        $this->breadcrumb()
            ->add(trans('core/setting::setting.title'), route('settings.index'))
            ->add(trans('packages/api::api.settings'));

        Assets::addScriptsDirectly('vendor/core/packages/api/js/api-settings.js')
            ->addStylesDirectly('vendor/core/packages/api/css/api-settings.css');

        $form = ApiSettingForm::create();

        $sanctumTokenTable->setAjaxUrl(route('api.sanctum-token.index'));

        // Add translations for JavaScript
        $translations = [
            'api_key_generated' => trans('packages/api::api.api_key_generated'),
            'api_key_copied' => trans('packages/api::api.api_key_copied'),
            'api_key_edit_enabled' => trans('packages/api::api.api_key_edit_enabled'),
            'notification_sending' => trans('packages/api::api.notification_sending'),
            'notification_sent_success' => trans('packages/api::api.notification_sent_success'),
            'notification_sent_error' => trans('packages/api::api.notification_sent_error'),
            'file_uploaded_successfully' => trans('packages/api::api.file_uploaded_successfully'),
            'file_removed_successfully' => trans('packages/api::api.file_removed_successfully'),
            'invalid_json_file' => trans('packages/api::api.invalid_json_file'),
            'file_upload_error' => trans('packages/api::api.file_upload_error'),
            'file_remove_error' => trans('packages/api::api.file_remove_error'),
            'send_notification' => trans('packages/api::api.send_notification'),
            'your_api_key_here' => trans('packages/api::api.your_api_key_here'),
            'file_size_too_large' => trans('packages/api::api.file_size_too_large'),
            'confirm_remove_service_account' => trans('packages/api::api.confirm_remove_service_account'),
            'service_account_file_label' => trans('packages/api::api.service_account_file_label'),
            'just_uploaded' => trans('packages/api::api.just_uploaded'),
            'service_account_not_uploaded' => trans('packages/api::api.service_account_not_uploaded'),
            'please_enter_notification_title' => trans('packages/api::api.please_enter_notification_title'),
            'please_enter_notification_message' => trans('packages/api::api.please_enter_notification_message'),
            'notification_error_occurred' => trans('packages/api::api.notification_error_occurred'),
            'sent_to_devices' => trans('packages/api::api.sent_to_devices'),
            'failed_devices' => trans('packages/api::api.failed_devices'),
            'will_send_to_devices' => trans('packages/api::api.will_send_to_devices'),
            'close' => trans('packages/api::api.close'),
        ];

        return view('packages/api::settings', compact('form', 'sanctumTokenTable', 'translations'));
    }

    public function update(ApiSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }

    public function sendNotification(SendNotificationRequest $request, BaseHttpResponse $response)
    {
        $pushService = new PushNotificationService();

        // Validate notification data
        $errors = $pushService->validateNotification($request->validated());
        if (! empty($errors)) {
            return $response
                ->setError()
                ->setMessage('Validation failed: ' . implode(', ', $errors))
                ->setCode(422);
        }

        $notification = [
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'action_url' => $request->input('action_url'),
            'image_url' => $request->input('image_url'),
        ];

        $target = $request->input('target', 'all');

        // Send notification based on target
        $result = match ($target) {
            'android' => $pushService->sendToPlatform('android', $notification),
            'ios' => $pushService->sendToPlatform('ios', $notification),
            'customers' => $pushService->sendToUserType('customer', $notification),
            default => $pushService->sendToAll($notification),
        };

        if ($result['success']) {
            return $response
                ->setData($result)
                ->setMessage($result['message']);
        } else {
            return $response
                ->setError()
                ->setData($result)
                ->setMessage($result['message'])
                ->setCode(400);
        }
    }

    public function getDeviceTokensStats(BaseHttpResponse $response)
    {
        $pushService = new PushNotificationService();
        $stats = $pushService->getDeviceTokensCount();

        return $response->setData($stats);
    }

    public function uploadServiceAccount(Request $request, BaseHttpResponse $response)
    {
        $request->validate([
            'service_account_file' => ['required', 'file', 'mimes:json', 'max:2048'], // 2MB max
        ], [
            'service_account_file.required' => 'Please select a service account file.',
            'service_account_file.file' => 'The uploaded file is invalid.',
            'service_account_file.mimes' => 'The service account file must be a JSON file.',
            'service_account_file.max' => 'The service account file must not exceed 2MB.',
        ]);

        try {
            $file = $request->file('service_account_file');

            // Validate JSON content
            $content = file_get_contents($file->getPathname());
            $json = json_decode($content, true);

            if (! $json) {
                return $response
                    ->setError()
                    ->setMessage('Invalid JSON file format')
                    ->setCode(422);
            }

            if (! isset($json['type']) || $json['type'] !== 'service_account') {
                return $response
                    ->setError()
                    ->setMessage('This is not a valid Firebase service account JSON file')
                    ->setCode(422);
            }

            // Required fields for Firebase service account
            $requiredFields = ['project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($json[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (! empty($missingFields)) {
                return $response
                    ->setError()
                    ->setMessage('Missing required fields in service account file: ' . implode(', ', $missingFields))
                    ->setCode(422);
            }

            // Remove old service account file if exists
            $oldPath = setting('fcm_service_account_path');
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
            }

            // Create firebase directory if it doesn't exist
            if (! Storage::disk('local')->exists('firebase')) {
                Storage::disk('local')->makeDirectory('firebase');
            }

            // Generate filename with project ID for better identification
            $projectId = $json['project_id'];
            $timestamp = time();
            $filename = "firebase/service-account-{$projectId}-{$timestamp}.json";

            // Store the file
            Storage::disk('local')->put($filename, $content);

            // Update the setting
            setting()->set('fcm_service_account_path', $filename)->save();

            return $response
                ->setData([
                    'path' => $filename,
                    'filename' => basename($filename),
                    'project_id' => $projectId,
                    'client_email' => $json['client_email'],
                ])
                ->setMessage('Service account file uploaded and configured successfully');

        } catch (Exception $e) {
            logger()->error('Failed to upload service account file', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to upload service account file: ' . $e->getMessage())
                ->setCode(500);
        }
    }

    public function removeServiceAccount(BaseHttpResponse $response)
    {
        try {
            $currentPath = setting('fcm_service_account_path');

            if ($currentPath && Storage::disk('local')->exists($currentPath)) {
                Storage::disk('local')->delete($currentPath);
            }

            // Clear the setting
            setting()->set('fcm_service_account_path', '')->save();

            return $response
                ->setMessage('Service account file removed successfully');

        } catch (Exception $e) {
            logger()->error('Failed to remove service account file', [
                'error' => $e->getMessage(),
                'path' => setting('fcm_service_account_path'),
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to remove service account file: ' . $e->getMessage())
                ->setCode(500);
        }
    }
}
