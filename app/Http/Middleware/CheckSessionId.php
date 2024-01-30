<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSessionId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()) {
            $user = Auth::user();
            dd($user);
            $sessionToken = session('remember_token');

            if ($user && $user->remember_token !== $sessionToken) {
                Auth::logout();

                return redirect('/')->with('error', 'Session expired. Please log in again.');
            }
        }
        
        // dd('test');
        return $next($request);
    }
}
