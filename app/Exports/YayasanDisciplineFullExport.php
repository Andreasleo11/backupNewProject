<?php

namespace App\Exports;

use App\Models\EvaluationData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;


class YayasanDisciplineFullExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithStrictNullComparison
{

    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         return collect($this->employees);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Karyawan Name',
            'Status', 
            'Start Date',
            'Month',
            'Alpha',
            'Telat',
            'Izin',
            'Sakit',
            'Kemampuan Kerja',
            'Kecerdasan Kerja',
            'Qualitas Kerja',
            'Disiplin Kerja',
            'Kepatuhan Kerja',
            'Lembur',
            'Efektifitas Kerja',
            'Relawan',
            'Integritas',
            'Total', // Add more column titles as needed
        ];
    }


    public function map($row): array
    {
        return [
            $row->id,
            $row->karyawan->Nama ?? 'N/A', // Assuming 'karyawan' relation has 'name'
            $row->karyawan->status ?? 'N/A',
            $row->karyawan->start_date ?? 'N/A',
            $row->Month,
            $row->Alpha,
            $row->Telat,
            $row->Izin,
            $row->Sakit,
            $row->kemampuan_kerja,
            $row->kecerdasan_kerja,
            $row->qualitas_kerja,
            $row->disiplin_kerja,
            $row->kepatuhan_kerja,
            $row->lembur,
            $row->efektifitas_kerja,
            $row->relawan,
            $row->integritas,
            $row->total,
        ];
    }
}
