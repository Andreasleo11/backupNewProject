<?php

namespace App\Imports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;
use App\Notifications\PurchaseOrderImportStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PurchaseOrderImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    protected $newlyCreatedPOs = []; // Temporary storage for newly created POs within this import session
    protected $userId; // Store user ID

    public function __construct($userId)
    {
        $this->userId = $userId;
        PurchaseOrderItem::truncate();
    }

    public function collection(Collection $rows)
    {
        try {
            DB::transaction(function () use ($rows) {
                // **Group Rows by Document Number**
                $groupedData = $rows->groupBy('document_number');

                foreach ($groupedData as $documentNumber => $items) {
                    // **Check if PO Exists (in DB or newly created in this session)**
                    if (isset($this->newlyCreatedPOs[$documentNumber])) {
                        // **PO was just created in a previous chunk → No need to check existing items**
                        $purchaseOrder = $this->newlyCreatedPOs[$documentNumber];
                    } else {
                        // **Retrieve existing PO**
                        $purchaseOrder = PurchaseOrder::where('po_number', $documentNumber)->first();

                        if (!$purchaseOrder) {
                            $firstRow = $items->first();
                            // **Create New PO and Store in Memory**
                            $purchaseOrder = PurchaseOrder::create([
                                'status' => isset($firstRow['cancelled']) && $firstRow['cancelled'] === 'Y' ? "cancelled" : "open",
                                'po_number' => $documentNumber,
                                'filename' => null,
                                'vendor_name' => $firstRow['vendor_name'],
                                'vendor_code' => $firstRow['vendor_code'],
                                'posting_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['created_at']))->format('Y-m-d'),
                                'delivery_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['delivery_date']))->format('Y-m-d'),
                                'sales_employee_name' => $firstRow['sales_employee_name'],
                                'total' => floatval(str_replace(',', '', $firstRow['document_total'])),
                                'total_tax' => floatval(str_replace(',', '', $firstRow['total_tax'])),
                                'tanggal_pembayaran' => $this->processTanggalPembayaran($firstRow['created_at'], $firstRow['payment_terms_group_name']),
                                'bill_to' => $firstRow['bill_to'],
                                'ship_to' => $firstRow['ship_to'],
                                'remarks' => $firstRow['remarks'],
                                'payment_terms' => $firstRow['payment_terms_group_name'],
                                'contact_person_name' => $firstRow['contact_person_name'],
                                'currency' => $firstRow['document_currency'],
                            ]);

                            // Store the newly created PO in memory
                            $this->newlyCreatedPOs[$documentNumber] = $purchaseOrder;
                        } else {
                            // **PO already existed before the import**
                            // **Update existing PO**

                            $firstRow = $items->first();

                            $updateData = [
                                'vendor_name' => $firstRow['vendor_name'],
                                'vendor_code' => $firstRow['vendor_code'],
                                'posting_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['created_at']))->format('Y-m-d'),
                                'delivery_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['delivery_date']))->format('Y-m-d'),
                                'sales_employee_name' => $firstRow['sales_employee_name'],
                                'total' => floatval(str_replace(',', '', $firstRow['document_total'])),
                                'total_tax' => floatval(str_replace(',', '', $firstRow['total_tax'])),
                                'bill_to' => $firstRow['bill_to'],
                                'ship_to' => $firstRow['ship_to'],
                                'remarks' => $firstRow['remarks'],
                                'tanggal_pembayaran' => $this->processTanggalPembayaran($firstRow['created_at'], $firstRow['payment_terms_group_name']),
                                'payment_terms' => $firstRow['payment_terms_group_name'],
                                'contact_person_name' => $firstRow['contact_person_name'],
                                'currency' => $firstRow['document_currency'],
                            ];

                            // Now status logic works safely
                            if ($firstRow['cancelled'] === 'Y') {
                                $updateData['status'] = 'cancelled';
                            } elseif($firstRow['document_status'] === 'C') {
                                $updateData['status'] = 'closed';
                            }

                            

                            $purchaseOrder->update($updateData);
                        }
                    }

                    foreach ($items as $row) {
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'purchase_order_number' => $purchaseOrder->po_number,
                            'code' => $row['item_code'],
                            'name' => $row['item_name'],
                            'category_name' => $row['group_name'],
                            'category_code' => $row['item_group'],
                            'quantity' => intval(str_replace(['.', ','], ['', ''], $row['quantity'])) / 100000,
                            'uom' => $row['purchasing_uom'],
                            'currency' => $row['document_currency'],
                            'price' => floatval(str_replace(',', '', $row['price'])),
                            'dept_code' => $row['department'] ?? null,
                        ]);
                    }
                }
            });

        } catch (\Exception $e) {
             // Notify user if import fails
             Log::error("❌ Purchase Order Import Failed. Exception: " . $e->getMessage() );
             $this->notifyUser('error', 'Purchase Order Import Failed. ' . $e->getMessage());
        }
    }

    private function processTanggalPembayaran($postingDate, $paymentTermsGroupName)
    {
        // Extract the number of days from the string (e.g., "70 days" → 70)
        preg_match('/\d+/', $paymentTermsGroupName, $matches);
        $totalDays = isset($matches[0]) ? (int) $matches[0] : 0;

        // Calculate months and remaining days
        $monthsToAdd = intdiv($totalDays, 30);
        $daysToAdd = $totalDays % 30;

        // Create the date and add the duration
        $paymentDate = Carbon::createFromFormat('d.m.y', $postingDate)
            ->addMonths($monthsToAdd)
            ->addDays($daysToAdd);

        return $paymentDate->format('Y-m-d');
    }

    private function notifyUser($status, $message)
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                $user->notify(new PurchaseOrderImportStatus($status, $message));
            }
        }
    }

    /**
     * Chunk size for processing large files.
     */
    public function chunkSize(): int
    {
        return 1000; // Process 1000 rows at a time
    }

    // ✅ Use Laravel's event system to trigger a notification
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $this->notifyUser('success', 'Purchase Order Import Completed Successfully.');
            },
        ];
    }
}

