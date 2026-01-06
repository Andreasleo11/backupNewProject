<?php

namespace Database\Factories;

use App\Enums\ToDepartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id_create' => User::factory(),
            'date_pr' => $this->faker->date(),
            'date_required' => $this->faker->date(),
            'remark' => $this->faker->sentence(),
            'to_department' => $this->faker->randomElement(ToDepartment::cases()),
            'status' => 1, // Default pending
            'pr_no' => $this->faker->unique()->numerify('PR-####'),
            'doc_num' => $this->faker->unique()->numerify('DOC-###'),
            'supplier' => $this->faker->company(),
            'pic' => $this->faker->name(),
            'type' => 'office',
            'from_department' => 'Computer', // Default, override as needed
            'branch' => 'JAKARTA',
            'is_import' => false,
            'is_cancel' => false,
        ];
    }
}
