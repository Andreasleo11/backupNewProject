<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ApprovalSignatureController extends Controller
{
    public function show(ApprovalStep $step): StreamedResponse
    {
        // Must have at least the image path
        abort_unless($step->signature_image_path, 404);

        // Load req + approvable
        $step->loadMissing('request.approvable');

        $req = $step->request;
        abort_unless($req, 404);

        $approvable = $req->approvable;
        abort_unless($approvable, 404);

        abort_unless(Auth::check(), 401);
        $user = Auth::user();

        /**
         * Authorization strategy:
         * - Prefer policy: if approvable has "view" policy, use it.
         * - Otherwise fallback to minimal safe allowlist.
         */
        $authorized = false;

        // A) Use policy for the approvable:
        Gate::authorize('view', $approvable);
        $authorized = true;

        // B) Temporary allowlist fallback (rarely reached now):
        if (! $authorized) {
            $isPrivileged = $user->roles()->whereIn('name', ['super-admin', 'admin'])->exists();

            $creatorId = $approvable->user_id_create ?? null;

            // allow: creator, acted_by, submitted_by, current approver actor (if user-based)
            $isCreator = $creatorId && (int) $creatorId === (int) $user->id;
            $isActor = $step->acted_by && (int) $step->acted_by === (int) $user->id;
            $isSubmitter = $req->submitted_by && (int) $req->submitted_by === (int) $user->id;

            $isCurrentApproverUser =
                $req->status === 'IN_REVIEW'
                && (int) $req->current_step === (int) $step->sequence
                && $step->approver_type === 'user'
                && (int) $step->approver_id === (int) $user->id;

            abort_unless(
                $isPrivileged || $isCreator || $isActor || $isSubmitter || $isCurrentApproverUser,
                403
            );
        }

        /**
         * IMPORTANT (Option 1):
         * Use snapshot path stored on the step, NOT the current user_signatures path.
         */
        $path = $step->signature_image_path;
        abort_unless($path, 404);

        return Storage::disk('private')->response($path);
    }
}
