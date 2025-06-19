<?php

namespace Botble\Api\Http\Requests;

use Botble\Support\Http\Requests\Request;

class DeviceTokenRequest extends Request
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'user_type' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Device token is required',
            'token.string' => 'Device token must be a string',
            'token.max' => 'Device token must not exceed 255 characters',
            'platform.in' => 'Platform must be either android or ios',
            'app_version.max' => 'App version must not exceed 50 characters',
            'device_id.max' => 'Device ID must not exceed 255 characters',
            'user_type.max' => 'User type must not exceed 50 characters',
            'user_id.integer' => 'User ID must be an integer',
            'user_id.min' => 'User ID must be greater than 0',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'token' => [
                'description' => 'The FCM device token',
                'example' => 'dGhpcyBpcyBhIGZha2UgdG9rZW4gZm9yIGV4YW1wbGU',
            ],
            'platform' => [
                'description' => 'The device platform',
                'example' => 'android',
            ],
            'app_version' => [
                'description' => 'The app version',
                'example' => '1.0.0',
            ],
            'device_id' => [
                'description' => 'The unique device identifier',
                'example' => 'device-123-abc',
            ],
            'user_type' => [
                'description' => 'The user type (customer, admin)',
                'example' => 'customer',
            ],
            'user_id' => [
                'description' => 'The user ID',
                'example' => 1,
            ],
        ];
    }
}
