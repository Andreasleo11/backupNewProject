<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OvertimeSummaryExport implements WithMultipleSheets
{
    protected $start_date;

    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function sheets(): array
    {
        return [
            new OvertimeSummarySheet($this->start_date, $this->end_date), // Sheet 1: Ringkasan
            new OvertimeDetailSheet($this->start_date, $this->end_date), // Sheet 2: Detail semua data
            new OvertimeRejectedSheet($this->start_date, $this->end_date),
        ];
    }
}
