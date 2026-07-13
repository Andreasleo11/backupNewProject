<?php

namespace App\Exports;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// ponytail: single-sheet, flat rows per item — no multi-sheet unless requested
class VerificationReportsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return VerificationReport::with('items');
    }

    public function headings(): array
    {
        return [
            'Doc Number',
            'Rec Date',
            'Verify Date',
            'Customer',
            'Invoice #',
            'Status',
            'Part Name',
            'Rec Qty',
            'Verify Qty',
            'Can Use',
            "Can't Use",
            'Price',
            'Currency',
            'DO Number',
            'Line Total',
        ];
    }

    public function map($report): array
    {
        $rows = [];

        foreach ($report->items as $item) {
            $rows[] = [
                $report->document_number,
                $report->rec_date?->format('Y-m-d'),
                $report->verify_date?->format('Y-m-d'),
                $report->customer,
                $report->invoice_number,
                $report->status,
                $item->part_name,
                $item->rec_quantity,
                $item->verify_quantity,
                $item->can_use,
                $item->cant_use,
                $item->price,
                $item->currency,
                $item->do_number,
                (float) $item->verify_quantity * (float) $item->price,
            ];
        }

        return $rows;
    }
}
