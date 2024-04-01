<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements FromQuery, WithMultipleSheets
{
    use Exportable;

    /**
     * Query to fetch all records from the reports table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return Report::query();
    }

    public function sheets(): array
    {
        $sheets = [];

        // Add a sheet for exporting reports data
        $sheets[] = new ReportsSheet($this->query());

        // Add sheets for exporting details, defects, and defect categories
        $sheets[] = new DetailsSheet();
        // Add sheets for defects and defect categories similarly

        $sheets[] = new DefectsSheet();
        $sheets[] = new DefectCategoriesSheet();

        return $sheets;
    }

}
