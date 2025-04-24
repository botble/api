<?php

namespace Botble\Api\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ForgotPasswordRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'min:6', 'max:60'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The email address of the user',
                'example' => 'john.smith@example.com',
            ],
        ];
    }
}
