<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Notifications\MonthlyBudgetSummaryReportCreated;
use App\Notifications\MonthlyBudgetSummaryReportUpdated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MonthlyBudgetSummaryReport extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'report_date',
        'creator_id',
        'doc_num',
        'is_moulding',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $appends = ['total_amount', 'mom'];

    public function scopeWithTotals(Builder $q): Builder
    {
        return $q->addSelect([
            'total_amount' => DB::table('monthly_budget_report_summary_details as d')
                ->selectRaw('COALESCE(SUM(d.quantity * d.cost_per_unit), 0)')
                ->whereColumn('d.header_id', 'monthly_budget_summary_reports.id'),
        ]);
    }

    public function scopeWithPrevTotals(Builder $q): Builder
    {
        return $q->addSelect([
            'prev_total' => DB::table('monthly_budget_report_summary_details as d2')
                ->join('monthly_budget_summary_reports as r2', 'r2.id', '=', 'd2.header_id')
                ->selectRaw('COALESCE(SUM(d2.quantity * d2.cost_per_unit), 0)')
                ->whereColumn('r2.creator_id', 'monthly_budget_summary_reports.creator_id')
                ->whereRaw(
                    'r2.report_date = DATE_SUB(monthly_budget_summary_reports.report_date, INTERVAL 1 MONTH)',
                ),
        ]);
    }

    public function getTotalAmountAttribute(): float
    {
        // Use preloaded selectSub if present; otherwise fall back to eager-loaded details
        if (array_key_exists('total_amount', $this->attributes)) {
            return (float) $this->attributes['total_amount'];
        }

        return $this->relationLoaded('details')
            ? (float) $this->details->sum(
                fn ($d) => (float) ($d->quantity ?? 0) * (float) ($d->cost_per_unit ?? 0),
            )
            : 0.0;
    }

    public function getMomAttribute(): array
    {
        $curr = (float) $this->total_amount;
        $prev = (float) ($this->attributes['prev_total'] ?? 0.0);

        if ($prev <= 0) {
            return [
                'has_prev' => false,
                'diff' => null,
                'pct' => null,
                'direction' => 'none',
                'prev' => $prev,
            ];
        }

        $diff = $curr - $prev;
        $pct = ($diff / $prev) * 100;

        return [
            'has_prev' => true,
            'diff' => $diff,
            'pct' => $pct,
            'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
            'prev' => $prev,
        ];
    }

    // Relations
    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportSummaryDetail::class, 'header_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
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
        if (!$this->approvalRequest) {
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
            })
            ->toArray();
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    // Queries

    public function scopeFilteredByUser($query, $user)
    {
        if ($user->hasRole('super-admin') || $user->email === 'nur@daijo.co.id' || $user->hasRole('purchaser')) {
            return $query;
        }

        $isGm = $user->hasRole('general-manager');
        $isDirector = $user->hasRole('director');

        return $query->where(function ($q) use ($user, $isGm, $isDirector) {
            // A. Creator always sees their own
            $q->where('creator_id', $user->id);

            // B. GM Logic (Step 1 for Summary)
            if ($isGm) {
                $q->orWhere(function ($gq) {
                    $gq->whereHas('approvalRequest', function ($aq) {
                        $aq->where(function ($sub) {
                            $sub->where('status', 'IN_REVIEW')->where('current_step', 1);
                        })->orWhere(function ($sub) {
                            $sub->whereHas('steps', fn($stepQ) => $stepQ->where('sequence', 1)->whereNotNull('acted_at'));
                        })->orWhereIn('status', ['APPROVED', 'REJECTED', 'CANCELED']);
                    });
                });
            }

            // C. Director Logic (Step 2 for Summary)
            if ($isDirector) {
                $q->orWhere(function ($dq) {
                    $dq->whereHas('approvalRequest', function ($aq) {
                         $aq->where(function ($sub) {
                            $sub->where('status', 'IN_REVIEW')->where('current_step', 2);
                        })->orWhere(function ($sub) {
                            $sub->whereHas('steps', fn($stepQ) => $stepQ->where('sequence', 2)->whereNotNull('acted_at'));
                        })->orWhereIn('status', ['APPROVED', 'REJECTED', 'CANCELED']);
                    });
                });
            }
        });
    }

    /**
     * Check if the report is currently a draft or returned.
     */
    public function isDraft(): bool
    {
        $status = $this->workflow_status;
        return $status === 'DRAFT' || $status === 'RETURNED';
    }

    // Other
    protected static function boot()
    {
        parent::boot();

        static::created(function ($report) {
            $prefix = 'MBSR';
            if ($report->is_moulding) {
                $prefix = $prefix . '/MOULD';
            }
            $id = $report->id;
            $date = $report->created_at->format('dmY');
            $docNum = "$prefix/$id/$date";

        });
    }

}
