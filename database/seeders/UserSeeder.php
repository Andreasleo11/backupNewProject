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
        // DB::table('users')->insert([
        //     'name' => 'admin',
        //     'email' => 'admin@daijo.co.id',
        //     'password' => Hash::make("test1234"),
        //     'role_id' => '1'
        // ], [
        //     'name' => 'user',
        //     'email' => 'user@daijo.co.id',
        //     'password' => Hash::make("test1234"),
        //     'role_id' => '2'
        // ], [
        //     'name' => 'DEDI',
        //     'email' => 'DEDI@daijo.co.id',
        //     'password' => Hash::make("dedi1234"),
        //     'role_id' => '2',
        //     'department' => 'QC'
        // ], [
        //     'name' => 'bernadett',
        //     'email' => 'bernadett@daijo.co.id',
        //     'password' => Hash::make("test1234"),
        //     'role_id' => '2',
        //     'department' => 'HRD'
        // ], [
        //     'name' => 'ahmad',
        //     'email' => 'ahmad@daijo.co.id',
        //     'password' => Hash::make("ahmad1234"),
        //     'role_id' => '2',
        //     'department' => 'QA'
        // ]);

        // DB::table('users')->insert([
        //     'name' => 'Benny',
        //     'email' => 'benny@daijo.co.id',
        //     'password' => Hash::make("benny123"),
        //     'role_id' => '2',
        //     'is_head' => '1'
        // ] );

        // DB::table('users')->insert([
        //     'name' => 'Naya',
        //     'email' => 'naya@daijo.co.id',
        //     'password' => Hash::make("naya1234"),
        //     'role_id' => '2',
        //     'is_head' => '0',
        //     'department'=> 'ACCOUNTING'
        // ] );

        // DB::table('users')->insert([
        //     'name' => 'Bernadet',
        //     'email' => 'bernadet@daijo.co.id',
        //     'password' => Hash::make("bernadet123"),
        //     'role_id' => '2',
        //     'is_head' => '1',
        //     'department'=> 'HRD'
        // ] );

        // DB::table('users')->insert([
        //     'name' => 'Djoni',
        //     'email' => 'djoni@daijo.co.id',
        //     'password' => Hash::make("djoni123"),
        //     'role_id' => '2',
        //     'is_head' => '1',
        //     'department'=> 'DIREKTUR'
        // ] );

        DB::table('users')->insert([
            'name' => 'Vicky',
            'email' => 'vicky@daijo.co.id',
            'password' => Hash::make("vicky123"),
            'role_id' => '2',
            'department'=> 'COMP'
        ] );


    }
}