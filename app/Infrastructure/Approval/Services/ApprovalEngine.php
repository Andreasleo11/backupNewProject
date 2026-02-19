<?php

namespace App\Infrastructure\Approval\Services;

use App\Application\Approval\Contracts\Approvals;
use App\Application\Approval\DTOs\ApprovalInfo;
use App\Application\Auth\UserRoles;
use App\Domain\Approval\Contracts\Approvable;
use App\Domain\Approval\Contracts\RuleResolver;
use App\Domain\Signature\Repositories\UserSignatureRepository;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ApprovalEngine implements Approvals
{
    public function __construct(private RuleResolver $resolver, private UserRoles $userRoles, private UserSignatureRepository $userSignatures) {}

    private function toInfo(?ApprovalRequest $req): ?ApprovalInfo
    {
        if (! $req) {
            return null;
        }

        return new ApprovalInfo(id: $req->id, status: $req->status, currentStep: $req->current_step);
    }

    public function currentRequest(Approvable $approvable): ?ApprovalInfo
    {
        $req = $approvable->approvalRequest()->with('steps')->first();

        return $this->toInfo($req);
    }

    public function reject(Approvable $approvable, int $by, ?string $remarks = null): void
    {
        $req = $this->mustGetInReview($approvable);

        DB::transaction(function () use ($req, $by, $remarks) {
            $step = $this->mustGetCurrentStep($req);
            $this->guardActor($step, $by);
            $step->update(['status' => 'REJECTED', 'acted_by' => $by, 'acted_at' => now(), 'remarks' => $remarks]);

            $from = $req->status;
            $req->update(['status' => 'REJECTED']);
            $this->log($req, $by, $from, 'REJECTED', $remarks);
            $this->notifyRejection($req);
        });
    }

    public function return(Approvable $approvable, int $by, string $reason): void
    {
        $req = $this->mustGetInReview($approvable);

        DB::transaction(function () use ($req, $by, $reason) {
            $step = $this->mustGetCurrentStep($req);
            $this->guardActor($step, $by);

            // Mark current step as RETURNED
            $step->update([
                'status' => 'RETURNED',
                'acted_by' => $by,
                'acted_at' => now(),
                'return_reason' => $reason,
            ]);

            $from = $req->status;
            $req->update(['status' => 'RETURNED']);
            $this->log($req, $by, $from, 'RETURNED', $reason);
            // $this->notifyReturnToCreator($req, $reason); // Implement notification later if needed
        });
    }

    public function submit(Approvable $approvable, int $by, array $ctx = []): ApprovalInfo
    {
        $req = DB::transaction(function () use ($approvable, $by, $ctx) {
            /** @var ApprovalRequest $req */
            $req = $approvable->approvalRequest()->firstOrNew([]);

            // Allow resubmission from RETURNED or REJECTED state
            $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];
            if ($req->exists && ! in_array($req->status, $allowedStatuses)) {
                throw new \DomainException('Already submitted.');
            }

            // On resubmit from RETURNED or REJECTED: wipe old steps, reset item approvals
            if ($req->exists && in_array($req->status, ['RETURNED', 'REJECTED'])) {
                $req->steps()->delete();
                if (method_exists($approvable, 'resetItemApprovals')) {
                    $approvable->resetItemApprovals();
                }
            }

            $modelType = get_class($approvable);
            $tpl = $this->resolver->resolveFor($modelType, $ctx);
            if (! $tpl) {
                throw new \DomainException('No matching approval rule template.');
            }

            $req->fill([
                'status' => 'IN_REVIEW',
                'rule_template_id' => $tpl->id,
                'current_step' => 1,
                'submitted_by' => $by,
                'submitted_at' => now(),
                'meta' => $ctx,
            ])->save();

            // snapshot steps
            foreach ($tpl->steps as $s) {
                $snapshot = $this->resolveApproverSnapshot($s->approver_type, $s->approver_id);

                $req->steps()->create([
                    'sequence' => $s->sequence,
                    'approver_type' => $s->approver_type,
                    'approver_id' => $s->approver_id,
                    'approver_snapshot_name' => $snapshot['name'],
                    'approver_snapshot_role_slug' => $snapshot['role_slug'],
                    'approver_snapshot_label' => $snapshot['label'],
                    'status' => 'PENDING',
                ]);
            }

            $this->log($req, $by, 'DRAFT', 'IN_REVIEW', null);
            $this->notifyCurrentApprover($req);

            return $req->fresh('steps');
        });

        return $this->toInfo($req);
    }

    public function approve(Approvable $approvable, int $by, ?string $remarks = null): void
    {
        $req = $this->mustGetInReview($approvable);

        DB::transaction(function () use ($req, $by, $remarks) {
            $step = $this->mustGetCurrentStep($req);
            $this->guardActor($step, $by);

            // attach signature snapshot once
            $this->attachSignatureSnapshotToStep($step, $by, $remarks);

            $actingUser = \App\Infrastructure\Persistence\Eloquent\Models\User::find($by);

            $step->update([
                'status' => 'APPROVED',
                'acted_by' => $by,
                'acted_at' => now(),
                'remarks' => $remarks,
                'approver_snapshot_name' => $actingUser?->name,
            ]);

            $next = $req->steps()->where('sequence', '>', $req->current_step)->orderBy('sequence')->first();

            if ($next) {
                $from = $req->status;
                $req->update(['current_step' => $next->sequence]); // remain IN_REVIEW
                $this->log($req, $by, $from, 'IN_REVIEW', $remarks);
                $this->notifyCurrentApprover($req);
            } else {
                $from = $req->status;
                $req->update(['status' => 'APPROVED']);
                $this->log($req, $by, $from, 'APPROVED', $remarks);
                $this->notifyFinalApproval($req);
            }
        });
    }

    private function mustGetInReview(Approvable $approvable): ApprovalRequest
    {
        /** @var ApprovalRequest|null $req */
        $req = $approvable->approvalRequest()->with('steps')->first();

        if (! $req) {
            throw new \DomainException('No approval request.');
        }
        if ($req->status !== 'IN_REVIEW') {
            throw new \DomainException('Request is not in review.');
        }

        return $req;
    }

    public function canAct(Approvable $approvable, int $userId): bool
    {
        $req = $approvable->approvalRequest()->with('steps')->first();
        if (! $req || $req->status !== 'IN_REVIEW') {
            return false;
        }

        $step = $req->steps->firstWhere('sequence', (int) $req->current_step);
        if (! $step) {
            return false;
        }

        try {
            $this->guardActor($step, $userId);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function mustGetCurrentStep(ApprovalRequest $req)
    {
        return $req->steps()->where('sequence', $req->current_step)->firstOrFail();
    }

    private function guardActor(ApprovalStep $step, int $userId): void
    {
        if ($step->approver_type === 'user') {
            if ((int) $step->approver_id !== $userId) {
                throw new AuthorizationException('Not the assigned approver.');
            }
        } else {
            // role-based
            if (! $this->userRoles->userHasRoleId($userId, (int) $step->approver_id)) {
                throw new AuthorizationException('Your role is not permitted to approve this step.');
            }
        }
    }

    private function log(ApprovalRequest $req, int $by, ?string $from, string $to, ?string $remarks): void
    {
        $req->actions()->create([
            'user_id' => $by,
            'from_status' => $from,
            'to_status' => $to,
            'remarks' => $remarks,
        ]);
    }

    // --- notifications (centralized) ---
    private function notifyCurrentApprover(ApprovalRequest $req): void
    {
        $currentSteps = $req->steps()->where('sequence', $req->current_step)->get();
        // Load PR relationship for department check (assuming 'approvable' is PurchaseRequest)
        // Since ApprovalRequest is polymorphic, we need to get the approvable.
        // Or assume $req is loaded with approvable.
        $pr = $req->approvable; 
        
        // Safety check: if approvable isn't loaded or isn't a PR, we might skip scoping.
        $prDeptName = $pr instanceof \App\Models\PurchaseRequest ? $pr->from_department : null;

        foreach ($currentSteps as $step) {
            $usersToNotify = collect();

            if ($step->approver_type === 'user') {
                $user = \App\Infrastructure\Persistence\Eloquent\Models\User::find($step->approver_id);
                if ($user) $usersToNotify->push($user);
            } else {
                // Role-based
                $roleUsers = $this->userRoles->getUsersWithRole((int) $step->approver_id);
                
                // Scoping Logic
                $roleSlug = $step->approver_snapshot_role_slug;

                if ($roleSlug === 'pr-dept-head' && $prDeptName) {
                    $roleUsers->load('department'); 
                    $usersToNotify = $roleUsers->filter(function ($u) use ($prDeptName) {
                        return $u->department && $u->department->name === $prDeptName;
                    });
                }
                elseif ($roleSlug === 'pr-gm' && $pr->branch) {
                    $roleUsers->load('employee');
                    $usersToNotify = $roleUsers->filter(function ($u) use ($pr) {
                        return $u->employee && $u->employee->branch === $pr->branch->value;
                    });
                }
                elseif ($roleSlug === 'pr-purchaser' && $pr->to_department) {
                    $roleUsers->load('roles');
                    // Check for specific sub-role capability: pr-purchaser-{dept_slug}
                    // e.g. pr-purchaser-maintenance, pr-purchaser-computer
                    $targetRole = 'pr-purchaser-' . \Illuminate\Support\Str::slug($pr->to_department->label());
                    
                    $usersToNotify = $roleUsers->filter(function ($u) use ($targetRole) {
                        return $u->hasRole($targetRole);
                    });
                }
                else {
                    // Global roles (Director, Verificator) or fallback - Notify all
                    $usersToNotify = $roleUsers;
                }
            }

            if ($usersToNotify->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send(
                    $usersToNotify, 
                    new \App\Notifications\PurchaseRequestApprovalNotification($pr, $step)
                );
            }
        }
    }

    private function notifyFinalApproval(ApprovalRequest $req): void
    {
        /* ... */
    }

    private function notifyRejection(ApprovalRequest $req): void
    {
        /* ... */
    }

    private function attachSignatureSnapshotToStep(ApprovalStep $step, int $by, ?string $remarks): void
    {
        // prevent double attach + double "used" events
        if ($step->user_signature_id) {
            return;
        }

        // find default active signature
        $sig = $this->userSignatures->listByUser($by, onlyActive: true)[0] ?? null;

        if (! $sig || ! $sig->isDefault || ! $sig->isActive()) {
            throw new \DomainException('No default active signature found. Please set a default signature first.');
        }

        // snapshot to approval_steps
        $step->update([
            'user_signature_id' => (int) $sig->id,
            'signature_image_path' => $sig->filePath ?? $sig->svgPath,
            'signature_sha256' => $sig->sha256,
        ]);

        // global signature audit log
        $this->userSignatures->recordEvent(
            (int) $sig->id,
            'used',
            [
                'feature' => 'approval_engine',
                'approval_step_id' => $step->id,
                'sequence' => $step->sequence,
                'remarks' => $remarks,
            ]
        );
    }

    private function resolveApproverSnapshot(string $type, int $id): array
    {
        if ($type === 'user') {
            $user = \App\Infrastructure\Persistence\Eloquent\Models\User::find($id);

            return [
                'name' => $user->name ?? 'Unknown User',
                'role_slug' => null,
                'label' => $user->name ?? 'Unknown User',
            ];
        }

        // role
        $role = \Spatie\Permission\Models\Role::find($id);

        return [
            'name' => null,
            'role_slug' => $role->name ?? null,
            'label' => $this->getRoleLabel($role->name ?? ''),
        ];
    }

    private function getRoleLabel(string $slug): string
    {
        return match ($slug) {
            'pr-dept-head' => 'Dept head',
            'pr-verificator' => 'Verificator',
            'pr-director' => 'Director',
            'pr-gm' => 'General Manager',
            'pr-purchaser' => 'Purchasing',
            default => $slug,
        };
    }
}
