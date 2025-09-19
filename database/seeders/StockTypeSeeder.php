<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StockType;

class StockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockType::truncate();
        StockType::create([
            "name" => "Tinta",
        ]);

        StockType::create([
            "name" => "Toner",
        ]);
    }
}
