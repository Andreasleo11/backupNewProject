<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\DetailFormOvertime;
use Illuminate\Support\Facades\DB;

class OvertimeSummarySheet implements FromView
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
        $summary = DetailFormOvertime::query()
            ->select(
                "NIK",
                "name",
                DB::raw("MIN(start_date) as start_date"),
                DB::raw("MAX(end_date) as end_date"),
                DB::raw("SUM(TIMESTAMPDIFF(MINUTE, 
                    STR_TO_DATE(CONCAT(start_date, ' ', start_time), '%Y-%m-%d %H:%i:%s'), 
                    STR_TO_DATE(CONCAT(end_date, ' ', end_time), '%Y-%m-%d %H:%i:%s')
                ) - `break`) / 60 as total_ot"),
            )
            ->whereBetween("start_date", [$this->start_date, $this->end_date])
            ->whereNull("deleted_at")
            ->where("status", "Approved")
            ->groupBy("NIK", "name")
            ->get();

        return view("formovertime.export_summary_excel", compact("summary"));
    }
}
