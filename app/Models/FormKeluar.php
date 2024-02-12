<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormKeluar extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'name',
        'doc_num', 
        'jabatan',
        'department',
        'alasan_izin_keluar',
        'pengganti',
        'keperluan',
        'tanggal_masuk',
        'no_karyawan',
        'tanggal_permohonan',
        'keterangan_user',
        'waktu_keluar',
        'jam_keluar',
        'jam_kembali',
        'autograph_1',
        'autograph_user_1',
        'is_accept',
        'is_security',
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
            $customId = "EPF/{$increment}/{$date}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }
}
