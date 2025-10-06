<?php

namespace Database\Seeders;

use App\Models\Requirement;
use Illuminate\Database\Seeder;

class RequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Requirement::firstOrCreate(
            ['code' => 'ORG_STRUCTURE'],
            [
                'name' => 'Organization Structure',
                'description' => 'Latest org chart per department.',
                'allowed_mimetypes' => ['application/pdf', 'image/png', 'image/jpeg'],
                'min_count' => 1,
                'validity_days' => 365,
                'frequency' => 'yearly',
                'requires_approval' => true,
            ]
        );
    }
}
