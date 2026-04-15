<?php

namespace Database\Factories;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeFormDetailFactory extends Factory
{
    protected $model = OvertimeFormDetail::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $startTime = $this->faker->time('H:i:s');
        $endTime = $this->faker->time('H:i:s');

        return [
            'header_id' => OvertimeForm::factory(),
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
