<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvaluationData>
 */
class EvaluationDataFactory extends Factory
{
    protected $model = EvaluationData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'NIK' => Employee::factory(),
            'dept' => fake()->randomElement(['001', '002', '003', '004', '005']),
            'Month' => fake()->dateTimeBetween('-6 months', 'now'),
            'Alpha' => fake()->numberBetween(0, 5),
            'Telat' => fake()->numberBetween(0, 10),
            'Izin' => fake()->numberBetween(0, 3),
            'Sakit' => fake()->numberBetween(0, 3),

            // Old scoring system fields
            'kerajinan_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'kerapian_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'prestasi' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'loyalitas' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'perilaku_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),

            // New scoring system fields (Yayasan/Magang)
            'kemampuan_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'kecerdasan_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'qualitas_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'disiplin_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'kepatuhan_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'lembur' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'efektifitas_kerja' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'relawan' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'integritas' => fake()->randomElement(['A', 'B', 'C', 'D']),

            'total' => fake()->numberBetween(50, 100),
            'pengawas' => null,
            'depthead' => null,
            'generalmanager' => null,
            'remark' => null,
            'is_lock' => false,
        ];
    }

    /**
     * Indicate that the evaluation data is for a specific employee.
     */
    public function forEmployee(Employee|string $employee): static
    {
        $nik = $employee instanceof Employee ? $employee->NIK : $employee;

        return $this->state(fn (array $attributes) => [
            'NIK' => $nik,
        ]);
    }

    /**
     * Indicate that the evaluation data is for a specific month/year.
     */
    public function forMonth(int $month, int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'Month' => sprintf('%04d-%02d-01', $year, $month),
        ]);
    }

    /**
     * Indicate that the evaluation data is approved by department head.
     */
    public function approvedByDeptHead(string $name = 'Department Head'): static
    {
        return $this->state(fn (array $attributes) => [
            'depthead' => $name,
        ]);
    }

    /**
     * Indicate that the evaluation data is approved by general manager.
     */
    public function approvedByGM(string $name = 'General Manager'): static
    {
        return $this->state(fn (array $attributes) => [
            'generalmanager' => $name,
        ]);
    }

    /**
     * Indicate that the evaluation data is fully approved.
     */
    public function fullyApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'depthead' => 'Department Head',
            'generalmanager' => 'General Manager',
        ]);
    }

    /**
     * Indicate that the evaluation data is rejected by department head.
     */
    public function rejectedByDeptHead(string $remark = 'Needs improvement'): static
    {
        return $this->state(fn (array $attributes) => [
            'depthead' => 'rejected',
            'remark' => $remark,
        ]);
    }

    /**
     * Indicate that the evaluation data is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_lock' => true,
        ]);
    }

    /**
     * Indicate that the evaluation data has a high score (Grade A).
     */
    public function gradeA(): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => fake()->numberBetween(80, 100),
        ]);
    }

    /**
     * Indicate that the evaluation data has a low score (Grade B).
     */
    public function gradeB(): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => fake()->numberBetween(50, 79),
        ]);
    }

    /**
     * Indicate that the evaluation data is in a specific department.
     */
    public function inDepartment(string $deptNo): static
    {
        return $this->state(fn (array $attributes) => [
            'dept' => $deptNo,
        ]);
    }
}
