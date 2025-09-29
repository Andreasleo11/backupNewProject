<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            PurchaseOrderCategorySeeder::class,
        ]);

        /**
         * If this class has already been used before, comment it out.
         * These classes are intentionally designed to run only once.
         */
        $this->call([
            // FixPurchaseRequestSeeder::class,
            // FixMonthlyBudgetReportSeeder::class,
            // FixMonthlyBudgetSummaryReportSeeder::class,
            // FixSPKRemarks::class,
            // FixSPK::class,
        ]);
    }
}
