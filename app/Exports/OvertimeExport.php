<?php

namespace App\Exports;

use App\Models\DetailFormOvertime;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;


class OvertimeExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell
{

    protected $header;
    protected $datas;

    public function __construct($header, $datas)
    {
        $this->header = $header;
        $this->datas = $datas;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         return collect($this->datas);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            ['UPLOAD OVERTIME (HOUR)'], // A1
            ['T/12', 'DATE', 'T/255', 'DATE', 'T/5', 'DATE', 'T/5', 'NUMERIC', 'T/100'], // A2
            ['EMPLOYEE ID', 'OVERTIME DATE', 'JOB DESCRIPTION', 'START DATE', 'START TIME', 'END DATE', 'END TIME', 'BREAK TIME (MINUTE)', 'REMARK'] // A4
        ];
    }


    public function map($data): array
    {
        return [
            $data->NIK,
            Carbon::parse($data->start_date)->format('d/m/Y'),
            $data->job_desc,
            Carbon::parse($data->start_date)->format('d/m/Y'),
            Carbon::parse($data->start_time)->format('H:i'),
            Carbon::parse($data->end_date)->format('d/m/Y'),
            Carbon::parse($data->end_time)->format('H:i'),
            $data->break,
            $data->remarks,
        ];
    }
}
