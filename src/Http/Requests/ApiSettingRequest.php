<?php

namespace Botble\Api\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ApiSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'api_enabled' => [new OnOffRule()],
            'api_key' => ['nullable', 'string', 'max:255'],
            'push_notifications_enabled' => [new OnOffRule()],
            'fcm_project_id' => ['nullable', 'string', 'max:255'],
            'fcm_service_account_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'api_enabled' => [
                'description' => 'Enable or disable the API',
                'example' => 'on',
            ],
            'api_key' => [
                'description' => 'API key for authentication (optional)',
                'example' => 'your-secret-api-key',
            ],
            'push_notifications_enabled' => [
                'description' => 'Enable or disable push notifications',
                'example' => 'on',
            ],
            'fcm_project_id' => [
                'description' => 'Firebase project ID',
                'example' => 'my-firebase-project',
            ],
            'fcm_service_account_path' => [
                'description' => 'Path to Firebase service account JSON file',
                'example' => 'firebase/service-account.json',
            ],
        ];
    }
}
