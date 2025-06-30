<?php

namespace Botble\Api\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserSetting extends BaseModel
{
    use HasUuidsOrIntegerIds;

    protected $table = 'user_settings';

    protected $fillable = [
        'user_type',
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Get a setting value for a user
     */
    public static function getUserSetting(string $userType, int $userId, string $key, $default = null)
    {
        $setting = static::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value for a user
     */
    public static function setUserSetting(string $userType, int $userId, string $key, $value): self
    {
        return static::query()->updateOrCreate(
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Get all settings for a user
     */
    public static function getUserSettings(string $userType, int $userId): array
    {
        $settings = static::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Set multiple settings for a user
     */
    public static function setUserSettings(string $userType, int $userId, array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::setUserSetting($userType, $userId, $key, $value);
        }
    }

    /**
     * Delete a setting for a user
     */
    public static function deleteUserSetting(string $userType, int $userId, string $key): bool
    {
        return static::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('key', $key)
            ->delete() > 0;
    }

    /**
     * Get biometric setting for a user
     */
    public static function getBiometricEnabled(string $userType, int $userId): bool
    {
        return (bool) static::getUserSetting($userType, $userId, 'biometric_enabled', false);
    }

    /**
     * Set biometric setting for a user
     */
    public static function setBiometricEnabled(string $userType, int $userId, bool $enabled): self
    {
        return static::setUserSetting($userType, $userId, 'biometric_enabled', $enabled);
    }
}
