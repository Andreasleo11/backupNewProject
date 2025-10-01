<?php

namespace Database\Seeders;

use App\Models\MonthlyBudgetReport;
use Illuminate\Database\Seeder;

class FixMonthlyBudgetReportSeeder extends Seeder
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
        $reports = MonthlyBudgetReport::all();
        foreach ($reports as $report) {
            // $this->updateStatus($report);
            $this->updateDocNum($report);
        }
    }

    private function updateStatus($report)
    {
        if ($report->is_reject === 1) {
            $report->status = 7;
        } else {
            if ($report->approved_autograph) {
                $report->status = 6;
            } elseif ($report->is_known_autograph) {
                if ($report->department->name === 'MOULDING') {
                    $report->status = 3;
                } elseif (
                    $report->department->name === 'QA' ||
                    $report->department->name === 'QC'
                ) {
                    $report->status = 5;
                } else {
                    $report->status = 4;
                }
            } elseif ($report->created_autograph) {
                $report->status = 2;
            }
        }

        $report->save();
    }

    private function updateDocNum($report)
    {
        $prefix = 'MBR';
        $id = $report->id;
        $date = $report->created_at->format('dmY');
        $docNum = "$prefix/$id/$date";
        $report->update(['doc_num' => $docNum]);
    }
}
