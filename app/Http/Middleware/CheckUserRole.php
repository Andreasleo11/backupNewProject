<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        // Check if the user's role matches the provided role
        if (!$user) {
            return redirect('/home');
        }

        foreach ($roles as $role) {
            // Check numeric role_id (legacy) or attribute
            if (isset($user->role_id) && is_numeric($role) && (int)$user->role_id === (int)$role) {
                return $next($request);
            }
            // Check string role name (Spatie)
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Redirect or handle unauthorized access
        return redirect('/home'); // Adjust the redirect path as needed
    }
}
