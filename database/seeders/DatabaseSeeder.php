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
        /*
         |--------------------------------------------------------------
         | 1. Base System Setup (Identity & Permissions)
         |--------------------------------------------------------------
         */
        $this->call([
            RolesAndPermissionsSeeder::class, // Roles and basic permissions
            PrRoleMappingSeeder::class,       // Map PR-specific permissions to roles
            AdminUserSeeder::class,           // Create super-admin role and admin user
        ]);

        /*
         |--------------------------------------------------------------
         | 2. Master Data
         |--------------------------------------------------------------
         */
        $this->call([
            // SpecificationSeeder::class,
            DepartmentSeeder::class,
            StockTypeSeeder::class,
            PurchaseOrderCategorySeeder::class,
        ]);

        /*
         |--------------------------------------------------------------
         | 3. Business Rules (Approval Workflows)
         |--------------------------------------------------------------
         */
        $this->call([
            PrApprovalRulesSeeder::class,      // Purchase Request approval rules
        ]);

        /**
         * Maintenance and one-off fixes.
         * Uncomment if needed for specific data correction tasks.
         */
        // $this->call([
        //     FixPurchaseRequestSeeder::class,
        //     FixMonthlyBudgetReportSeeder::class,
        //     FixMonthlyBudgetSummaryReportSeeder::class,
        //     FixSPKRemarks::class,
        //     FixSPK::class,
        // ]);
    }
}
