<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportantDocTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('important_docs_type')->insert([
            'id' => 0,
            'name' => 'Other',
        ]);
    }
}
