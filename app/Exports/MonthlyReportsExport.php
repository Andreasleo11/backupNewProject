<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyReportsExport implements FromCollection, WithHeadings, WithMapping, WithMultipleSheets
{

    protected $month;
    protected $year;

    protected $summaryData = [];

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $reports = Report::with('details', 'details.defects', 'details.defects.category')
        ->whereMonth('verify_date', $this->month)
        ->whereYear('verify_date', $this->year)
        ->get();

        $this->calculateSummary($reports);

        return $reports;
    }


    protected function calculateSummary($reports)
    {
        foreach ($reports as $report) {
            foreach ($report->details as $detail) {
                foreach ($detail->defects as $defect) {
                    $customer = $report->customer;
                    $category = $defect->category->name ?? '-';

                    if (!isset($this->summaryData[$customer][$category])) {
                        $this->summaryData[$customer][$category] = 0;
                    }

                    $this->summaryData[$customer][$category] += $defect->quantity;
                }
            }
        }
    }


     public function headings(): array
    {
        return [
            'Verify Date',
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
                    $report->verify_date,
                    $report->customer,
                    $partNumber,
                    $partName,
                    $detail->rec_quantity,
                    $detail->can_use,
                    $detail->cant_use,
                    $detail->price,
                    $totalPrice,
                    $defect->quantity,
                    $defect->category->name,
                    $defect->remarks
                ];
            }
        }
        return $rows;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ReportDataSheet($this->month, $this->year, $this->collection());
        $sheets[] = new SummarySheet($this->summaryData);

        return $sheets;
    }
}
