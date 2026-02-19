<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    /**
     * Redirect to the home page.
     * Notifications are displayed via the bell widget; there is no standalone page.
     */
    public function index(Request $request)
    {
        return redirect()->route('home');
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function feed(Request $request)
    {
        $user = $request->user();

        $items = $user
            ->notifications()
            ->latest()
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'type'       => class_basename($n->type),
                    'title'      => data_get($n->data, 'title', 'Notification'),
                    'body'       => data_get($n->data, 'message', data_get($n->data, 'body', '')),
                    'url'        => data_get($n->data, 'action_url', data_get($n->data, 'url', null)),
                    'read_at'    => optional($n->read_at)->toIso8601String(),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            });

        return response()->json(['items' => $items]);
    }

    public function markAsRead(Request $request, ?string $id = null)
    {
        $user = $request->user();

        if ($id) {
            $n = $user->notifications()->whereKey($id)->firstOrFail();
            $n->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Mark every unread notification for the current user as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}

