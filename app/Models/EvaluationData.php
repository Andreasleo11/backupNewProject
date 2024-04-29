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
    protected $primaryKey = null;
    
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
        'total',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Employee::class, 'NIK', 'NIK');
    }

    public function getTotalAttribute()
    {
        return $this->Alpha + $this->Telat + $this->Izin;
    }
}
