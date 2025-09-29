<?php

namespace App\Imports;

use App\Models\UtiHolidayList;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class HolidayListTemplateImport implements ToCollection
{
    /**
     * @param  Collection  $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                // Skip the header row
                continue;
            }

            // Convert the Excel serial date to a human-readable date format
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[0])->format(
                'Y-m-d',
            );

            // Insert the data into the database
            UtiHolidayList::create([
                'date' => $date,
                'holiday_name' => $row[1],
                'description' => $row[2],
                'half_day' => $row[3],
            ]);
        }
    }
}
