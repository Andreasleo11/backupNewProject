<?php

namespace Database\Seeders;

use App\Models\PurchaseOrderCategory;
use Illuminate\Database\Seeder;

class PurchaseOrderCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseOrderCategory::truncate();
        PurchaseOrderCategory::create(['name' => 'Raw Material']);
        PurchaseOrderCategory::create(['name' => 'Indirect Material']);
        PurchaseOrderCategory::create(['name' => 'Consumable']);
        PurchaseOrderCategory::create(['name' => 'Jasa']);
        PurchaseOrderCategory::create(['name' => 'Asset']);
    }
}
