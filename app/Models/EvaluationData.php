<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationData extends Model
{
    use HasFactory;
    protected $table = 'evaluation_datas';
    public $timestamps = false;
    public $incrementing = false;
  
    
    protected $fillable = [
        'NIK',
        'Month',
        'Alpha',
        'Telat',
        'Izin',
        'Sakit',
        'kerajinan_kerja',
        'kerapian_pakaian',
        'kerapian_rambut',
        'kerapian_sepatu',
        'prestasi',
        'loyalitas',
        'kemampuan_kerja',
        'kecerdasan_kerja',
        'qualitas_kerja',
        'disiplin_kerja',
        'kepatuhan_kerja',
        'lembur',
        'efektifitas_kerja',
        'relawan',
        'integritas',
        'total',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Employee::class, 'NIK', 'NIK');
    }

  
}
