<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TempDailyReportSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing temporary data to prevent bloat
        DB::table('employee_daily_reports')->truncate();

        // Query up to 15 REAL employees to seed data for
        $realEmployees = \App\Infrastructure\Persistence\Eloquent\Models\Employee::whereNotNull('dept_code')
            ->whereNotNull('name')
            ->limit(15)
            ->get(['nik', 'name', 'dept_code']);

        if ($realEmployees->isEmpty()) {
            echo "No real employees found with departments to seed.\n";
            return;
        }

        $activities = [
            'Fixing bug in production', 'Deploying new feature', 'Server maintenance', 
            'Database optimization', 'Attending sprint planning', 'Interviewing candidates',
            'Processing payroll', 'Employee onboarding', 'Updating company policies',
            'Conflict resolution meeting', 'Reconciling accounts', 'Preparing tax reports',
            'Reviewing expense claims', 'Budget forecasting', 'Audit preparation'
        ];

        $records = [];
        $today = Carbon::today();

        // Generate data for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->subDays($i);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($realEmployees as $emp) {
                // 10% chance an employee forgot to submit a report on a given day
                if (rand(1, 100) <= 10) {
                    continue;
                }

                // Create a random number of activity entries for the day (e.g., 1 to 3 tasks logged)
                $numTasks = rand(1, 4);
                
                for ($j = 0; $j < $numTasks; $j++) {
                    $task = $activities[array_rand($activities)];
                    $hours = rand(1, 4);
                    // Generate a semi-random submission time (e.g., end of the day)
                    $submittedAt = $date->copy()->addHours(rand(16, 20))->addMinutes(rand(0, 59));

                    $records[] = [
                        'submitted_at' => $submittedAt,
                        'report_type' => 'Daily',
                        'employee_id' => $emp->nik,
                        'departement_id' => $emp->dept_code,
                        'employee_name' => $emp->name,
                        'work_date' => $date->format('Y-m-d'),
                        'work_time' => "{$hours} Hours",
                        'work_description' => "{$task} - Completed successfully.",
                        'proof_url' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Chunk inserts to avoid memory exhaustion
        foreach (array_chunk($records, 100) as $chunk) {
            DB::table('employee_daily_reports')->insert($chunk);
        }
    }
}
