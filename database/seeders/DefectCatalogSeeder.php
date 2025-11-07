<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use Illuminate\Database\Seeder;

class DefectCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DefectCatalog::updateOrCreate(
            ['code' => 'DENT'],
            ['name' => 'Dent / Deformation', 'default_severity' => 'MEDIUM', 'default_source' => 'SUPPLIER', 'default_quantity' => 1, 'notes' => null, 'active' => true]
        );
        DefectCatalog::updateOrCreate(
            ['code' => 'RUST'],
            ['name' => 'Rust / Corrosion', 'default_severity' => 'HIGH', 'default_source' => 'SUPPLIER', 'default_quantity' => 1, 'notes' => null, 'active' => true]
        );
        DefectCatalog::updateOrCreate(
            ['code' => 'WRONG-SPEC'],
            ['name' => 'Wrong Spec', 'default_severity' => 'HIGH', 'default_source' => 'CUSTOMER', 'default_quantity' => 1, 'notes' => null, 'active' => true]
        );
    }
}
