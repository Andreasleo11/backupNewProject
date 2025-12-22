<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('detail_purchase_requests')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                $insert = [];

                foreach ($rows as $row) {
                    $insert[] = [
                        // if you want to keep same ID, you *can* set 'id' => $row->id
                        'id' => $row->id,
                        'purchase_request_id' => $row->purchase_request_id, // adjust name
                        'master_item_id' => $row->master_item_id ?? null, // if exists
                        'item_name' => $row->item_name,
                        'quantity' => $row->quantity,
                        'uom' => $row->uom,
                        'currency' => $row->currency ?? 'IDR',
                        'unit_price' => $row->price, // or unit_price column if you have it
                        'purpose' => $row->purpose,
                        'received_quantity' => $row->received_quantity ?? null,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];
                }

                if (! empty($insert)) {
                    DB::table('purchase_request_items')->insert($insert);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // we do NOT delete original data, just drop the new table in previous migration if needed
    }
};
