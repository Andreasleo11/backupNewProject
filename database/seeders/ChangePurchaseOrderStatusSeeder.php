<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChangePurchaseOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldStatusMap = [
            1 => 'waiting',
            2 => 'approved',
            3 => 'rejected',
            4 => 'canceled',
        ];

        \App\Models\PurchaseOrder::query()->chunkById(1000, function ($purchaseOrders) use ($oldStatusMap) {
            foreach ($purchaseOrders as $po) {
                if (isset($oldStatusMap[$po->status])) {
                    $po->status = $oldStatusMap[$po->status];
                    $po->save();
                }
            }
        });

        \Illuminate\Support\Facades\Log::info('Purchase order status has been changed');
    }
}
