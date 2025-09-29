<?php

namespace Database\Seeders;

use App\Models\StockType;
use Illuminate\Database\Seeder;

class StockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockType::truncate();
        StockType::create([
            'name' => 'Tinta',
        ]);

        StockType::create([
            'name' => 'Toner',
        ]);
    }
}
