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
            // Get the current date in the required format
            $date = now()->format('dmy'); // Day-Month-Year format (e.g., '240819')

            // Fetch the last record's doc_num for the current date
            $latest = static::where('doc_num', 'like', "FC/{$date}/%")
                            ->orderBy('id', 'desc')
                            ->first();

            if ($latest) {
                // Extract the increment part from the latest doc_num
                $lastIncrement = (int) substr($latest->doc_num, -3); // Assuming the increment is always 3 digits
            } else {
                $lastIncrement = 0; // No records found for today
            }

            // Calculate the next increment number
            $increment = str_pad($lastIncrement + 1, 3, '0', STR_PAD_LEFT);

            // Build the custom ID
            $customId = "FC/{$date}/{$increment}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }
}
