<?php

namespace App\Exports;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonthlyVerificationDataSheet implements FromCollection, WithHeadings, WithMapping
{
    protected int $month;

    protected int $year;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    public function collection()
    {
        return VerificationReport::with('items.defects')
            ->whereMonth('rec_date', $this->month)
            ->whereYear('rec_date', $this->year)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Rec Date', 'Customer', 'Part Number', 'Part Name',
            'Rec Quantity', 'Can Use', "Can't Use", 'Price',
            'Total Price', 'Defect Quantity', 'Defect Name', 'Defect Notes',
        ];
    }

    public function map($report): array
    {
        $rows = [];

        foreach ($report->items as $item) {
            $parts      = explode('/', $item->part_name, 2);
            $partNumber = $parts[0];
            $partName   = $parts[1] ?? '';
            $totalPrice = (float) $item->rec_quantity * (float) $item->price;

            foreach ($item->defects as $defect) {
                $rows[] = [
                    $report->rec_date?->format('Y-m-d'),
                    $report->customer,
                    $partNumber,
                    $partName,
                    $item->rec_quantity,
                    $item->can_use,
                    $item->cant_use,
                    $item->price,
                    $totalPrice,
                    $defect->quantity,
                    $defect->name,
                    $defect->notes,
                ];
            }
        }

        return $rows;
    }
}
