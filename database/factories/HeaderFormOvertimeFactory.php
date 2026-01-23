<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\HeaderFormOvertime;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeaderFormOvertimeFactory extends Factory
{
    protected $model = HeaderFormOvertime::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dept_id' => Department::factory(),
            'branch' => $this->faker->randomElement(['Karawang', 'Jakarta']),
            'status' => 'pending',
            'is_design' => false,
            'is_export' => false,
            'is_push' => 0,
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

    public function pushed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_push' => 1,
        ]);
    }
}
