<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()){
            $user = Auth::user();
            $session_token = session('remember_token');

            if($user && $user->remember_token !== $session_token){
                Auth::user()->update(['remember_token'=>null]);
                Auth::logout();
                return redirect('/')->with('error', 'Session expired. Please login again!');
            }
        }
        return $next($request);
    }
}
