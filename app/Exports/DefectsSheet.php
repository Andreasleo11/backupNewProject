<?php

namespace App\Exports;

use App\Models\Defect;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DefectsSheet implements FromCollection, WithTitle, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Defect::all();
    }

    public function headings(): array
    {
        // Get column names from the defects table
        $columnNames = Schema::getColumnListing("defects");

        // Return the column names as headers
        return $columnNames;
    }
    public function title(): string
    {
        return "Defects"; // Custom title for the sheet
    }
}
