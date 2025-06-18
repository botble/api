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
        ];
    }
}
