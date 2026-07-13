<?php

namespace App\Exports;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyVerificationExport implements FromCollection, WithHeadings, WithMapping, WithMultipleSheets
{
    use Exportable;

    protected int $month;

    protected int $year;

    protected array $summaryData = [];

    protected array $defectData = [];

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    public function collection()
    {
        $reports = VerificationReport::with('items.defects')
            ->whereMonth('rec_date', $this->month)
            ->whereYear('rec_date', $this->year)
            ->get();

        $this->calculateSummary($reports);
        $this->calculateDefect($reports);

        return $reports;
    }

    protected function calculateSummary($reports): void
    {
        foreach ($reports as $report) {
            foreach ($report->items as $item) {
                foreach ($item->defects as $defect) {
                    $customer = $report->customer;
                    $category = $defect->name ?? '-';

                    $this->summaryData[$customer][$category] ??= 0;
                    $this->summaryData[$customer][$category] += $defect->quantity;
                }
            }
        }
    }

    protected function calculateDefect($reports): void
    {
        foreach ($reports as $report) {
            foreach ($report->items as $item) {
                $partName = $item->part_name;

                $this->defectData[$partName] ??= [
                    'customer'     => $report->customer,
                    'part_name'    => $partName,
                    'rec_quantity' => 0,
                    'defects'      => [],
                ];

                $this->defectData[$partName]['rec_quantity'] += $item->rec_quantity;

                foreach ($item->defects as $defect) {
                    $categoryName = $defect->name ?? '-';

                    $this->defectData[$partName]['defects'][$categoryName] ??= [
                        'category_name' => $categoryName,
                        'quantity'      => 0,
                    ];

                    $this->defectData[$partName]['defects'][$categoryName]['quantity'] += $defect->quantity;
                }
            }
        }
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

    public function sheets(): array
    {
        // ponytail: reuse existing model-agnostic SummarySheet/DefectReportSheet — no new classes needed
        if (empty($this->summaryData) && empty($this->defectData)) {
            $this->collection();
        }

        return [
            new MonthlyVerificationDataSheet($this->month, $this->year),
            new SummarySheet($this->summaryData),
            new DefectReportSheet($this->defectData),
        ];
    }
}
