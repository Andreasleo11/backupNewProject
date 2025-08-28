<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DefectCategory;

class DefectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DefectCategory::create(["name" => "Nubmark"]);
        DefectCategory::create(["name" => "Burn mark"]);
        DefectCategory::create(["name" => "Salah warna"]);
    }
}
