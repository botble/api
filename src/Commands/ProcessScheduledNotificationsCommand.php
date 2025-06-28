<?php

namespace Botble\Api\Commands;

use Botble\Api\Models\PushNotification;
use Botble\Api\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:push-notification:process-scheduled', 'Process and send scheduled push notifications')]
class ProcessScheduledNotificationsCommand extends Command
{
    protected $signature = 'cms:push-notification:process-scheduled
                            {--limit=50 : Maximum number of notifications to process}
                            {--dry-run : Show what would be processed without actually sending}';

    protected $description = 'Process and send scheduled push notifications that are due';

    protected PushNotificationService $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        parent::__construct();
        $this->pushNotificationService = $pushNotificationService;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('🕐 Processing scheduled push notifications...');
        $this->line('');

        // Get scheduled notifications that are due
        $notifications = PushNotification::query()
            ->where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', Carbon::now());
            })
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('✅ No scheduled notifications to process');

            return self::SUCCESS;
        }

        $this->info("Found {$notifications->count()} notification(s) to process");
        $this->line('');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No notifications will actually be sent');
            $this->line('');
        }

        $processed = 0;
        $successful = 0;
        $failed = 0;

        foreach ($notifications as $notification) {
            $processed++;

            $this->line("Processing notification #{$notification->id}: {$notification->title}");

            if ($dryRun) {
                $this->line("  → Would send to: {$notification->target_type}" .
                    ($notification->target_value ? " ({$notification->target_value})" : ''));

                continue;
            }

            try {
                $result = $this->sendNotification($notification);

                if ($result['success']) {
                    $successful++;
                    $this->line("  ✅ Sent successfully (sent: {$result['sent_count']}, failed: {$result['failed_count']})");
                } else {
                    $failed++;
                    $this->line("  ❌ Failed: {$result['message']}");
                }

            } catch (\Exception $e) {
                $failed++;
                $this->line("  ❌ Error: {$e->getMessage()}");

                // Mark notification as failed
                $notification->markAsFailed($e->getMessage());

                logger()->error('Scheduled notification processing failed', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line('');
        $this->info('📊 Processing Summary:');
        $this->table(['Metric', 'Count'], [
            ['Total Processed', $processed],
            ['Successful', $successful],
            ['Failed', $failed],
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function sendNotification(PushNotification $notification): array
    {
        $notificationData = [
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'target_type' => $notification->target_type,
            'target_value' => $notification->target_value,
            'action_url' => $notification->action_url,
            'image_url' => $notification->image_url,
            'data' => $notification->data,
        ];

        $result = match ($notification->target_type) {
            'all' => $this->pushNotificationService->sendToAll($notificationData),
            'platform' => $this->pushNotificationService->sendToPlatform($notification->target_value, $notificationData),
            'user_type' => $this->pushNotificationService->sendToUserType($notification->target_value, $notificationData),
            'user' => $this->pushNotificationService->sendToUser('customer', (int) $notification->target_value, $notificationData),
            default => throw new \InvalidArgumentException("Invalid target type: {$notification->target_type}")
        };

        // Update notification status
        if ($result['success']) {
            $notification->markAsSent($result['sent_count'], $result['failed_count']);
        } else {
            $notification->markAsFailed($result['message'] ?? 'Unknown error');
        }

        return $result;
    }
}
