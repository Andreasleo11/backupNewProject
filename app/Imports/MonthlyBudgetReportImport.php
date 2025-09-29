<?php

namespace App\Imports;

use App\Models\MonthlyBudgetReportDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MonthlyBudgetReportImport implements ToCollection
{
    protected $deptNo;

    protected $reportDate;

    protected $headerId;

    public function __construct($deptNo, $reportDate, $headerId)
    {
        $this->deptNo = $deptNo;
        $this->reportDate = $reportDate;
        $this->headerId = $headerId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Skip the header row if necessary
            if ($index === 0) {
                continue;
            }

            if ($this->deptNo == 363) {
                // Assuming your Excel columns are in order:
                $name = $row[0];
                $spec = $row[1];
                $uom = $row[2];
                $lastRecordedStock = $row[3];
                $usagePerMonth = $row[4];
                $quantity = $row[5];
                $remark = $row[6];

                // Create MonthlyBudgetReportDetail entry
                MonthlyBudgetReportDetail::create([
                    'header_id' => $this->headerId, // Assign the header_id here
                    'name' => $name,
                    'spec' => $spec,
                    'uom' => $uom,
                    'last_recorded_stock' => $lastRecordedStock,
                    'usage_per_month' => $usagePerMonth,
                    'quantity' => $quantity,
                    'remark' => $remark,
                ]);
            } else {
                $name = $row[0];
                $uom = $row[1];
                $quantity = $row[2];
                $remark = $row[3];

                // Create MonthlyBudgetReportDetail entry
                MonthlyBudgetReportDetail::create([
                    'header_id' => $this->headerId, // Assign the header_id here
                    'name' => $name,
                    'spec' => null,
                    'uom' => $uom,
                    'last_recorded_stock' => null,
                    'usage_per_month' => null,
                    'quantity' => $quantity,
                    'remark' => $remark,
                ]);
            }
        }
    }
}
