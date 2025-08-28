<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\SoftwareTypeInventory;

class SoftwareTypeInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $softwareTypes = ["OS", "Microsoft Office"];

        foreach ($softwareTypes as $type) {
            SoftwareTypeInventory::create(["name" => $type]);
        }
    }
}
