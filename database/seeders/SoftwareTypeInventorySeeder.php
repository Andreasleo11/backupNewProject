<?php

namespace Database\Seeders;

use App\Models\SoftwareTypeInventory;
use Illuminate\Database\Seeder;

class SoftwareTypeInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $softwareTypes = ['OS', 'Microsoft Office'];

        foreach ($softwareTypes as $type) {
            SoftwareTypeInventory::create(['name' => $type]);
        }
    }
}
