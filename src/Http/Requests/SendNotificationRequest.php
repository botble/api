<?php

namespace Botble\Api\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SendNotificationRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:500'],
            'target' => ['nullable', 'string', 'in:all,android,ios,customers'],
            'action_url' => ['nullable', 'url', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Notification title is required',
            'title.max' => 'Notification title must not exceed 100 characters',
            'message.required' => 'Notification message is required',
            'message.max' => 'Notification message must not exceed 500 characters',
            'target.in' => 'Target must be one of: all, android, ios, customers',
            'action_url.url' => 'Action URL must be a valid URL',
            'action_url.max' => 'Action URL must not exceed 255 characters',
            'image_url.url' => 'Image URL must be a valid URL',
            'image_url.max' => 'Image URL must not exceed 255 characters',
        ];
    }
}
