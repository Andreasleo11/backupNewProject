<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderFormOvertime extends Model
{
    protected $table = 'header_form_overtime';

    protected $fillable = [
        'user_id',
        'dept_id',
        'create_date',
        'branch',
        'is_approve',
        'status',
        'is_design',
        'is_export',
        'description',
        'is_planned',
        'approval_flow_id',
    ];

    public function user()
    {
        return $this->hasone(User::class, 'id', 'user_id');
    }

    public function department()
    {
        return $this->hasone(Department::class, 'id', 'dept_id');
    }

    public function details()
    {
        return $this->hasMany(DetailFormOvertime::class, 'header_id', 'id');
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
            ? $this->flow->steps()
            ->whereNotIn(
                'id',
                $this->approvals()
                    ->where('status', 'approved')
                    ->pluck('flow_step_id')
            )
            ->orderBy('step_order')
            ->first()
            : null;
    }

    public function nextStep(): ?ApprovalFlowStep
    {
        if (! $this->flow) {
            return null;               // no template attached
        }

        // Which step order number is the last that got approved?
        $lastApprovedOrder = $this->approvals()
            ->where('status', 'approved')
            ->with('step')             // eager load so we can read step_order
            ->get()
            ->pluck('step.step_order')
            ->max();                   // null if nothing approved yet

        // Return the first step with a higher order number
        return $this->flow->steps()
            ->when(
                $lastApprovedOrder !== null,
                fn($q) => $q->where('step_order', '>', $lastApprovedOrder)
            )
            ->orderBy('step_order')
            ->first();                 // null means we’re at the end
    }
}
