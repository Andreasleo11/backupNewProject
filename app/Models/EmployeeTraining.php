<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    use HasFactory;

    protected $fillable = ["employee_id", "description", "last_training_at", "evaluated"];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
