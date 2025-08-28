<?php

namespace App\Exports;

use App\Models\DetailFormOvertime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OvertimeRejectedSheet implements FromCollection, WithHeadings
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        return DetailFormOvertime::whereBetween("start_date", [$this->start_date, $this->end_date])
            ->where("status", "Rejected")
            ->where(function ($query) {
                $query->whereNull("reason")->orWhere("reason", "!=", "Duplicate Data");
            })
            ->get([
                "NIK",
                "ID",
                "header_id",
                "nama",
                "overtime_date",
                "start_date",
                "start_time",
                "end_date",
                "end_time",
                "job_desc",
                "break",
                "remarks",
                "status",
                "reason",
            ]);
    }

    public function headings(): array
    {
        return [
            "NIK",
            "ID",
            "Header ID",
            "Nama",
            "Tanggal Lembur",
            "Start Date",
            "Start Time",
            "End Date",
            "End Time",
            "Job Desc",
            "Break",
            "Remarks",
            "Status",
            "Reason",
        ];
    }
}
