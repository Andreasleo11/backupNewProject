<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * The name of the factory's corresponding model primary key.
     *
     * @var string
     */
    protected $primaryKey = 'nik';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $nikCounter = 1000;

        return [
            'nik' => 'EMP' . str_pad($nikCounter++, 5, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'date_birth' => fake()->dateTimeBetween('-50 years', '-20 years'),
            'gender' => fake()->randomElement(['L', 'P']),
            'dept_code' => fake()->randomElement(['001', '002', '003', '004', '005']),
            'start_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'employment_scheme' => fake()->randomElement(['YAYASAN', 'YAYASAN KARAWANG', 'KONTRAK', 'MAGANG']),
            'grade_level' => fake()->randomElement(['Staff', 'Supervisor', 'Manager']),
            'jatah_cuti_tahun' => fake()->numberBetween(12, 18),
            'organization_structure' => fake()->randomElement(['Production', 'Quality', 'Maintenance', 'HR']),
            'end_date' => null,
            'employment_type' => fake()->randomElement(['Active', 'Inactive']),
            'branch' => fake()->randomElement(['Jakarta', 'Karawang']),
            'grade_code' => fake()->randomElement(['A', 'B', 'C']),
        ];
    }

    /**
     * Indicate that the employee is a Yayasan (permanent) employee.
     */
    public function yayasan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => fake()->randomElement(['YAYASAN', 'YAYASAN KARAWANG']),
        ]);
    }

    /**
     * Indicate that the employee is a contract employee.
     */
    public function kontrak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'KONTRAK',
        ]);
    }

    /**
     * Indicate that the employee is an intern (Magang).
     */
    public function magang(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'MAGANG',
        ]);
    }

    /**
     * Indicate that the employee is in a specific department.
     */
    public function inDepartment(string $deptNo): static
    {
        return $this->state(fn (array $attributes) => [
            'dept_code' => $deptNo,
        ]);
    }

    /**
     * Indicate that the employee is inactive/terminated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_type' => 'Inactive',
            'end_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
}
