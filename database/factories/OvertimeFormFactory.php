<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Domain\Overtime\Models\OvertimeForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeFormFactory extends Factory
{
    protected $model = OvertimeForm::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dept_id' => Department::factory(),
            'branch' => $this->faker->randomElement(['Karawang', 'Jakarta']),
            'status' => 'pending',
            'is_design' => false,
            'is_export' => false,
            'description' => null,
            'is_planned' => true,
            'is_after_hour' => true,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
