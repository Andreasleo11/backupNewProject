<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseRequestWithDetailsExport implements WithMultipleSheets
{
    protected $purchaseRequestIds;

    public function __construct($purchaseRequestIds)
    {
        $this->purchaseRequestIds = $purchaseRequestIds;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new PurchaseRequestsExport($this->purchaseRequestIds), // First sheet for Purchase Request
            new DetailPurchaseRequestsExport($this->purchaseRequestIds), // Second sheet for Detail Purchase Request
        ];
    }
}
