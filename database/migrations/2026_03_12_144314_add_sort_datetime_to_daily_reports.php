<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_daily_reports', function (Blueprint $table) {
            $table->timestamp('sort_datetime')->nullable()->after('work_time');
            $table->index('sort_datetime');
            $table->index('employee_id');
        });

        // Backfill data
        DB::table('employee_daily_reports')->orderBy('id')->chunk(500, function ($reports) {
            foreach ($reports as $report) {
                $time = $report->work_time;
                $date = $report->work_date;
                $timePart = '00:00:00';

                // Handle "HH:MM - HH:MM"
                if (str_contains($time, '-')) {
                    $parts = explode('-', $time);
                    $endTime = trim(end($parts));
                    if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]/', $endTime, $matches)) {
                        $timePart = $matches[0] . ':00';
                    }
                }
                // Handle "HH:MM"
                elseif (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]/', $time, $matches)) {
                    $timePart = $matches[0] . ':00';
                }

                try {
                    $sortDatetime = \Carbon\Carbon::parse("$date $timePart");
                    DB::table('employee_daily_reports')
                        ->where('id', $report->id)
                        ->update(['sort_datetime' => $sortDatetime]);
                } catch (\Exception $e) {
                    // Skip if parsing fails
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_daily_reports', function (Blueprint $table) {
            $table->dropIndex(['sort_datetime']);
            $table->dropIndex(['employee_id']);
            $table->dropColumn('sort_datetime');
        });
    }
};
