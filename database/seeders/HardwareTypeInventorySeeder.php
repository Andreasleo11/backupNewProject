<?php

namespace Database\Seeders;

use App\Models\HardwareTypeInventory;
use Illuminate\Database\Seeder;

class HardwareTypeInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hardwareTypes = [
            'MONITOR',
            'PRINTER 1',
            'PRINTER 2',
            'PROCESSOR',
            'MAINBOARD',
            'MEMORY',
            'DRIVE 1',
            'DRIVE 2',
            'VGA',
        ];

        foreach ($hardwareTypes as $type) {
            HardwareTypeInventory::create(['name' => $type]);
        }
    }
}
