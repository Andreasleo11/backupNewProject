<?php

namespace App\Exports;

use App\Models\SapDelsched;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection, WithHeadings
{
    protected $totalQuantities;
    protected $itemCounts;
    protected $result;

    public function __construct($totalQuantities, $itemCounts, $result)
    {
        $this->totalQuantities = $totalQuantities;
        $this->itemCounts = $itemCounts;
        $this->result = $result;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $filteredData = collect();

        foreach ($this->totalQuantities as $month => $items) {
            foreach ($items as $itemCode => $quantity) {
                $count = $this->itemCounts[$month][$itemCode] ?? 0;
                $averageWithCount = $count > 0 ? round($quantity / $count) : 0;
                $inStock = $this->result[$month][$itemCode]['in_stock'] ?? 0;
                $itemName = $this->result[$month][$itemCode]['item_name'] ?? '';
                $warehouse = $this->result[$month][$itemCode]['warehouse'] ?? '';
                $days = $averageWithCount > 0 ? floor($inStock / $averageWithCount) : 0;

                // Filter for daysFilter "small" (0-1 days)
                if ($days >= 0 && $days <= 1) {
                    $filteredData->push([
                        'Month' => $month,
                        'Item Code' => $itemCode,
                        'Item Name' => $itemName,
                        'Warehouse' => $warehouse,
                        'Total Delivery' => $quantity,
                        'Delivery Freq' => $count,
                        'Avg Per Delivery' => $averageWithCount,
                        'In Stock' => $inStock,
                        'Stock Days' => $days,
                        'Min Stock (2 Days)' => $averageWithCount * 2,
                        'Max Stock (5 Days)' => $averageWithCount * 5,
                    ]);
                }
            }
        }

        return $filteredData;
    }

    public function headings(): array
    {
        return [
            'Month', 'Item Code', 'Item Name', 'Warehouse',
            'Total Delivery', 'Delivery Freq', 'Avg Per Delivery',
            'In Stock', 'Stock Days', 'Min Stock (2 Days)', 'Max Stock (5 Days)'
        ];
    }
}
