<?php

namespace App\Exports;

use App\Models\MasterInventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryMasterExport implements FromCollection, WithHeadings
{
    /**
     * Return a collection of data to be exported.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return MasterInventory::select(
            'ip_address',
            'username',
            'dept',
            'type',
            'purpose',
            'brand',
            'os',
            'description',
        )->get();
    }

    /**
     * Define the headings for the Excel file.
     */
    public function headings(): array
    {
        return [
            'IP Address',
            'Username',
            'Department',
            'Type',
            'Purpose',
            'Brand',
            'Operating System',
            'Description',
        ];
    }
}
