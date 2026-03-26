<?php

namespace Database\Factories;

use App\Models\DetailSoftware;
use App\Models\MasterInventory;
use App\Models\SoftwareTypeInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailSoftwareFactory extends Factory
{
    protected $model = DetailSoftware::class;

    public function definition(): array
    {
        return [
            'master_inventory_id' => MasterInventory::factory(),
            'software_id' => SoftwareTypeInventory::factory(),
            'software_brand' => $this->faker->company,
            'license' => $this->faker->uuid,
            'software_name' => $this->faker->randomElement(['Microsoft Office', 'Adobe Photoshop', 'AutoCAD', 'VS Code']),
            'remark' => $this->faker->optional()->sentence,
        ];
    }
}
