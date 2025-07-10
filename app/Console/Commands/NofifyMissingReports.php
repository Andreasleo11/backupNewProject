<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeDailyReport;
use App\Models\User;
use App\Notifications\MissingDailyReportsNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NofifyMissingReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:missing-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify department heads about missing employee daily reports until today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $this->info("Checking for missing reports as of " . $today->toDateString());

        $deptHeads = User::where('is_head', true)->get();

        foreach ($deptHeads as $head) {
            $headDept = $head->department->dept_no ?? null;

            if (!$headDept) {
                $this->warn("No department found for user {$head->name}");
                continue;
            }

            $employees = Employee::where('Dept', $headDept)
                ->whereNull('end_date')
                ->get();

            $missingReports = [];

            foreach ($employees as $employee) {
                $startDate = Carbon::now()->startOfMonth();
                $expectedDates = [];

                for ($date = $startDate->copy(); $date->lt($today); $date->addDay()) {
                    // skip weekends if needed:
                    // if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) continue;

                    $expectedDates[] = $date->toDateString();
                }

                $submittedDates = EmployeeDailyReport::where('employee_id', $employee->NIK)
                    ->whereDate('work_date', '<', $today)
                    ->pluck('work_date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();

                $missingDates = collect($expectedDates)->diff($submittedDates)->values()->toArray();

                if (count($missingDates) > 0) {
                    $missingReports[] = [
                        'employee' => $employee,
                        'dates' => $missingDates,
                    ];
                }
            }

            if (count($missingReports) > 0 && $head->name === 'raymond') {
                Notification::send($head, new MissingDailyReportsNotification(($missingReports)));

                $this->info("Notification sent to {$head->email} for " . count($missingReports) . " missing reports.");
            } else {
                $this->line("No missing reports for {$head->name}");
            }
        }

        $this->info("Done.");
    }
}
