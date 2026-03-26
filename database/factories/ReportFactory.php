<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rec_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
            'verify_date' => now()->subDays(rand(0, 15))->format('Y-m-d'),
            'customer' => $this->faker->company(),
            'invoice_no' => 'INV-' . $this->faker->unique()->numerify('######'),
            'autograph_1' => null,
            'autograph_2' => null,
            'autograph_3' => null,
            'autograph_user_1' => null,
            'autograph_user_2' => null,
            'autograph_user_3' => null,
            'created_by' => null,
            'attachment' => null,
            'is_approve' => 2, // Default: waiting for approval
            'description' => $this->faker->optional()->sentence(),
            'first_reject' => null,
            'rejected_at' => null,
            'is_locked' => false,
            'has_been_emailed' => false,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the report is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approve' => 1,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the report is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approve' => 0,
            'rejected_at' => now(),
            'first_reject' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the report has all autographs.
     */
    public function withAutographs(): static
    {
        return $this->state(fn (array $attributes) => [
            'autograph_1' => 'path/to/signature1.png',
            'autograph_2' => 'path/to/signature2.png',
            'autograph_3' => 'path/to/signature3.png',
            'autograph_user_1' => 1,
            'autograph_user_2' => 2,
            'autograph_user_3' => 3,
        ]);
    }

    /**
     * Indicate that the report is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_locked' => true,
        ]);
    }
}
