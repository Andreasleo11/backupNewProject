<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderFormOvertime extends Model implements Approvable
{
    use SoftDeletes;

    protected $table = 'header_form_overtime';

    protected $fillable = [
        'user_id',
        'dept_id',
        'branch',
        'status',
        'is_design',
        'is_export',
        'description',
        'is_planned',
        'approval_flow_id',
        'is_after_hour',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->hasOne(\App\Infrastructure\Persistence\Eloquent\Models\Department::class, 'id', 'dept_id');
    }

    public function details()
    {
        return $this->hasMany(DetailFormOvertime::class, 'header_id', 'id');
    }

    public function rejectedDetails()
    {
        return $this->hasMany(DetailFormOvertime::class, 'header_id', 'id')->where('status', 'Rejected');
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(OvertimeFormApproval::class, 'overtime_form_id', 'id');
    }

    public function currentStep()
    {
        return $this->flow
            ? $this->flow
                ->steps()
                ->whereNotIn(
                    'id',
                    $this->approvals()->where('status', 'approved')->pluck('flow_step_id'),
                )
                ->orderBy('step_order')
                ->first()
            : null;
    }

    public function nextStep(): ?ApprovalFlowStep
    {
        if (! $this->flow) {
            return null;
        }

        $lastApprovedOrder = $this->approvals()
            ->where('status', 'approved')
            ->with('step')
            ->get()
            ->pluck('step.step_order')
            ->max();

        return $this->flow
            ->steps()
            ->when(
                $lastApprovedOrder !== null,
                fn ($q) => $q->where('step_order', '>', $lastApprovedOrder),
            )
            ->orderBy('step_order')
            ->first();
    }

    // -------------------------------------------------------------------------
    // Approvable contract — makes Overtime polymorphically compatible with the
    // unified approval UI used by PurchaseRequest and IT Ticket.
    // -------------------------------------------------------------------------

    /**
     * Overtime uses OvertimeFormApproval pivot, not the PR-style ApprovalRequest morphOne.
     * Returns null so generic approval UI falls back gracefully; all overtime approval
     * logic flows through currentStep() / approvals().
     */
    public function approvalRequest()
    {
        return null;
    }

    public function getApprovableTypeLabel(): string
    {
        return 'Form Overtime';
    }

    public function getApprovableIdentifier(): string
    {
        return 'OT-' . $this->id;
    }

    public function getApprovableShowUrl(): string
    {
        return route('overtime.detail', $this->id);
    }
}
