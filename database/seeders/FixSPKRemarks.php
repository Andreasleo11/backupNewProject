<?php

namespace Database\Seeders;

use App\Models\SpkRemark;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixSPKRemarks extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->fixStatus();
    }

    private function fixStatus()
    {
        SpkRemark::where("status", 2)->update(["status" => 3]);
        SpkRemark::where("status", 1)->update(["status" => 2]);
        SpkRemark::where("status", 0)->update(["status" => 1]);
    }
}
