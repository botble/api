<?php

namespace Botble\Api\Http\Requests;

use Botble\Support\Http\Requests\Request;

class VerifyEmailRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'min:6', 'max:60'],
            'token' => ['required', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The email address to verify',
                'example' => 'john.smith@example.com',
            ],
            'token' => [
                'description' => 'The verification token',
                'example' => 'abc123def456',
            ],
        ];
    }
}
