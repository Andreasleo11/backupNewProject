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
        Specification::firstOrCreate(["name" => "-"]);
        Specification::firstOrCreate(["name" => "INSPECTOR"]);
        Specification::firstOrCreate(["name" => "LEADER"]);
        Specification::firstOrCreate(["name" => "STAFF"]);
        Specification::firstOrCreate(["name" => "DIRECTOR"]);
        Specification::firstOrCreate(["name" => "ADMIN"]);
        Specification::firstOrCreate(["name" => "HEAD"]);
        Specification::firstOrCreate(["name" => "PURCHASER"]);
        Specification::firstOrCreate(["name" => "VERIFICATOR"]);
        Specification::firstOrCreate(["name" => "DESIGN"]);
    }
}
