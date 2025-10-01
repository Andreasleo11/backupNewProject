<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HeaderFormAdjustSheet implements FromCollection, WithHeadings, WithTitle
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
     */
    public function headings(): array
    {
        // Get column names from the reports table
        $columnNames = Schema::getColumnListing('header_form_adjusts');

        // Return the column names as headers
        return $columnNames;
    }

    public function title(): string
    {
        return 'Header Form Adjust'; // Custom title for the sheet
    }
}
