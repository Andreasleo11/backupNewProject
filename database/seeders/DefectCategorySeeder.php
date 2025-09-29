<?php

namespace Database\Seeders;

use App\Models\DefectCategory;
use Illuminate\Database\Seeder;

class DefectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DefectCategory::create(['name' => 'Nubmark']);
        DefectCategory::create(['name' => 'Burn mark']);
        DefectCategory::create(['name' => 'Salah warna']);
    }
}
