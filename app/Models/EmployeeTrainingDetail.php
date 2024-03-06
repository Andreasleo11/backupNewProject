<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingDetail extends Model
{
    use HasFactory;
    protected $table = 'table_employee_training_details';

    protected $fillable = [
        'header_id',
        'training_name',
        'training_date',
        'is_internal',
        'is_external',
        'result',
        'information',
    ];

    public function trainingHeader()
    {
        return $this->belongsTo(EmployeeTrainingHeader::class);
    }

}
