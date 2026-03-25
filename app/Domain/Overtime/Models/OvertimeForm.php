<?php

namespace App\Domain\Overtime\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Models\User;
use App\Models\ApprovalFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeForm extends Model implements Approvable
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
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id');
    }

    public function rejectedDetails()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id')->where('status', 'Rejected');
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }



    // -------------------------------------------------------------------------
    // Approvable contract — makes Overtime polymorphically compatible with the
    // unified approval UI used by PurchaseRequest and IT Ticket.
    // -------------------------------------------------------------------------

    /**
     * MorphOne relation to the unified Approval Engine.
     */
    public function approvalRequest()
    {
        return $this->morphOne(\App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::class, 'approvable');
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

