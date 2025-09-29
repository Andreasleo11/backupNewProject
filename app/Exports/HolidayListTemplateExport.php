<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class HolidayListTemplateExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([['Date', 'Holiday Name', 'Description', 'Is Half Day']]);
    }
}
