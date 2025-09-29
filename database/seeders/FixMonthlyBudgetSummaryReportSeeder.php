<?php

namespace Database\Seeders;

use App\Models\MonthlyBudgetSummaryReport;
use Illuminate\Database\Seeder;

class FixMonthlyBudgetSummaryReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->fixAll();
    }

    private function fixAll()
    {
        $reports = MonthlyBudgetSummaryReport::all();
        foreach ($reports as $report) {
            $this->updateDocNum($report);
        }
    }

    private function updateDocNum($report)
    {
        $prefix = 'MBSR';
        $id = $report->id;
        $date = $report->created_at->format('dmY');
        $docNum = "$prefix/$id/$date";

        $report->update(['doc_num' => $docNum]);
    }
}
