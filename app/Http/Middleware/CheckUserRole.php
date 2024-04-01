<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ... $roles): Response
    {
        $user = auth()->user();


        // Check if the user's role matches the provided role
        if ($user && in_array($user->role_id, $roles)) {
            return $next($request);
        }


        // Redirect or handle unauthorized access
        return redirect('/home'); // Adjust the redirect path as needed
    }

}
