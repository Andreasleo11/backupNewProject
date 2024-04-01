<?php

namespace App\Exports;

use App\Models\Detail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailsSheet implements FromCollection, WithTitle, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Detail::all();
    }

    public function headings(): array
    {
         // Get column names from the details table
         $columnNames = Schema::getColumnListing('details');

         // Return the column names as headers
         return $columnNames;
    }

    public function title(): string
    {
        return 'Details'; // Custom title for the sheet
    }

}
