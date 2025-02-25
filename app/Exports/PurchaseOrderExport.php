<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

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
            'data' => $this->filteredData
        ]);
    }
}
