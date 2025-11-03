<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePartCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_part_catalog')->insert([
            ['code' => 'OIL', 'name' => 'Engine Oil', 'default_interval_km' => 10000, 'default_interval_days' => 180],
            ['code' => 'OILF', 'name' => 'Oil Filter', 'default_interval_km' => 10000],
            ['code' => 'AIRF', 'name' => 'Air Filter', 'default_interval_km' => 20000],
            ['code' => 'BRKP', 'name' => 'Brake Pads'],
            ['code' => 'TIRE', 'name' => 'Tire Rotation', 'default_interval_km' => 8000],
            ['code' => 'INSP', 'name' => 'General Inspection', 'default_interval_days' => 90],
        ]);
    }
}
