<?php

namespace App\Exports;

use App\Models\DetailPurchaseRequest;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailPurchaseRequestsExport implements FromCollection, WithTitle, WithHeadings
{
    protected $purchaseRequestIds;

    public function __construct($purchaseRequestIds)
    {
        $this->purchaseRequestIds = $purchaseRequestIds;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Fetch DetailPurchaseRequests where purchase_request_id is in the selected PurchaseRequests
        return DetailPurchaseRequest::whereIn(
            "purchase_request_id",
            $this->purchaseRequestIds,
        )->get();
    }

    public function headings(): array
    {
        // Get column names from the reports table
        $columnNames = Schema::getColumnListing("detail_purchase_requests");

        // Return the column names as headers
        return $columnNames;
    }

    public function title(): string
    {
        return "Detail Purchase Requests"; // Custom title for the sheet
    }
}
