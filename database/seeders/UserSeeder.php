<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends seeder 
{
    public function run() : void
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@daijo.co.id',
            'password' => Hash::make("test1234"),
            'role_id' => '1'
        ], [
            'name' => 'user',
            'email' => 'user@daijo.co.id',
            'password' => Hash::make("test1234"),
            'role_id' => '2'
        ], [
            'name' => 'DEDI',
            'email' => 'DEDI@daijo.co.id',
            'password' => Hash::make("dedi1234"),
            'role_id' => '2',
            'department' => 'QC'
        ], [
            'name' => 'bernadett',
            'email' => 'bernadett@daijo.co.id',
            'password' => Hash::make("test1234"),
            'role_id' => '2',
            'department' => 'HRD'
        ], [
            'name' => 'ahmad',
            'email' => 'ahmad@daijo.co.id',
            'password' => Hash::make("ahmad1234"),
            'role_id' => '2',
            'department' => 'QA'
        ]);
    }
}