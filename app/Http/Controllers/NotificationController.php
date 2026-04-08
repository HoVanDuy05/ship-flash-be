<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $notification = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->findOrFail($notificationId);

        $notification->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function getUnreadCount(Request $request)
    {
        $count = $request->user()->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:ride_accepted,ride_cancelled,ride_completed,payment_received,driver_arriving',
        ]);

        try {
            $notification = Notification::create([
                'notifiable_id' => $request->user_id,
                'notifiable_type' => 'App\\Models\\User',
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'data' => json_encode(['notification_type' => $request->type]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteNotification(Request $request, $notificationId)
    {
        $notification = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->findOrFail($notificationId);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    public function pushNotification(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        try {
            $notifications = [];
            foreach ($request->user_ids as $userId) {
                $notifications[] = [
                    'notifiable_id' => $userId,
                    'notifiable_type' => 'App\\Models\\User',
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => 'push_notification',
                    'data' => json_encode($request->data ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Notification::insert($notifications);

            return response()->json([
                'success' => true,
                'message' => 'Push notifications sent successfully',
                'data' => [
                    'sent_count' => count($notifications)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send push notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
