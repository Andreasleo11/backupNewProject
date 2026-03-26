<?php

namespace Database\Factories;

use App\Models\MasterStock;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterStockFactory extends Factory
{
    protected $model = MasterStock::class;

    public function definition(): array
    {
        return [
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'stock_type_id' => $this->faker->numberBetween(1, 5),
            'name' => $this->faker->word,
            'description' => $this->faker->optional()->sentence,
        ];
    }
}
