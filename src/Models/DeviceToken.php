<?php

namespace Botble\Api\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends BaseModel
{
    use HasUuidsOrIntegerIds;

    protected $table = 'device_tokens';

    protected $fillable = [
        'token',
        'platform',
        'app_version',
        'device_id',
        'user_type',
        'user_id',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => Carbon::now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeForUser($query, string $userType, int|string $userId)
    {
        return $query->where('user_type', $userType)->where('user_id', $userId);
    }

    public static function createOrUpdateToken(array $data): self
    {
        $token = static::query()->where('token', $data['token'])->first();

        if ($token) {
            $token->update([
                'platform' => $data['platform'] ?? $token->platform,
                'app_version' => $data['app_version'] ?? $token->app_version,
                'device_id' => $data['device_id'] ?? $token->device_id,
                'user_type' => $data['user_type'] ?? $token->user_type,
                'user_id' => $data['user_id'] ?? $token->user_id,
                'is_active' => true,
                'last_used_at' => Carbon::now(),
            ]);
        } else {
            $token = static::query()->create(array_merge($data, [
                'is_active' => true,
                'last_used_at' => Carbon::now(),
            ]));
        }

        return $token;
    }

    public static function removeInactiveTokens(int $days = 30): int
    {
        return static::query()->where('last_used_at', '<', Carbon::now()->subDays($days))
            ->orWhere('is_active', false)
            ->delete();
    }
}
