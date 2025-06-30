<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Models\PushNotificationRecipient;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    /**
     * Get user notifications
     *
     * Retrieve notifications for the authenticated user.
     *
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Number of notifications per page (max 50). Example: 20
     * @queryParam unread_only boolean Filter to show only unread notifications. Example: false
     * @queryParam type string Filter by notification type. Example: general
     *
     * @group Notifications
     */
    public function index(Request $request, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);
        $perPage = min($request->input('per_page', 20), 50);

        $query = PushNotificationRecipient::with(['pushNotification'])
            ->forUser($userType, $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        // Filter by notification type
        if ($request->filled('type')) {
            $query->whereHas('pushNotification', function ($q) use ($request) {
                $q->where('type', $request->input('type'));
            });
        }

        $notifications = $query->paginate($perPage);

        // Transform the data
        $transformedNotifications = $notifications->getCollection()->map(function ($recipient) {
            $notification = $recipient->pushNotification;

            return [
                'id' => $recipient->id,
                'notification_id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'action_url' => $notification->action_url,
                'image_url' => $notification->image_url,
                'data' => $notification->data,
                'is_read' => $recipient->isRead(),
                'is_clicked' => $recipient->isClicked(),
                'sent_at' => $recipient->sent_at,
                'read_at' => $recipient->read_at,
                'clicked_at' => $recipient->clicked_at,
                'created_at' => $recipient->created_at,
            ];
        });

        return $response->setData([
            'notifications' => $transformedNotifications,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ],
            'unread_count' => PushNotificationRecipient::forUser($userType, $user->id)->unread()->count(),
        ]);
    }

    /**
     * Mark notification as read
     *
     * Mark a specific notification as read for the authenticated user.
     *
     * @group Notifications
     */
    public function markAsRead(Request $request, int $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $recipient = PushNotificationRecipient::forUser($userType, $user->id)->find($id);

        if (! $recipient) {
            return $response
                ->setError()
                ->setMessage('Notification not found')
                ->setCode(404);
        }

        $recipient->markAsRead();

        return $response->setMessage('Notification marked as read');
    }

    /**
     * Mark notification as clicked
     *
     * Mark a notification as clicked when user taps on it.
     *
     * @group Notifications
     */
    public function markAsClicked(Request $request, int $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $recipient = PushNotificationRecipient::forUser($userType, $user->id)->find($id);

        if (! $recipient) {
            return $response
                ->setError()
                ->setMessage('Notification not found')
                ->setCode(404);
        }

        $recipient->markAsClicked();

        return $response->setMessage('Notification marked as clicked');
    }

    /**
     * Mark all notifications as read
     *
     * Mark all notifications as read for the authenticated user.
     *
     * @group Notifications
     */
    public function markAllAsRead(Request $request, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $count = PushNotificationRecipient::forUser($userType, $user->id)
            ->unread()
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        return $response
            ->setData(['marked_count' => $count])
            ->setMessage("Marked {$count} notifications as read");
    }

    /**
     * Get notification statistics
     *
     * Get notification statistics for the authenticated user.
     *
     * @group Notifications
     */
    public function getStats(Request $request, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $stats = [
            'total' => PushNotificationRecipient::forUser($userType, $user->id)->count(),
            'unread' => PushNotificationRecipient::forUser($userType, $user->id)->unread()->count(),
            'read' => PushNotificationRecipient::forUser($userType, $user->id)->read()->count(),
            'clicked' => PushNotificationRecipient::forUser($userType, $user->id)->whereNotNull('clicked_at')->count(),
        ];

        return $response->setData($stats);
    }

    /**
     * Delete notification
     *
     * Delete a notification from user's list.
     *
     * @group Notifications
     */
    public function destroy(Request $request, int $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $recipient = PushNotificationRecipient::forUser($userType, $user->id)->find($id);

        if (! $recipient) {
            return $response
                ->setError()
                ->setMessage('Notification not found')
                ->setCode(404);
        }

        $recipient->delete();

        return $response->setMessage('Notification deleted successfully');
    }

    protected function getUserType($user): string
    {
        // Determine user type based on the model class
        $class = $user::class;

        if (str_contains($class, 'Customer')) {
            return 'customer';
        }

        if (str_contains($class, 'User')) {
            return 'admin';
        }

        return 'user';
    }
}
