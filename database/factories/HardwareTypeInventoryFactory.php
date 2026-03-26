<?php

namespace Database\Factories;

use App\Models\HardwareTypeInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class HardwareTypeInventoryFactory extends Factory
{
    protected $model = HardwareTypeInventory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['CPU', 'GPU', 'RAM', 'SSD', 'Motherboard', 'Power Supply']),
        ];
    }
}
