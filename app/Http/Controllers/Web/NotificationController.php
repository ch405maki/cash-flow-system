<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications.
     */
    public function index()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        // Authorization - ensure notification belongs to authenticated user
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== 'App\\Models\\User') {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead();

        // If request expects JSON (for API calls)
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        // For Inertia requests
        return redirect()->back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        // If request expects JSON (for API calls)
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        }

        // For Inertia requests
        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a notification.
     */
    public function destroy(DatabaseNotification $notification)
    {
        // Authorization - ensure notification belongs to authenticated user
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== 'App\\Models\\User') {
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        // If request expects JSON (for API calls)
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        }

        // For Inertia requests
        return redirect()->back()->with('success', 'Notification deleted successfully');
    }

    /**
     * Get unread notifications count (for AJAX polling).
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Mark notification as read and redirect to its link.
     */
    public function readAndRedirect(DatabaseNotification $notification)
    {
        // Authorization - ensure notification belongs to authenticated user
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== 'App\\Models\\User') {
            abort(403, 'Unauthorized action.');
        }

        // Mark as read if not already read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // Get the link from notification data
        $link = $notification->data['link'] ?? route('notifications.index');

        // Redirect to the notification link
        return redirect($link);
    }
}