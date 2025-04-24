<?php

namespace Botble\Api\Http\Requests;

use Botble\Api\Facades\ApiHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'last_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'name' => ['nullable', 'required_without:first_name', 'string', 'max:120', 'min:2'],
            'email' => ['required', 'email', Rule::unique(ApiHelper::getTable()), 'max:60', 'min:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', ...BaseHelper::getPhoneValidationRule(true)],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'first_name' => [
                'description' => 'The first name of the user',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'The last name of the user',
                'example' => 'Smith',
            ],
            'name' => [
                'description' => 'The full name of the user (can be used instead of first_name and last_name)',
                'example' => 'John Smith',
            ],
            'email' => [
                'description' => 'The email address of the user',
                'example' => 'john.smith@example.com',
            ],
            'password' => [
                'description' => 'The password for the account',
                'example' => 'secure_password',
            ],
            'password_confirmation' => [
                'description' => 'The password confirmation (must match password)',
                'example' => 'secure_password',
            ],
            'phone' => [
                'description' => 'The phone number of the user (optional)',
                'example' => '+1234567890',
            ],
        ];
    }
}
