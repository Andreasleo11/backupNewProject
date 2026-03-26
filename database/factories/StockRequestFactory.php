<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\MasterStock;
use App\Models\StockRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockRequestFactory extends Factory
{
    protected $model = StockRequest::class;

    public function definition(): array
    {
        return [
            'stock_id' => MasterStock::factory(),
            'dept_id' => Department::factory(),
            'request_quantity' => $this->faker->numberBetween(1, 50),
            'quantity_available' => $this->faker->numberBetween(0, 50),
            'month' => $this->faker->date(),
            'remark' => $this->faker->optional()->sentence,
        ];
    }
}
