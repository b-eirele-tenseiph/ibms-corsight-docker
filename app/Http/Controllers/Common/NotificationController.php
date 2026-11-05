<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Notifications\AdminNotification;
use App\Notifications\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // resolveUser              (1.0) Resolve the authenticated user based on
    //                                guard.
    // resolveType              (2.0) Resolve the notification type based on
    //                                user.
    // index                    (3.0) Retrieve paginated notifications +
    //                                unread count.
    // markAsRead               (4.0) Bulk mark selected notifications as read.
    // readOne                  (5.0) Mark single notification as read
    //                                and redirect.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> resolveUser
     * <Function> Resolve the authenticated user based on guard.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    private function resolveUser(Request $request)
    {
        return $request->user('admin') ?? $request->user();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> resolveType
     * <Function> Resolve the notification type based on user.
     *
     * @param  mixed $user
     * @return string
     */
    private function resolveType($user): string
    {
        return $user instanceof Admin
            ? AdminNotification::class
            : UserNotification::class;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> index
     * <Function> Retrieve paginated notifications and unread count.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $type = $this->resolveType($user);

        $notifications = $user->notifications()
            ->where('type', $type)
            ->latest()
            ->paginate(10);

        $notifications->getCollection()->transform(function ($notification) {
            $notification->data = is_string($notification->data)
                ? json_decode($notification->data, true)
                : $notification->data;
            return $notification;
        });

        $unreadCount = $user->unreadNotifications()
            ->where('type', $type)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
            'unread_badge'  => $unreadCount > 99 ? '99+' : (string) $unreadCount,
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> markAsRead
     * <Function> Bulk mark selected notifications as read.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $type = $this->resolveType($user);
        $ids  = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return response()->json(['message' => 'No IDs provided'], 422);
        }

        $updated = $user->unreadNotifications()
            ->where('type', $type)
            ->whereIn('id', $ids)
            ->update(['read_at' => now()]);

        $unreadCount = $user->unreadNotifications()
            ->where('type', $type)
            ->count();

        return response()->json([
            'message'      => 'Marked as read successfully',
            'updated'      => $updated,
            'unread_count' => $unreadCount,
            'unread_badge' => $unreadCount > 99 ? '99+' : (string) $unreadCount,
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> readOne
     * <Function> Mark single notification as read and redirect.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Notifications\DatabaseNotification $notification
     * @return mixed
     */
    public function readOne(Request $request, DatabaseNotification $notification)
    {
        $user = $this->resolveUser($request);

        abort_unless(
            $notification->notifiable_type === get_class($user)
                && (string) $notification->notifiable_id === (string) $user->getKey(),
            403
        );

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $url = $request->input('url');

        if (empty($url)) {
            return response()->noContent();
        }

        return redirect($url);
    }
}
