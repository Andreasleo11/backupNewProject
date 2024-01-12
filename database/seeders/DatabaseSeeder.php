<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@daijo.co.id',
            'password' => Hash::make("test1234"),
            'role_id' => '1'
        ]);
        
        DB::table('users')->insert([
            'name' => 'user',
            'email' => 'user@daijo.co.id',
            'password' => Hash::make("test1234"),
            'role_id' => '2'
        ]);
    }
}
