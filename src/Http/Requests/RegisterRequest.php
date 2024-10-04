<?php

namespace Botble\Api\Http\Requests;

use Botble\Api\Facades\ApiHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'last_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'name' => ['nullable', 'required_without:first_name', 'string', 'max:120', 'min:2'],
            'email' => 'required|max:60|min:6|email|unique:' . ApiHelper::getTable(),
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', ...BaseHelper::getPhoneValidationRule(true)],
        ];
    }

    public function bodyParameters()
    {
        return [
            'first_name' => [
                'example' => 'e.g: John',
            ],
            'last_name' => [
                'example' => 'e.g: Smith',
            ],
            'email' => [
                'example' => 'e.g: abc@example.com',
            ],
        ];
    }
}
