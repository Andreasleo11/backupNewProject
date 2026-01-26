<?php

namespace Database\Factories;

use App\Models\SoftwareTypeInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoftwareTypeInventoryFactory extends Factory
{
    protected $model = SoftwareTypeInventory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Operating System', 'Office Suite', 'Design Software', 'Development Tool']),
        ];
    }
}
