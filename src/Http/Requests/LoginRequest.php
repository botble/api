<?php

namespace Tiryaq\Api\Http\Requests;

use Tiryaq\Support\Http\Requests\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The email address of the user',
                'example' => 'john.smith@example.com',
            ],
            'password' => [
                'description' => 'The password for the account',
                'example' => 'secure_password',
            ],
        ];
    }
}
