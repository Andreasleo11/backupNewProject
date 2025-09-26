<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        return response()->json([
            "unread" => $user->unreadNotifications()->count(),
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
                    "id" => $n->id,
                    "type" => class_basename($n->type),
                    "title" => data_get($n->data, "title", "Notification"),
                    "body" => data_get($n->data, "body", ""),
                    "url" => data_get($n->data, "url", null),
                    "read_at" => optional($n->read_at)->toIso8601String(),
                    "created_at" => $n->created_at->toIso8601String(),
                ];
            });

        return response()->json(["items" => $items]);
    }

    public function markAsRead(Request $request, ?string $id)
    {
        $user = $request->user();

        if ($id) {
            $n = $user->notifications()->whereKey($id)->firstOrFail();
            $n->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(["ok" => true]);
    }
}
