<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationData extends Model
{
    use HasFactory;
    protected $table = "evaluation_datas";
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        "NIK",
        "dept",
        "Month",
        "Alpha",
        "Telat",
        "Izin",
        "Sakit",
        "kerajinan_kerja",
        "kerapian_kerja",
        "prestasi",
        "loyalitas",
        "perilaku_kerja",
        "kemampuan_kerja",
        "kecerdasan_kerja",
        "qualitas_kerja",
        "disiplin_kerja",
        "kepatuhan_kerja",
        "lembur",
        "efektifitas_kerja",
        "relawan",
        "integritas",
        "total",
        "pengawas",
        "depthead",
        "generalmanager",
        "remark",
    ];

    public function karyawan()
    {
        return $this->belongsTo(Employee::class, "NIK", "NIK");
    }

    public function department()
    {
        return $this->belongsTo(Department::class, "dept", "dept_no");
    }
}
