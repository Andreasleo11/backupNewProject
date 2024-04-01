<?php

namespace App\Exports;

use App\Models\DefectCategory;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DefectCategoriesSheet implements FromCollection, WithTitle, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DefectCategory::all();
    }

    public function headings(): array
    {
         // Get column names from the defect_categories table
         $columnNames = Schema::getColumnListing('defect_categories');

         // Return the column names as headers
         return $columnNames;
    }

    public function title(): string
    {
        return 'Defect Categories'; // Custom title for the sheet
    }
}
