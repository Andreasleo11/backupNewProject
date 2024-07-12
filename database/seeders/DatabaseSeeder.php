<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use App\Models\PurchaseRequest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SpecificationSeeder::class,
            PermissionSeeder::class,
            StockTypeSeeder::class,
        ]);

        $this->call([
            FixPurchaseRequestSeeder::class,
        ]);
    }
}
