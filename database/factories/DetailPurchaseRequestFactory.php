<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailPurchaseRequest>
 */
class DetailPurchaseRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_name' => $this->faker->word(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'purpose' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1000, 1000000),
            'uom' => 'PCS',
            'currency' => 'IDR',
            'is_approve' => true,
        ];
    }
}
