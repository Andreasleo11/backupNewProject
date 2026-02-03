<?php

namespace Database\Factories;

use App\Models\Specification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Specification>
 *
 * @deprecated This factory is for backwards compatibility only.
 *
 * The Specification model and specification_id column will be replaced
 * with Spatie Laravel Permission (roles & permissions).
 *
 * Migration Plan:
 * - specification_id will be removed from users table
 * - Role checks will use Spatie's hasRole() / can()
 * - Tests will use Spatie's role assignment
 *
 * Timeline: After PR test fixes are complete
 */
class SpecificationFactory extends Factory
{
    protected $model = Specification::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'VERIFICATOR',
                'DIRECTOR',
                'HEAD',
                'MANAGER',
                'GM',
            ]),
        ];
    }

    /**
     * Factory state for verificator role
     */
    public function verificator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'VERIFICATOR',
        ]);
    }

    /**
     * Factory state for director role
     */
    public function director(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'DIRECTOR',
        ]);
    }

    /**
     * Factory state for head role
     */
    public function head(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'HEAD',
        ]);
    }

    /**
     * Factory state for GM role
     */
    public function gm(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'GM',
        ]);
    }
}
