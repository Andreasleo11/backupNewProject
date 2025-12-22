<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\UserSignature as EloquentUserSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ApprovalStepSignatureController extends Controller
{
    public function show(ApprovalStep $step): StreamedResponse
    {
        abort_unless($step->user_signature_id, 404);

        // Load req + approvable
        $step->loadMissing('request.approvable');

        $req = $step->request;
        abort_unless($req, 404);

        $approvable = $req->approvable;
        abort_unless($approvable, 404);

        // ✅ Authorization (PR now, extend later)
        // Option A: if your approvable has a policy "view"
        // Gate::authorize('view', $approvable);

        // Option B (simple + aligns with your current PR access logic):
        // Only allow if user can view the PR show page.
        // If you already have PR policy, use it. If not yet, keep temporary allowlist:
        // NOTE: Replace this with policy ASAP.
        if (method_exists($approvable, 'createdBy')) {
            // just touching; no-op
        }

        // TEMP minimal guard (better than open access):
        // - allow creator
        // - allow superadmin/admin by role name
        $user = Auth::user();
        $isPrivileged = $user?->roles()?->whereIn('name', ['super-admin', 'admin'])->exists() ?? false;

        $creatorId = $approvable->user_id_create ?? null;
        abort_unless($isPrivileged || ($creatorId && (int) $creatorId === (int) $user->id), 403);

        // Stream the signature file from private disk
        $sig = EloquentUserSignature::query()->findOrFail($step->user_signature_id);

        $path = $sig->file_path ?? $sig->svg_path;
        abort_unless($path, 404);

        return Storage::disk('private')->response($path);
    }
}
