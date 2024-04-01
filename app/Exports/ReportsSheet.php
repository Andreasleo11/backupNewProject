<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsSheet implements FromCollection, WithTitle, WithHeadings
{
    /**
     * Collection of data to be exported.
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Query to fetch data for the sheet.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function collection()
    {
        return $this->query->get();
    }

    /**
     * Specify the headers for the exported data.
     *
     * @return array
     */
    public function headings(): array
    {
        // Get column names from the reports table
        $columnNames = Schema::getColumnListing('reports');

        // Return the column names as headers
        return $columnNames;
    }

    public function title(): string
    {
        return 'Reports'; // Custom title for the sheet
    }
}
