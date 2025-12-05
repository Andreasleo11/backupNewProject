<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'SUPERADMIN']);
        Role::create(['name' => 'STAFF']);
        Role::create(['name' => 'USER']);
    }
}
