<?php

namespace Database\Factories;

use App\Models\DetailHardware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailHardwareFactory extends Factory
{
    protected $model = DetailHardware::class;

    public function definition(): array
    {
        return [
            'master_inventory_id' => MasterInventory::factory(),
            'hardware_id' => HardwareTypeInventory::factory(),
            'brand' => $this->faker->randomElement(['Intel', 'AMD', 'NVIDIA', 'Samsung', 'Kingston']),
            'hardware_name' => $this->faker->randomElement(['i7-12700K', 'Ryzen 9 5900X', 'RTX 3080', '32GB RAM']),
            'remark' => $this->faker->optional()->sentence,
        ];
    }
}
