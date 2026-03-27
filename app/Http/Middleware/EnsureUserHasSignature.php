<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Models\User $user */
        $user = $request->user();

        // 1. Skip if guest or user doesn't require a signature for their role
        if (! $user || ! $user->needsSignature()) {
            return $next($request);
        }

        // 2. Skip for signature-related or logout routes to prevent loops
        // We use both route names and paths for maximum robustness
        if ($request->routeIs(['signatures.*', 'logout', 'livewire.update']) || 
            $request->is('settings/signatures*', 'signatures*', 'logout', 'livewire/*') ||
            $request->hasHeader('X-Livewire') || 
            $request->ajax()) {
            return $next($request);
        }

        // 3. Skip if the user already has a default active signature
        // We check this after exclusions to save a database query
        if ($user->hasDefaultSignature()) {
            return $next($request);
        }

        // 4. Redirect to signature management with a helpful notice
        return redirect()->route('signatures.manage')
            ->with('onboarding_signature', true)
            ->with('toast_info', 'Digital Signature Required: Your role requires a verified signature for approvals. Let\'s get it set up!');
    }
}
