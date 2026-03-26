<?php

namespace App\Http\Middleware;

use App\Models\UserPageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisits
{
    /**
     * Route name fragments to skip tracking.
     */
    private const SKIP_PATTERNS = [
        'api.',
        'livewire.',
        '_debugbar',
        '_ignition',
        'nav.',
        'login',
        'logout',
        'password.',
        'register',
    ];

    /**
     * Route name suffixes to skip.
     */
    private const SKIP_SUFFIXES = [
        'export-pdf',
        'ping',
        'download',
        'upload',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track authenticated GET requests with a named route
        if (
            Auth::check() &&
            $request->isMethod('GET') &&
            $request->route()?->getName()
        ) {
            $routeName = $request->route()->getName();

            if ($this->shouldTrack($routeName)) {
                $this->recordVisit(Auth::id(), $routeName);
            }
        }

        return $response;
    }

    private function shouldTrack(string $routeName): bool
    {
        foreach (self::SKIP_PATTERNS as $pattern) {
            if (str_contains($routeName, $pattern)) {
                return false;
            }
        }

        foreach (self::SKIP_SUFFIXES as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return false;
            }
        }

        return true;
    }

    private function recordVisit(int $userId, string $routeName): void
    {
        try {
            UserPageVisit::upsert(
                [
                    'user_id' => $userId,
                    'route_name' => $routeName,
                    'visit_count' => 1,
                    'last_visited_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['user_id', 'route_name'],
                [
                    'visit_count' => DB::raw('visit_count + 1'),
                    'last_visited_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable) {
            // Never crash the request due to tracking failure
        }
    }
}
