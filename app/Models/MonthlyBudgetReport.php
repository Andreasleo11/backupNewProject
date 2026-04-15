<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MonthlyBudgetReport extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'dept_no',
        'creator_id',
        'report_date',
        'doc_num',
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
        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Get cancellation reason from approval action.
     */
    public function getCancellationReasonAttribute(): ?string
    {
        return $this->approvalRequest?->actions()
            ->where('to_status', 'CANCELED')
            ->latest()
            ->first()?->remarks;
    }

    /**
     * Check if the report is currently a draft or returned.
     */
    public function isDraft(): bool
    {
        $status = $this->workflow_status;

        return $status === 'DRAFT' || $status === 'RETURNED';
    }

    /**
     * Get current workflow step (approver label).
     */
    public function getWorkflowStepAttribute(): ?string
    {
        $approval = $this->approvalRequest;
        if (! $approval || $approval->status !== 'IN_REVIEW') {
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
        if (! $this->approvalRequest) {
            return [];
        }

        return $this->approvalRequest->steps()
            ->orderBy('sequence')
            ->with('actedUser')
            ->get()
            ->map(function ($step) {
                $uiStatus = match ($step->status) {
                    'APPROVED' => 'signed',
                    'REJECTED' => 'rejected',
                    'CANCELED' => 'canceled',
                    default => 'pending',
                };

                return [
                    'step_code' => $step->approver_label ?? 'Approver',
                    'user' => $step->actedUser,
                    'name' => $step->approver_name ?? ($step->actedUser?->name ?? 'Waiting...'),
                    'image' => $step->signature_url,
                    'at' => $step->acted_at,
                    'status' => $uiStatus,
                    'is_current' => $this->approvalRequest->current_step == $step->sequence && $this->approvalRequest->status === 'IN_REVIEW',
                    'source' => 'approval_system',
                ];
            })
            ->toArray();
    }

    // Queries

    public function scopeFilteredByUser($query, $user)
    {
        // 1. Admins & Special Overrides
        if ($user->hasRole('super-admin') || $user->email === 'nur@daijo.co.id' || $user->hasRole('purchaser')) {
            return $query;
        }

        $isHead = $user->hasRole('department-head');
        $isGm = $user->hasRole('general-manager');
        $isDirector = $user->hasRole('director');

        return $query->where(function ($q) use ($user, $isHead, $isGm, $isDirector) {
            // A. Creator always sees their own
            $q->where('creator_id', $user->id);

            // B. Department Head Logic
            if ($isHead) {
                $q->orWhere(function ($hq) use ($user) {
                    // Must be in their department (including special cross-dept roles)
                    $hq->whereHas('department', function ($dq) use ($user) {
                        $dq->where('id', $user->department_id);
                        if ($user->department?->name === 'QA') {
                            $dq->orWhere('name', 'QC');
                        }
                        if ($user->department?->name === 'LOGISTIC') {
                            $dq->orWhere('name', 'STORE');
                        }
                    })->where(function ($sq) {
                        // AND (In Review & Waiting for them at Step 1) OR (Acted on Step 1) OR (Finished)
                        $sq->whereHas('approvalRequest', function ($aq) {
                            $aq->where(function ($sub) {
                                $sub->where('status', 'IN_REVIEW')->where('current_step', 1);
                            })->orWhere(function ($sub) {
                                $sub->whereHas('steps', fn ($stepQ) => $stepQ->where('sequence', 1)->whereNotNull('acted_at'));
                            })->orWhereIn('status', ['APPROVED', 'REJECTED', 'CANCELED']);
                        })->orWhereDoesntHave('approvalRequest'); // Drafts in their dept
                    });
                });
            }

            // C. GM Logic
            if ($isGm) {
                $q->orWhere(function ($gq) {
                    $gq->whereHas('approvalRequest', function ($aq) {
                        $aq->where(function ($sub) {
                            // Waiting for them at Step 2 (Standard/Moulding)
                            $sub->where('status', 'IN_REVIEW')->where('current_step', 2);
                        })->orWhere(function ($sub) {
                            // Already acted at Step 2
                            $sub->whereHas('steps', fn ($stepQ) => $stepQ->where('sequence', 2)->whereNotNull('acted_at'));
                        })->orWhereIn('status', ['APPROVED', 'REJECTED', 'CANCELED']);
                    })->whereHas('department', function ($dq) {
                        // Preserve existing exclusions for GM (Director handles QA/QC)
                        $dq->whereNotIn('name', ['QA', 'QC']);
                    });
                });
            }

            // D. Director Logic
            if ($isDirector) {
                $q->orWhere(function ($dq) {
                    $dq->whereHas('department', fn ($deptQ) => $deptQ->whereIn('name', ['QA', 'QC']))
                        ->whereHas('approvalRequest', function ($aq) {
                            $aq->where(function ($sub) {
                                // Waiting for them (Director is Step 2 for QA/QC)
                                $sub->where('status', 'IN_REVIEW')->where('current_step', 2);
                            })->orWhere(function ($sub) {
                                // Already acted
                                $sub->whereHas('steps', fn ($stepQ) => $stepQ->where('sequence', 2)->whereNotNull('acted_at'));
                            })->orWhereIn('status', ['APPROVED', 'REJECTED', 'CANCELED']);
                        });
                });
            }
        });
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

    // --- Approvable Interface ---

    public function getApprovableTypeLabel(): string
    {
        return 'Monthly Budget Report';
    }

    public function getApprovableIdentifier(): string
    {
        return $this->doc_num;
    }

    public function getApprovableShowUrl(): string
    {
        return route('monthly-budget-reports.show', $this->id);
    }
}
