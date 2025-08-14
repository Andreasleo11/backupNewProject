<?php

namespace App\Console\Commands;

use App\Models\HeaderFormOvertime;
use Illuminate\Console\Command;
use App\Notifications\DailyOvertimeSummaryNotification;
use App\Models\User;

class NotifyOvertimeApprovers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:overtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily overtime reminders to approvers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statuses = [
            'waiting-dept-head',
            'waiting-supervisor',
            'waiting-verificator',
            'waiting-director',
            'waiting-gm',
        ];

        foreach ($statuses as $status) {
            $reports = HeaderFormOvertime::with(['department', 'user'])
                ->where('status', $status)
                ->get();

            if ($reports->isEmpty()) continue;

            switch ($status) {
                case 'waiting-dept-head':
                    $grouped = $reports->groupBy('dept_id');
                    foreach ($grouped as $deptId => $reportsGroup) {
                        $user = User::where('is_head', 1)
                            ->where('department_id', $deptId)
                            ->first();

                        if ($user) $user->notify(new DailyOvertimeSummaryNotification($reportsGroup, $status));
                    }
                    break;

                case 'waiting-supervisor':
                    $user = User::whereHas('specification', fn($q) => $q->where('name', 'SUPERVISOR'))->first();
                    if ($user) $user->notify(new DailyOvertimeSummaryNotification($reports, $status));
                    break;

                case 'waiting-verificator':
                    $user = User::whereHas('specification', fn($q) => $q->where('name', 'VERIFICATOR'))->first();
                    if ($user) $user->notify(new DailyOvertimeSummaryNotification($reports, $status));
                    break;

                case 'waiting-director':
                    $user = User::whereHas('specification', fn($q) => $q->where('name', 'DIRECTOR'))->first();
                    if ($user) $user->notify(new DailyOvertimeSummaryNotification($reports, $status));
                    break;

                case 'waiting-gm':
                    $grouped = $reports->groupBy('branch');
                    foreach ($grouped as $branch => $reportsGroup) {
                        $email = $branch === 'Karawang'
                            ? 'pawarid_pannin@daijo.co.id'
                            : 'albert@daijo.co.id';

                        $user = User::where('email', $email)->first();
                        if ($user) $user->notify(new DailyOvertimeSummaryNotification($reportsGroup, $status));
                    }
                    break;
            }
        }
    }
}
