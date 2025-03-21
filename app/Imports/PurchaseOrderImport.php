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
                        $isNewPO = true;
                    } else {
                        // **Retrieve existing PO**
                        $purchaseOrder = PurchaseOrder::where('po_number', $documentNumber)->first();

                        if (!$purchaseOrder) {
                            $firstRow = $items->first();
                            // **Create New PO and Store in Memory**
                            $purchaseOrder = PurchaseOrder::create([
                                'po_number' => $documentNumber,
                                'status' => 0,
                                'filename' => null,
                                'vendor_name' => $firstRow['vendor_name'],
                                'vendor_code' => $firstRow['vendor_code'],
                                'posting_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['created_at']))->format('Y-m-d'),
                                'delivery_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['delivery_date']))->format('Y-m-d'),
                                'sales_employee_name' => $firstRow['sales_employee_name'],
                                'total_before_tax' => floatval(str_replace(',', '', $firstRow['document_total'])),
                                'total_tax' => floatval(str_replace(['.', ','], ['', '.'], $firstRow['total_tax'])),
                                'tanggal_pembayaran' => null,
                                'bill_to' => $firstRow['bill_to'],
                                'ship_to' => $firstRow['ship_to'],
                                'remark' => $firstRow['remarks'],
                                'payment_terms' => $firstRow['payment_terms_group_name'],
                                'contact_person_name' => $firstRow['contact_person_name'],
                            ]);

                            // Store the newly created PO in memory
                            $this->newlyCreatedPOs[$documentNumber] = $purchaseOrder;
                            $isNewPO = true;
                        } else {
                            // **PO already existed before the import**
                            // **Update existing PO**

                            $firstRow = $items->first();
 
                            $purchaseOrder->update([
                                'vendor_name' => $firstRow['vendor_name'],
                                'vendor_code' => $firstRow['vendor_code'],
                                'posting_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['created_at']))->format('Y-m-d'),
                                'delivery_date' => Carbon::createFromFormat('d.m.y', trim($firstRow['delivery_date']))->format('Y-m-d'),
                                'sales_employee_name' => $firstRow['sales_employee_name'],
                                'total_before_tax' => floatval(str_replace(',', '', $firstRow['document_total'])),
                                'total_tax' => floatval(str_replace(['.', ','], ['', '.'], $firstRow['total_tax'])),
                                'bill_to' => $firstRow['bill_to'],
                                'ship_to' => $firstRow['ship_to'],
                                'remark' => $firstRow['remarks'],
                                'payment_terms' => $firstRow['payment_terms_group_name'],
                                'contact_person_name' => $firstRow['contact_person_name'],
                            ]);

                            Log::info("✅ Successfully updated PO: " . $purchaseOrder->po_number);
                            $isNewPO = false;
                        }
                    }

                    // **Process Items**
                    if ($isNewPO) {
                        // **If PO is new, directly insert all items without checking existing ones**
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
                    } else {
                        // **If PO already existed, check for updates and deletions**
                        $existingItems = $purchaseOrder->items()->get()->keyBy('code');
                        $newItemCodes = [];

                        foreach ($items as $row) {
                            $itemCode = $row['item_code'];
                            $newItemCodes[] = $itemCode;

                            if ($existingItems->has($itemCode)) {
                                // **Update Existing Item**
                                $existingItem = $existingItems[$itemCode];
                                $existingItem->update([
                                    'name' => $row['item_name'],
                                    'category_name' => $row['group_name'],
                                    'category_code' => $row['item_group'],
                                    'quantity' => intval(str_replace(['.', ','], ['', ''], $row['quantity'])) / 100000,
                                    'uom' => $row['purchasing_uom'],
                                    'currency' => $row['document_currency'],
                                    'price' => floatval(str_replace(',', '', $row['price'])),
                                    'dept_code' => $row['department'] ?? null,
                                ]);
                            } else {
                                // **Insert New Item**
                                PurchaseOrderItem::create([
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'purchase_order_number' => $purchaseOrder->po_number,
                                    'code' => $itemCode,
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

                        // **Delete Missing Items (Items not found in the Excel)**
                        $existingItems->each(function ($existingItem) use ($newItemCodes) {
                            if (!in_array($existingItem->code, $newItemCodes)) {
                                $existingItem->delete();
                            }
                        });
                    }
                }
            });

        } catch (\Exception $e) {
             // Notify user if import fails
             Log::error("❌ Purchase Order Import Failed. Exception: " . $e->getMessage() );
             $this->notifyUser('error', 'Purchase Order Import Failed. ' . $e->getMessage());
        }
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

