<?php

namespace Botble\Api\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushNotification extends BaseModel
{
    use HasUuidsOrIntegerIds;

    protected $table = 'push_notifications';

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_type',
        'target_value',
        'action_url',
        'image_url',
        'data',
        'status',
        'sent_count',
        'failed_count',
        'delivered_count',
        'read_count',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'delivered_count' => 'integer',
        'read_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (PushNotification $pushNotification) {
            $pushNotification->recipients()->delete();
        });
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(PushNotificationRecipient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function scopeForUser($query, string $userType, int $userId)
    {
        return $query->whereHas('recipients', function ($q) use ($userType, $userId) {
            $q->where('user_type', $userType)->where('user_id', $userId);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', Carbon::now());
    }

    public function markAsSent(int $sentCount = 0, int $failedCount = 0): void
    {
        $this->update([
            'status' => 'sent',
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'sent_at' => Carbon::now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'sent_at' => Carbon::now(),
        ]);
    }

    public function incrementDeliveredCount(): void
    {
        $this->increment('delivered_count');
    }

    public function incrementReadCount(): void
    {
        $this->increment('read_count');
    }

    public function getDeliveryRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getReadRate(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }

        return round(($this->read_count / $this->delivered_count) * 100, 2);
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    public function canBeSent(): bool
    {
        return $this->status == 'scheduled' &&
               (! $this->scheduled_at || $this->scheduled_at->isPast());
    }

    public static function createFromRequest(array $data, ?int $createdBy = null): self
    {
        return static::query()->create([
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'general',
            'target_type' => $data['target_type'] ?? 'all',
            'target_value' => $data['target_value'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'data' => $data['data'] ?? null,
            'status' => ($data['scheduled_at'] ?? null) ? 'scheduled' : 'sent',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => $createdBy,
        ]);
    }
}
