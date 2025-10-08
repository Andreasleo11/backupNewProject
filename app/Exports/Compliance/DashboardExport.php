<?php

namespace App\Exports\Compliance;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $rows) {}

    public function headings(): array
    {
        return ['Code', 'Department', 'Percent'];
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($r) => [
            $r->department->code,
            $r->department->name,
            $r->percent,
        ]);
    }
}
