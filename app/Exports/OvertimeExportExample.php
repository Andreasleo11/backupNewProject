<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OvertimeExportExample implements FromArray, WithCustomStartCell, WithHeadings
{
    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            ['UPLOAD OVERTIME (HOUR)'], // A1
            ['T/12', 'DATE', 'T/255', 'DATE', 'T/5', 'DATE', 'T/5', 'NUMERIC', 'T/100'], // A2
            [
                'EMPLOYEE ID',
                'OVERTIME DATE',
                'JOB DESCRIPTION',
                'START DATE',
                'START TIME',
                'END DATE',
                'END TIME',
                'BREAK TIME (MINUTE)',
                'REMARK',
            ], // A3
        ];
    }

    public function array(): array
    {
        // Kosongkan data karena kamu hanya butuh header
        return [];
    }
}
