<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingHeader extends Model
{
    use HasFactory;
    protected $table = 'table_employee_training_headers';

    protected $fillable = [
        'doc_num',
        'name',
        'nik',
        'department',
        'mulai_bekerja',
    ];


    public function trainingDetail()
    {
        return $this->hasMany(EmployeeTrainingDetail::class, 'header_id','id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the current record's position in the table
            $position = static::count() + 1;

            // Calculate the increment number
            $increment = str_pad($position, 4, '0', STR_PAD_LEFT);

            // Get the date portion
            $date = now()->format('ymd'); // Assuming you want the current date

            // Build the custom ID
            $customId = "ETR/{$increment}/{$date}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }

}
