<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\DepartmentComplianceMonthly;
use App\Models\DepartmentComplianceSnapshot;
use App\Notifications\Compliance\DepartmentBelowThreshold;
use App\Services\ComplianceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class UpdateDepartmentComplianceSnapshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $departmentId, public bool $writeMonthly = false)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ComplianceService $svc): void
    {
        $dept = Department::find($this->departmentId);
        if (! $dept) {
            return;
        }

        $prev = \App\Models\DepartmentComplianceSnapshot::where('department_id', $dept->id)->first();
        $previousPercent = $prev?->percent;

        // Use your existing service to compute “percent” and counts
        $percent = (int) round($svc->getScopeCompliancePercent($dept));

        // If you don’t have these helpers, you can compute like your Requirements\Departments page
        $totalReq = $svc->getScopeAssignedRequirementsCount($dept) ?? 0;
        $complete = (int) round($percent * $totalReq / 100);

        // notify if crossed from >=70 to <70
        $threshold = 70;
        if (! is_null($previousPercent) && $previousPercent >= $threshold && $percent < $threshold) {
            $admin = \App\Models\User::where('email', 'raymond@daijo.co.id')->first();
            $yuli = \App\Models\User::where('email', 'yuli@daijo.co.id')->first();
            $notifiedUsers = array_filter([$admin, $yuli]);
            if (! empty($notifiedUsers)) {
                Notification::send($notifiedUsers, new DepartmentBelowThreshold($dept, $percent, $threshold));
            }
            // Or a dedicated Slack “compliance-alerts” notifiable via routeNotificationForSlack
            // Notification::route('slack', config('services.slack.webhook'))->notify(new DepartmentBelowThreshold($dept, $percent));
        }

        DepartmentComplianceSnapshot::updateOrCreate(
            ['department_id' => $dept->id],
            [
                'percent' => $percent,
                'complete_requirements' => $complete,
                'total_requirements' => $totalReq,
                'generated_at' => now(),
            ]
        );

        if ($this->writeMonthly) {
            $month = Carbon::now()->startOfMonth()->toDateString();
            DepartmentComplianceMonthly::updateOrCreate(
                ['department_id' => $dept->id, 'month' => $month],
                ['percent' => $percent]
            );
        }
    }
}
