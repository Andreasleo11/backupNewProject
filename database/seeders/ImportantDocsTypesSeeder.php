<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\hrd\ImportantDocType;

class ImportantDocsTypesSeeder extends Seeder
{
    public function run(): void
    {
        ImportantDocType::create(['name' => 'Other',]);
        ImportantDocType::create(['name' => 'BPKB',]);
        ImportantDocType::create(['name' => 'KITAS',]);
        ImportantDocType::create(['name' => 'Asuransi',]);
        ImportantDocType::create(['name' => 'Sewa rumah',]);
        ImportantDocType::create(['name' => 'STNK',]);
        ImportantDocType::create(['name' => 'Forklift',]);
        ImportantDocType::create(['name' => 'Crane',]);
        ImportantDocType::create(['name' => 'Listrik',]);
    }
}
