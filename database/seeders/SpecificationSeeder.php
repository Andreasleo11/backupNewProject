<?php

namespace Database\Seeders;

use App\Models\Specification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Specification::updateOrCreate(['name' => '-']);
        Specification::updateOrCreate(['name' => 'INSPECTOR']);
        Specification::updateOrCreate(['name' => 'LEADER']);
        Specification::updateOrCreate(['name' => 'STAFF']);
        Specification::updateOrCreate(['name' => 'DIRECTOR']);
        Specification::updateOrCreate(['name' => 'ADMIN']);
        Specification::updateOrCreate(['name' => 'HEAD']);
    }
}
