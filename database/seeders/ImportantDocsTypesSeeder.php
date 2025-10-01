<?php

namespace Database\Seeders;

use App\Models\hrd\ImportantDocType;
use Illuminate\Database\Seeder;

class ImportantDocsTypesSeeder extends Seeder
{
    public function run(): void
    {
        ImportantDocType::create(['name' => 'Other']);
        ImportantDocType::create(['name' => 'BPKB']);
        ImportantDocType::create(['name' => 'KITAS']);
        ImportantDocType::create(['name' => 'Asuransi']);
        ImportantDocType::create(['name' => 'Sewa rumah']);
        ImportantDocType::create(['name' => 'STNK']);
        ImportantDocType::create(['name' => 'Forklift']);
        ImportantDocType::create(['name' => 'Crane']);
        ImportantDocType::create(['name' => 'Listrik']);
    }
}
