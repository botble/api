<?php

namespace Botble\Api\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PushNotificationRecipient extends BaseModel
{
    use HasUuidsOrIntegerIds;

    protected $table = 'push_notification_recipients';

    protected $fillable = [
        'push_notification_id',
        'user_type',
        'user_id',
        'device_token',
        'platform',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'clicked_at',
        'fcm_response',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'fcm_response' => 'array',
    ];

    public function pushNotification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class);
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function scopeForUser($query, string $userType, int|string $userId)
    {
        return $query->where('user_type', $userType)->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function markAsDelivered(): void
    {
        if (! $this->delivered_at) {
            $this->update([
                'status' => 'delivered',
                'delivered_at' => Carbon::now(),
            ]);

            // Update parent notification delivered count
            $this->pushNotification->incrementDeliveredCount();
        }
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update([
                'status' => 'read',
                'read_at' => Carbon::now(),
            ]);

            // Update parent notification read count
            $this->pushNotification->incrementReadCount();
        }
    }

    public function markAsClicked(): void
    {
        $this->update([
            'clicked_at' => Carbon::now(),
        ]);

        // Also mark as read if not already
        if (! $this->read_at) {
            $this->markAsRead();
        }
    }

    public function markAsFailed(?string $errorMessage = null, ?array $fcmResponse = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'fcm_response' => $fcmResponse,
        ]);
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    public function isDelivered(): bool
    {
        return ! is_null($this->delivered_at);
    }

    public function isClicked(): bool
    {
        return ! is_null($this->clicked_at);
    }

    public static function createForUser(
        int|string $pushNotificationId,
        string $userType,
        int|string $userId,
        ?string $deviceToken = null,
        ?string $platform = null
    ): self {
        return static::query()->create([
            'push_notification_id' => $pushNotificationId,
            'user_type' => $userType,
            'user_id' => $userId,
            'device_token' => $deviceToken,
            'platform' => $platform,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
