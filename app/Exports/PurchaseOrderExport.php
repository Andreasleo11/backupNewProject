<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PurchaseOrderExport implements FromView
{
    protected $filteredData;

    public function __construct($filteredData)
    {
        $this->filteredData = $filteredData;
    }

    public function view(): View
    {
        return view('exports.purchase_orders', [
            'data' => $this->filteredData,
        ]);
    }
}
