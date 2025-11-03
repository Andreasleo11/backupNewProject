<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryMaintenanceInventoryReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Cleaning Group' => ['Casing CPU', 'Printer/Scanner'],
            'Check Monitor Group' => [
                'Pixel',
                'Tampilan (Warna, RGB, Posisi Layar Dalam)',
                'Fungsi Port',
            ],
            'Check PC/CPU Group' => [
                'Komponen (Motherboard, RAM, FAN, VGA dan Hardisk)',
                'Fungsi Port (USB, VGA, HDMI)',
                'Mouse, Keyboard',
            ],
            'Check Koneksi Internet Group' => [
                'Kabel LAN',
                'Port LAN CPU',
                'Koneksi ke HUB/Router',
            ],
            'Check Printer/Scanner Group' => [
                'Kualitas Cetak Dokumen',
                'Kualitas Sparepart untuk Tinta/Toner',
            ],
            'Check Software/System Group' => [
                'BIOS (Tanggal & Jam, Pointing Hardisk, Boot Priorities)',
                'Operating System (Crash/Bluescreen/Error)',
                'Driver Manager (Network,Printer, Scanner)',
                'Software (Office, SAP, Browser, dsb)',
                'Sharing (Connect Printer, File, FLogin)',
            ],
        ];

        foreach ($categories as $categoryName => $groups) {
            $categoryId = DB::table('group_maintenance_inventory_reports')
                ->where('name', $categoryName)
                ->value('id');

            foreach ($groups as $groupName) {
                DB::table('category_maintenance_inventory_reports')->insert([
                    'group_id' => $categoryId,
                    'name' => $groupName,
                ]);
            }
        }
    }
}
