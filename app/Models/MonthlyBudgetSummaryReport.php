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
        'created_autograph',
        'is_known_autograph',
        'approved_autograph',
        'doc_num',
        'is_reject',
        'reject_reason',
        'is_moulding',
        'is_cancel',
        'cancel_reason',
        'status',
        'workflow_status',
        'workflow_step',
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
        if ((int) $this->is_cancel === 1) {
            return 'CANCELED';
        }
        return $this->approvalRequest?->status ?? 'DRAFT';
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

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    // Queries
    public function scopeApproved($query)
    {
        return $query->where('status', 5);
    }

    public function scopeWaitingDirector($query)
    {
        return $query->where('status', 4);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 5);
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

            $report->update(['doc_num' => $docNum]);

            $report->sendNotification('created');
        });

        static::updated(function ($report) {
            if ($report->isDirty('status')) {
                $report->sendNotification('updated');
            }
        });
    }

    private function sendNotification($event)
    {
        $details = $this->prepareNotificationdetails();
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails()
    {
        $status = $this->getStatusText($this->status);

        $commonDetails = [
            'greeting' => 'Monthly Budget Summary Report Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('monthly.budget.summary.report.show', $this->id),
        ];

        $reportDate = \Carbon\Carbon::parse($this->report_date)->format('F Y');

        $commonDetails['body'] = "Notification for Monthly Budget Summary Report: <br>
            - Document Number : $this->doc_num <br>
            - Month : $reportDate <br>
            - Status : $status";

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'Waiting Creator';
            case 2:
                return 'Waiting GM';
            case 3:
                return 'Waiting Dept Head';
            case 4:
                return 'Waiting Director';
            case 5:
                return 'Approved';
            case 6:
                return 'Rejected';
            case 7:
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    }

    private function notifyUsers($details, $event)
    {
        $creator = $this->user; // Convert to array
        $users = [];
        array_push($users, $creator);

        if ($event === 'created') {
            // $creator[0]->notify(new MonthlyBudgetSummaryReportCreated($this, $details));
        } elseif ($event === 'updated') {
            if ($this->status == 2) {
                $gm = User::where('is_gm', 1)->first();
                array_push($users, $gm);
            } elseif ($this->status == 3) {
                $mouldingHead = User::role('DESIGN')
                    ->whereHas('department', function ($query) {
                        $query->where('name', 'MOULDING');
                    })
                    ->where('is_head', 1)
                    ->first();
                array_push($users, $mouldingHead);
            } elseif ($this->status == 4) {
                $director = User::role('DIRECTOR')->first();
                array_push($users, $director);
            }

            Notification::send($users, new MonthlyBudgetSummaryReportUpdated($this, $details));
        }
    }
}
