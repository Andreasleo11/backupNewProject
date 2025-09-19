<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\DetailFormOvertime;

class OvertimeDetailSheet implements FromView
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function view(): View
    {
        $details = DetailFormOvertime::with("actualOvertimeDetail")
            ->whereBetween("start_date", [$this->start_date, $this->end_date])
            ->where("status", "Approved")
            ->get();

        return view("formovertime.export_detail", compact("details"));
    }
}
