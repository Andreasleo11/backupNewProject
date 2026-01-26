<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\MasterStock;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransactionFactory extends Factory
{
    protected $model = StockTransaction::class;

    public function definition(): array
    {
        return [
            'stock_id' => MasterStock::factory(),
            'dept_id' => null,
            'unique_code' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'in_time' => $this->faker->dateTimeBetween('-1 month'),
            'out_time' => null,
            'is_out' => false,
            'receiver' => null,
            'remark' => $this->faker->optional()->sentence,
        ];
    }
}
