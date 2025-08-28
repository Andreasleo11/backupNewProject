<?php

namespace Database\Seeders;

use App\Models\SuratPerintahKerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixSPK extends Seeder
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
        $spks = SuratPerintahKerja::where("to_department", "COMPUTER")->get();
        foreach ($spks as $spk) {
            if ($spk->tanggal_selesai) {
                $spk->status_laporan = 4;
            } elseif ($spk->tindakan && $spk->tanggal_mulai && $spk->tanggal_estimasi) {
                $spk->status_laporan = 3;
            }
        }
    }
}
