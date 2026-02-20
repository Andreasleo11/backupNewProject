<?php

namespace App\Http\Controllers;

use App\Models\UserPinnedRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NavPinController extends Controller
{
    private const MAX_PINS = 3;

    /**
     * Pin a route to Quick Access.
     */
    public function pin(Request $request): JsonResponse
    {
        $request->validate([
            'route_name' => ['required', 'string', 'max:120'],
        ]);

        $userId    = Auth::id();
        $routeName = $request->input('route_name');

        // Validate the route actually exists
        if (! \Route::has($routeName)) {
            return response()->json(['error' => 'Invalid route.'], 422);
        }

        $currentCount = UserPinnedRoute::where('user_id', $userId)->count();

        // Already pinned → idempotent success
        $alreadyPinned = UserPinnedRoute::where('user_id', $userId)
            ->where('route_name', $routeName)
            ->exists();

        if ($alreadyPinned) {
            return response()->json(['pinned' => true, 'count' => $currentCount]);
        }

        if ($currentCount >= self::MAX_PINS) {
            return response()->json([
                'error' => 'Maximum ' . self::MAX_PINS . ' pinned items allowed.',
            ], 422);
        }

        UserPinnedRoute::create([
            'user_id'    => $userId,
            'route_name' => $routeName,
            'pinned_at'  => now(),
        ]);

        return response()->json(['pinned' => true, 'count' => $currentCount + 1]);
    }

    /**
     * Unpin a route from Quick Access.
     */
    public function unpin(Request $request): JsonResponse
    {
        $request->validate([
            'route_name' => ['required', 'string', 'max:120'],
        ]);

        $deleted = UserPinnedRoute::where('user_id', Auth::id())
            ->where('route_name', $request->input('route_name'))
            ->delete();

        $count = UserPinnedRoute::where('user_id', Auth::id())->count();

        return response()->json(['pinned' => false, 'count' => $count, 'deleted' => (bool) $deleted]);
    }
}
