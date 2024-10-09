<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportDataSheet implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;
    protected $reports;

    public function __construct($month, $year, $reports)
    {
        $this->month = $month;
        $this->year = $year;
        $this->reports = $reports;
    }

    public function collection()
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return [
            'Rec Date',
            'Customer',
            'Part Number',
            'Part Name',
            'Rec Quantity',
            'Can Use',
            'Can\'t Use',
            'Price',
            'Total Price',
            'Defect Quantity',
            'Defect Category',
            'Defect Info'
        ];
    }

    public function map($report): array
    {
        $rows = [];
        foreach ($report->details as $detail) {
            $partDetails = explode('/', $detail->part_name, 2);
            $partNumber = $partDetails[0];
            $partName = isset($partDetails[1]) ? $partDetails[1] : '';
            $totalPrice = $detail->rec_quantity * $detail->price;

            foreach ($detail->defects as $defect) {
                $rows[] = [
                    $report->rec_date,
                    $report->customer,
                    $partNumber,
                    $partName,
                    $detail->rec_quantity,
                    $detail->can_use,
                    $detail->cant_use,
                    $detail->price,
                    $totalPrice,
                    $defect->quantity,
                    $defect->category->name ?? '-',
                    $defect->remarks
                ];
            }
        }
        return $rows;
    }
}
