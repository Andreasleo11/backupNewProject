<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterStock;

class MasterStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '810-black',
            'stock_description' => 'Tinta 810-Black',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '811-color',
            'stock_description' => 'Tinta 811-Color',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '680-black',
            'stock_description' => 'Tinta 680-Black',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '680-color',
            'stock_description' => 'Tinta 680-color',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => 'lq-2190-panjang',
            'stock_description' => 'Tinta LQ-2190-panjang',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        
        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => 'lq-2190-kecil',
            'stock_description' => 'Tinta LQ-2190-kecil',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '644-black-1300-1200',
            'stock_description' => 'Tinta 644 Black 1300/1200',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '644-cyan-1300-1200',
            'stock_description' => 'Tinta 644 Cyan 1300/1200',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '644-magenta-1300-1200',
            'stock_description' => 'Tinta 644 Magenta 1300/1200',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 1,
            'dept_id' => 15,
            'stock_code' => '644-yellow-1300-1200',
            'stock_description' => 'Tinta 644 Yellow 1300/1200',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 2,
            'dept_id' => 15,
            'stock_code' => 'toner-325',
            'stock_description' => 'Toner 325',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 2,
            'dept_id' => 15,
            'stock_code' => 'toner-248',
            'stock_description' => 'Toner 248',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

        MasterStock::create([
            'stock_type_id' => 2,
            'dept_id' => 15,
            'stock_code' => 'toner-279',
            'stock_description' => 'Toner 279',
            'stock_quantity' => 0,
            // Add other fields as needed
        ]);

    }
}
