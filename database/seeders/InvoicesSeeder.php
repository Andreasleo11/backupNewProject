<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchaseOrders = PurchaseOrder::where(function ($query){
            $query->whereNotNull('invoice_number')
                ->orWhereNotNull('invoice_date');
        });
    }
}
