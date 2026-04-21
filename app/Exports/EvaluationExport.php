<?php

namespace App\Exports;

use App\Domain\Evaluation\Services\DepartmentEmployeeResolver;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class EvaluationExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    protected string $type;

    protected ?int $month;

    protected ?int $year;

    public function __construct(string $type, ?int $month, ?int $year)
    {
        $this->type = $type;
        $this->month = $month;
        $this->year = $year;
    }

    public function query()
    {
        $user = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        try {
            $employees = match ($this->type) {
                'yayasan' => $resolver->resolveYayasanForUser($user),
                'magang' => $resolver->resolveMagangForUser($user),
                default => $resolver->resolveForUser($user),
            };
        } catch (\Throwable) {
            $employees = collect();
        }

        $niks = $employees->pluck('nik')->filter()->values();

        $query = Employee::whereIn('nik', $niks);

        $query->with(['evaluationData' => function ($q) {
            if ($this->month) {
                $q->whereMonth('Month', $this->month);
            }
            if ($this->year) {
                $q->whereYear('Month', $this->year);
            }
        }]);

        return $query;
    }

    public function headings(): array
    {
        $baseHeadings = [
            'NIK',
            'Name',
            'Department',
            'Status',
            'Alpha',
            'Telat',
            'Izin',
            'Sakit',
            'Total Score',
            'Grade',
            'Approval Status',
        ];

        if (in_array($this->type, ['yayasan', 'magang'])) {
            // Add the 9 scoring fields for new system
            array_splice($baseHeadings, 4, 0, [
                'Kemampuan Kerja',
                'Kecerdasan Kerja',
                'Kualitas Kerja',
                'Disiplin Kerja',
                'Kepatuhan Kerja',
                'Lembur',
                'Efektifitas Kerja',
                'Relawan',
                'Integritas',
            ]);
            // Add pengawas for new system
            $baseHeadings[] = 'Graded By';
        } else {
            // Add the 5 scoring fields for regular system
            array_splice($baseHeadings, 4, 0, [
                'Kerajinan Kerja',
                'Kerapian Kerja',
                'Prestasi',
                'Loyalitas',
                'Perilaku Kerja',
            ]);
        }

        return $baseHeadings;
    }

    public function map($employee): array
    {
        $evalData = $employee->evaluationData->first();

        $grade = $evalData ? match (true) {
            $evalData->total >= 91 => 'A',
            $evalData->total >= 71 => 'B',
            $evalData->total >= 61 => 'C',
            default => 'D',
        } : 'Pending';

        $baseData = [
            $employee->nik,
            $employee->name,
            $employee->dept_code,
            $employee->employment_scheme,
            $evalData->Alpha ?? 0,
            $evalData->Telat ?? 0,
            $evalData->Izin ?? 0,
            $evalData->Sakit ?? 0,
            $evalData->total ?? 0,
            $grade,
            $evalData->approval_status ?? 'pending',
        ];

        if (in_array($this->type, ['yayasan', 'magang'])) {
            // Insert the 9 scoring fields
            array_splice($baseData, 4, 0, [
                $evalData->kemampuan_kerja ?? 0,
                $evalData->kecerdasan_kerja ?? 0,
                $evalData->qualitas_kerja ?? 0,
                $evalData->disiplin_kerja ?? 0,
                $evalData->kepatuhan_kerja ?? 0,
                $evalData->lembur ?? 0,
                $evalData->efektifitas_kerja ?? 0,
                $evalData->relawan ?? 0,
                $evalData->integritas ?? 0,
            ]);
            // Add pengawas
            $baseData[] = $evalData->pengawas ?? '';
        } else {
            // Insert the 5 scoring fields
            array_splice($baseData, 4, 0, [
                $evalData->kerajinan_kerja ?? 0,
                $evalData->kerapian_kerja ?? 0,
                $evalData->prestasi ?? 0,
                $evalData->loyalitas ?? 0,
                $evalData->perilaku_kerja ?? 0,
            ]);
        }

        return $baseData;
    }
}
