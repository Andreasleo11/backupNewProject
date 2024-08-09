<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupMaintenanceInventoryReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('group_maintenance_inventory_reports')->insert([
            ['name' => 'Cleaning Group'],
            ['name' => 'Check Monitor Group'],
            ['name' => 'Check PC/CPU Group'],
            ['name' => 'Check Koneksi Internet Group'],
            ['name' => 'Check Printer/Scanner Group'],
            ['name' => 'Check Software/System Group'],
        ]);
    }
}