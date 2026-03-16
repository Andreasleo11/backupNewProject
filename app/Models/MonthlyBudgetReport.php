<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Notifications\MonthlyBudgetReportUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Infrastructure\Persistence\Eloquent\Models\Department;

class MonthlyBudgetReport extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'dept_no',
        'creator_id',
        'report_date',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph',
        'reject_reason',
        'is_reject',
        'doc_num',
        'status',
        'is_cancel',
        'cancel_reason',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportDetail::class, 'header_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'dept_no', 'dept_no');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    /**
     * Get combined activities from Report, Files, and Approval Actions.
     */
    public function getCombinedActivitiesAttribute()
    {
        // 1. Get Report Logs
        $reportLogs = $this->activities;

        // 2. Get File Logs
        $fileIds = File::where('doc_id', $this->doc_num)->pluck('id');
        $fileLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', File::class)
            ->whereIn('subject_id', $fileIds)
            ->with('causer')
            ->get();

        // 3. Get Approval Actions
        $approvalActions = collect();
        if ($this->approvalRequest) {
            $approvalActions = $this->approvalRequest->actions()
                ->with('causer')
                ->get();
        }

        // 4. Merge & Sort desc
        return $reportLogs
            ->concat($fileLogs)
            ->concat($approvalActions)
            ->sortByDesc('created_at');
    }

    /**
     * Get workflow status from approval request.
     */
    public function getWorkflowStatusAttribute(): ?string
    {
        if ((int) $this->is_cancel === 1) {
            return 'CANCELED';
        }
        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Check if the report is currently a draft.
     */
    public function isDraft(): bool
    {
        return $this->approvalRequest === null;
    }

    /**
     * Get current workflow step (approver label).
     */
    public function getWorkflowStepAttribute(): ?string
    {
        $approval = $this->approvalRequest;
        if (!$approval || $approval->status !== 'IN_REVIEW') {
            return null;
        }
        $currentStep = $approval->steps()->where('sequence', $approval->current_step)->first();
        return $currentStep?->approver_snapshot_label ?? $currentStep?->approver_label;
    }

    /**
     * Get all workflow steps for visualization.
     */
    public function getWorkflowSignaturesAttribute(): array
    {
        $steps = collect();

        // 1. Modern Approval Engine
        if ($this->approvalRequest) {
            $approvalSteps = $this->approvalRequest->steps()
                ->orderBy('sequence')
                ->with('actedUser')
                ->get()
                ->map(function ($step) {
                    $uiStatus = match ($step->status) {
                        'APPROVED' => 'signed',
                        'REJECTED' => 'rejected',
                        'CANCELED' => 'canceled',
                        default   => 'pending',
                    };

                    return [
                        'step_code'  => $step->approver_label ?? 'Approver',
                        'user'       => $step->actedUser,
                        'name'       => $step->approver_name ?? ($step->actedUser?->name ?? 'Waiting...'),
                        'image'      => $step->signature_url,
                        'at'         => $step->acted_at,
                        'status'     => $uiStatus,
                        'is_current' => $this->approvalRequest->current_step == $step->sequence && $this->approvalRequest->status === 'IN_REVIEW',
                        'source'     => 'approval_system',
                    ];
                });

            if ($approvalSteps->isNotEmpty()) {
                return $approvalSteps->toArray();
            }
        }

        // 2. Legacy Fallback
        $legacySlots = [
            ['col' => 'created_autograph', 'label' => 'Dibuat'],
            ['col' => 'is_known_autograph', 'label' => 'Diketahui'],
            ['col' => 'approved_autograph', 'label' => 'Disetujui'],
        ];

        foreach ($legacySlots as $slot) {
            $val = $this->{$slot['col']};
            $steps->push([
                'step_code'  => $slot['label'],
                'user'       => null,
                'name'       => $val ? str_replace(['.png', '.jpg', '.jpeg'], '', $val) : 'Waiting...',
                'image'      => $val ? asset('autographs/' . $val) : null,
                'at'         => $val ? $this->updated_at : null,
                'status'     => $val ? 'signed' : 'pending',
                'is_current' => false,
                'source'     => 'legacy',
            ]);
        }

        return $steps->toArray();
    }

    // Queries
    public function scopeApprovedByDirector($query)
    {
        return $query
            ->whereHas('department', function ($query) {
                $query->where('name', 'QA')->orWhere('name', 'QC');
            })
            ->where('status', 6);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 5);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 7);
    }

    public function scopeFilteredByUser($query, $user)
    {
        $isHead = $user->hasRole('department-head');
        $isGm = $user->hasRole('general-manager');
        $isDirector = $user->hasRole('director');

        if ($user->email == 'nur@daijo.co.id') {
            return $query;
        }

        if ($isDirector) {
            return $query->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($q) {
                    $q->where('name', 'QA')->orWhere('name', 'QC');
                });
        }

        if ($isGm) {
            return $query->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($q) {
                    $q->whereNot(function ($subQ) {
                        $subQ->where('name', 'QA')
                            ->orWhere('name', 'QC')
                            ->orWhere('name', 'MOULDING');
                    });
                });
        }

        // Standard user/Dept head visibility
        if ($isHead) {
            // Logic for head if needed
        }

        // Filter by department
        if (! ($isDirector || $isGm || $user->email === 'nur@daijo.co.id' || $user->hasRole('super-admin'))) {
            $query->whereHas('department', function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->where('id', $user->department->id);
                    if ($user->department->name === 'QA') {
                        $subQ->orWhere('name', 'QC');
                    }
                });
            });

            if ($isHead && $user->department->name === 'LOGISTIC') {
                $query->orWhere(function ($q) {
                    $q->whereHas('department', fn ($deptQ) => $deptQ->where('name', 'STORE'));
                });
            }
        }

        return $query;
    }

    // Other
    protected static function boot()
    {
        parent::boot();

        static::created(function ($report) {
            $prefix = 'MBR';
            $id = $report->id;
            $date = $report->created_at->format('dmY');
            $docNum = "$prefix/$id/$date";

            $report->update(['doc_num' => $docNum]);
        });
    }
}
