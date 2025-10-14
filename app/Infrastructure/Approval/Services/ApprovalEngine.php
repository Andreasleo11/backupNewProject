<?php

namespace App\Infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\Approvals;
use App\Domain\Approval\Contracts\RuleResolver;
use App\Infrastructure\Approval\Models\ApprovalRequest;
use App\Infrastructure\Approval\Models\ApprovalStep;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ApprovalEngine implements Approvals
{
    public function __construct(private RuleResolver $resolver) {}

    public function currentRequest(object $approvable): ?ApprovalRequest
    {
        return $approvable->approvalRequest()->with('steps')->first();
    }

    public function submit(object $approvable, int $by, array $ctx = []): ApprovalRequest
    {
        return DB::transaction(function () use ($approvable, $by, $ctx) {
            /** @var ApprovalRequest $req */
            $req = $approvable->approvalRequest()->firstOrNew([]);
            if ($req->exists && $req->status !== 'DRAFT') {
                throw new \DomainException('Already submitted.');
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
                $req->steps()->create([
                    'sequence' => $s->sequence,
                    'approver_type' => $s->approver_type,
                    'approver_id' => $s->approver_id,
                    'status' => 'PENDING',
                ]);
            }

            $this->log($req, $by, 'DRAFT', 'IN_REVIEW', null);
            $this->notifyCurrentApprover($req);

            return $req->fresh('steps');
        });
    }

    public function approve(object $approvable, int $by, ?string $remarks = null): void
    {
        $req = $this->mustGetInReview($approvable);

        DB::transaction(function () use ($req, $by, $remarks) {
            $step = $this->mustGetCurrentStep($req);
            $this->guardActor($step, $by);
            $step->update(['status' => 'APPROVED', 'acted_by' => $by, 'acted_at' => now(), 'remarks' => $remarks]);

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

    public function reject(object $approvable, int $by, ?string $remarks = null): void
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

    private function mustGetInReview(object $approvable): ApprovalRequest
    {
        /** @var ApprovalRequest|null $req */
        $req = $this->currentRequest($approvable);
        if (! $req) {
            throw new \DomainException('No approval request.');
        }
        if ($req->status !== 'IN_REVIEW') {
            throw new \DomainException('Request is not in review.');
        }

        return $req;
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
        } else { // role-based
            $user = auth()->user();
            if (! $user || ! $user->roles()->whereKey($step->approver_id)->exists()) {
                throw new AuthorizationException('Your role is not permitted to approve this step.');
            }
        }
    }

    private function log(ApprovalRequest $req, int $by, ?string $from, string $to, ?string $remarks): void
    {
        $req->actions()->create([
            'user_id' => $by, 'from_status' => $from, 'to_status' => $to, 'remarks' => $remarks,
        ]);
    }

    // --- notifications (centralized) ---
    private function notifyCurrentApprover(ApprovalRequest $req): void
    {
        // Send mail/db/broadcast to the current approver(s)
        // (Wire to your existing notification system; keep it minimal here)
    }

    private function notifyFinalApproval(ApprovalRequest $req): void
    { /* ... */
    }

    private function notifyRejection(ApprovalRequest $req): void
    { /* ... */
    }
}
