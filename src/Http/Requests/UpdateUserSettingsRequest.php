<?php

namespace Botble\Api\Http\Requests;

class UpdateUserSettingsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'biometric_enabled' => ['sometimes', 'boolean'],
            'notification_enabled' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'string', 'max:10'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'theme' => ['sometimes', 'string', 'in:light,dark,auto'],
            'timezone' => ['sometimes', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'biometric_enabled.boolean' => 'Biometric enabled must be true or false.',
            'notification_enabled.boolean' => 'Notification enabled must be true or false.',
            'language.string' => 'Language must be a valid string.',
            'language.max' => 'Language must not exceed 10 characters.',
            'currency.string' => 'Currency must be a valid string.',
            'currency.max' => 'Currency must not exceed 10 characters.',
            'theme.in' => 'Theme must be one of: light, dark, auto.',
            'timezone.string' => 'Timezone must be a valid string.',
            'timezone.max' => 'Timezone must not exceed 50 characters.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'biometric_enabled' => [
                'description' => 'Enable or disable biometric authentication',
                'example' => true,
            ],
            'notification_enabled' => [
                'description' => 'Enable or disable push notifications',
                'example' => true,
            ],
            'language' => [
                'description' => 'Preferred language code',
                'example' => 'en',
            ],
            'currency' => [
                'description' => 'Preferred currency code',
                'example' => 'USD',
            ],
            'theme' => [
                'description' => 'Application theme preference',
                'example' => 'light',
            ],
            'timezone' => [
                'description' => 'User timezone',
                'example' => 'America/New_York',
            ],
        ];
    }
}
