<?php

namespace Database\Factories;

use App\Models\DetailFormOvertime;
use App\Models\Employee;
use App\Models\HeaderFormOvertime;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailFormOvertimeFactory extends Factory
{
    protected $model = DetailFormOvertime::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $startTime = $this->faker->time('H:i:s');
        $endTime = $this->faker->time('H:i:s');

        return [
            'header_id' => HeaderFormOvertime::factory(),
            'NIK' => Employee::factory(),
            'name' => $this->faker->name(),
            'overtime_date' => $startDate->format('Y-m-d'),
            'job_desc' => $this->faker->sentence(),
            'start_date' => $startDate->format('Y-m-d'),
            'start_time' => $startTime,
            'end_date' => $startDate->format('Y-m-d'),
            'end_time' => $endTime,
            'break' => $this->faker->randomElement([0, 30, 60]),
            'remarks' => $this->faker->optional()->sentence(),
            'status' => null,
            'reason' => null,
            'is_processed' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Approved',
            'is_processed' => 1,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
            'reason' => 'Rejected by system',
        ]);
    }
}
