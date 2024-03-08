<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCuti extends Model
{
    use HasFactory;

    protected $table = 'form_cuti';

    protected $fillable = [
        'name',
        'doc_num', 
        'jabatan',
        'department',
        'jenis_cuti',
        'pengganti',
        'keperluan',
        'tanggal_masuk',
        'no_karyawan',
        'tanggal_permohonan',
        'mulai_tanggal',
        'sampai_tanggal',
        'keterangan_user',
        'waktu_cuti',
        'autograph_1',
        'autograph_user_1',
        'is_accept',
    ];

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
            $customId = "LRF/{$increment}/{$date}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }
}
