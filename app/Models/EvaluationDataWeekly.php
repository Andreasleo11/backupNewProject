<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationDataWeekly extends Model
{
    use HasFactory;

    protected $table = "evaluation_data_weekly";
    public $incrementing = false;

    protected $fillable = ["NIK", "dept", "Month", "Alpha", "Telat", "Izin", "Sakit"];

    public function karyawan()
    {
        return $this->belongsTo(Employee::class, "NIK", "NIK");
    }
}
