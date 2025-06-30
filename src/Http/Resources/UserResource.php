<?php

namespace Botble\Api\Http\Resources;

use Botble\ACL\Models\User;
use Botble\Api\Models\UserSetting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
        ];

        if ($this->first_name || $this->last_name) {
            $data['first_name'] = $this->first_name;
            $data['last_name'] = $this->last_name;
        }

        // Get user type for settings
        $userType = $this->getUserType();

        // Get user settings
        $settings = UserSetting::getUserSettings($userType, $this->getKey());

        // Set default values for common settings
        $defaultSettings = [
            'biometric_enabled' => false,
            'notification_enabled' => true,
            'language' => 'en',
            'currency' => 'USD',
            'theme' => 'light',
            'timezone' => 'UTC',
        ];

        $settings = array_merge($defaultSettings, $settings);

        return [
            ...$data,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'description' => $this->description,
            'settings' => $settings,
        ];
    }

    /**
     * Get user type based on model class
     */
    protected function getUserType(): string
    {
        $class = get_class($this->resource);

        if (str_contains($class, 'Customer')) {
            return 'customer';
        }

        if (str_contains($class, 'User')) {
            return 'admin';
        }

        return 'user';
    }
}
