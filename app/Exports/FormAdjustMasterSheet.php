<?php

namespace App\Exports;

use App\Models\FormAdjustMaster;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FormAdjustMasterSheet implements FromCollection, WithTitle, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return FormAdjustMaster::all();
    }

    public function headings(): array
    {
        // Get column names from the details table
        $columnNames = Schema::getColumnListing("form_adjust_masters");

        // Return the column names as headers
        return $columnNames;
    }

    public function title(): string
    {
        return "Form Adjust Master"; // Custom title for the sheet
    }
}
