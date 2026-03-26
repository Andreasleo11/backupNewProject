<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EvaluationAccess
 *
 * Route-level guard for the entire Evaluation module.
 * Passes if the authenticated user has EITHER:
 *   - evaluation.view-department  (dept head / grader)
 *   - evaluation.view-any         (HRD, GM, super-admin)
 *
 * These are Spatie permissions seeded by RolesAndPermissionsSeeder.
 */
class EvaluationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->canAny(['evaluation.view-department', 'evaluation.view-any', 'evaluation.view-regular', 'evaluation.view-yayasan', 'evaluation.view-magang'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to Evaluation module.');
    }
}
