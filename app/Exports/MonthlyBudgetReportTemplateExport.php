<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyBudgetReportTemplateExport implements FromArray, WithHeadings
{
    protected $deptNo;

    public function __construct($deptNo)
    {
        $this->deptNo = $deptNo;
    }

    public function array(): array
    {
        // Define header row for the Excel template

        if($this->deptNo == 363){
            // If Moulding
            return [
                ['Name', 'Spec', 'UoM', 'Last Recorded Stock', 'Usage Per Month', 'Quantity Request', 'Remark']
            ];
        } else {
            return [
                ['Name', 'UoM', 'Quantity Request', 'Remark']
            ];
        }
    }

    public function headings(): array
    {
        // Return an empty array because headings are already defined in array() method
        return [];
    }
}
