<?php

namespace Database\Factories;

use App\Models\MasterInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterInventoryFactory extends Factory
{
    protected $model = MasterInventory::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4,
            'username' => $this->faker->userName,
            'position_image' => null,
            'dept' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Production', 'QA']),
            'type' => $this->faker->randomElement(['Desktop', 'Laptop', 'Server']),
            'purpose' => $this->faker->randomElement(['Development', 'Testing', 'Production']),
            'brand' => $this->faker->randomElement(['Dell', 'HP', 'Lenovo', 'ASUS']),
            'os' => $this->faker->randomElement(['Windows 11', 'Windows 10', 'Ubuntu', 'CentOS']),
            'description' => $this->faker->sentence,
        ];
    }
}
