<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseRequestsExport implements FromCollection, WithTitle, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $purchaseRequestIds;

    public function __construct($purchaseRequestIds)
    {
        $this->purchaseRequestIds = $purchaseRequestIds;
    }

    public function collection()
    {
        // Fetch PurchaseRequests by IDs
        return PurchaseRequest::whereIn("id", $this->purchaseRequestIds)->get();
    }

    public function headings(): array
    {
        // Get column names from the reports table
        $columnNames = Schema::getColumnListing("purchase_requests");

        // Return the column names as headers
        return $columnNames;
    }

    public function title(): string
    {
        return "Purchase Requests"; // Custom title for the sheet
    }
}
