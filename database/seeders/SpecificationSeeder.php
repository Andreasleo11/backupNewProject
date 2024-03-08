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
        Specification::create(['name' => '-']);
        Specification::create(['name' => 'INSPECTOR']);
        Specification::create(['name' => 'LEADER']);
        Specification::create(['name' => 'STAFF']);
        Specification::create(['name' => 'DIRECTOR']);
        Specification::create(['name' => 'ADMIN']);
    }
}
