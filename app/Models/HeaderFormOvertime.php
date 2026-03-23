<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderFormOvertime extends Model
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
        return $this->hasone(\App\Infrastructure\Persistence\Eloquent\Models\Department::class, 'id', 'dept_id');
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
            return null; // no template attached
        }

        // Which step order number is the last that got approved?
        $lastApprovedOrder = $this->approvals()
            ->where('status', 'approved')
            ->with('step') // eager load so we can read step_order
            ->get()
            ->pluck('step.step_order')
            ->max(); // null if nothing approved yet

        // Return the first step with a higher order number
        return $this->flow
            ->steps()
            ->when(
                $lastApprovedOrder !== null,
                fn ($q) => $q->where('step_order', '>', $lastApprovedOrder),
            )
            ->orderBy('step_order')
            ->first(); // null means we’re at the end
    }

}
