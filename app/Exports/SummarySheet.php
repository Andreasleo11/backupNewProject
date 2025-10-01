<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SummarySheet implements FromCollection, WithHeadings
{
    protected $summaryData;

    public function __construct($summaryData)
    {
        $this->summaryData = $summaryData;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->summaryData as $customer => $categories) {
            foreach ($categories as $category => $quantity) {
                $data[] = [
                    'Customer' => $customer,
                    'Defect Category' => $category,
                    'Total Quantity' => $quantity,
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return ['Customer', 'Defect Category', 'Total Quantity'];
    }
}
