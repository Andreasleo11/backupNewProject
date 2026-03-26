<?php

namespace App\Domain\Overtime\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\ApprovalFlow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeForm extends Model implements Approvable
{
    use SoftDeletes, HasFactory;

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

    public function processedDetails()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id')
                    ->where('status', 'Approved')
                    ->where('is_processed', 1);
    }

    public function failedDetails()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id')
                    ->where('status', 'Rejected')
                    ->where('reason', 'like', '%JPAYROLL%');
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(\App\Models\OvertimeFormApproval::class, 'overtime_form_id', 'id');
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

    public function getApprovableDepartmentName(): ?string
    {
        return $this->department?->name;
    }

    public function getApprovableBranchValue(): ?string
    {
        return (string) $this->branch;
    }

    /**
     * Centralized query scope for role-based visibility.
     * This logic is shared by the Index component and the Policy.
     */
    public function scopeByRole($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // 1. Super-admin sees everything (including drafts/null status)
            if ($user->hasRole('super-admin')) {
                return;
            }

            // 2. Creator always sees their own (including drafts)
            $q->where('user_id', $user->id);

            // 3. Verificator sees all submitted/approved forms
            if ($user->can('overtime.review')) {
                $q->orWhereNotNull('status');
                return;
            }

            // 4. Director sees all submitted/approved forms
            if ($user->hasRole('director')) {
                $q->orWhereNotNull('status');
                return;
            }

            // 5. General Manager: Sees non-office departments
            if ($user->hasRole('general-manager')) {
                $q->orWhere(function ($sq) {
                    $sq->whereNotNull('status')
                       ->whereHas('department', fn($dq) => $dq->where('is_office', false));
                });
                return;
            }

            // 6. Department Head: Sees their own department (plus linked depts like Logistic/Store)
            if ($user->hasRole('department-head')) {
                $deptName = $user->department?->name;
                $q->orWhere(function ($sq) use ($deptName) {
                    $sq->whereNotNull('status')
                       ->whereHas('department', function ($dq) use ($deptName) {
                           $dq->where('name', $deptName);
                           if ($deptName === 'LOGISTIC') {
                               $dq->orWhere('name', 'STORE');
                           } elseif ($deptName === 'QC') {
                               $dq->orWhere('name', 'QA');
                           }
                       });
                });
                return;
            }

            // 7. Regular staff: sees forms for their own department (submitted only)
            $deptName = $user->department?->name;
            if ($deptName) {
                $q->orWhere(function ($sq) use ($deptName) {
                    $sq->whereNotNull('status')
                       ->whereHas('department', fn($dq) => $dq->where('name', $deptName));
                });
            }
        });
    }
}

