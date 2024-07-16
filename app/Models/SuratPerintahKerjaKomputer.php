<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPerintahKerjaKomputer extends Model
{
    use HasFactory;
    protected $table = 'surat_perintah_kerja_komputer';

    protected $fillable = [
        'no_dokumen',
        'pelapor',
        'dept',
        'tanggal_lapor',
        'judul_laporan',
        'keterangan_laporan',
        'pic',
        'keterangan_pic',
        'status_laporan',
        'tanggal_selesai',
        'tanggal_estimasi',
    ];

    public function deptRelation()
    {
        return $this->belongsTo(Department::class);
    }
}
