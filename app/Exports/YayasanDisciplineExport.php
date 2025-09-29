<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class YayasanDisciplineExport implements FromCollection, WithCustomStartCell, WithHeadings, WithMapping, WithStrictNullComparison
{
    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->result);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            ['MANUAL ENTRY PAYROLL'], // A1
            ['ALLOWANCE', 'TUNJANGAN INSENTIF YAYASAN'], // A1
            ['T/12', 'NUMERIC', 'NUMERIC'], // A2
            ['EMPLOYEE ID', 'NILAI_A ', 'NILAI_B'], // A4
        ];
    }

    public function map($result): array
    {
        return [$result['employee_id'], $result['nilai_A'] ?? 0, $result['nilai_B'] ?? 0];
    }
}
