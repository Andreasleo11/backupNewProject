<?php

namespace Database\Seeders;

use App\Models\Specification;
use Illuminate\Database\Seeder;

class SpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specs = [
            1 => '-',
            2 => 'INSPECTOR',
            3 => 'LEADER',
            4 => 'STAFF',
            5 => 'DIRECTOR',
            6 => 'ADMIN',
            7 => 'HEAD',
            14 => 'PURCHASER',
            15 => 'VERIFICATOR',
            16 => 'DESIGN',
            17 => 'SUPERVISOR',
        ];

        foreach ($specs as $id => $name) {
            Specification::updateOrCreate(
                ['id' => $id],
                ['name' => $name]
            );
        }
    }
}
