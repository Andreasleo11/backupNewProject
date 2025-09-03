<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;

class FormKeluar extends Model
{
    use RequiresSignature;
    use HasFactory;

    protected $fillable = [
        "name",
        "doc_num",
        "jabatan",
        "department",
        "alasan_izin_keluar",
        "pengganti",
        "keperluan",
        "tanggal_masuk",
        "no_karyawan",
        "tanggal_permohonan",
        "keterangan_user",
        "waktu_keluar",
        "jam_keluar",
        "jam_kembali",
        "autograph_1",
        "autograph_user_1",
        "is_accept",
        "is_security",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the current date in the required format
            $date = now()->format("dmy"); // Day-Month-Year format

            // Fetch the last record's doc_num for the current date
            $latest = static::where("doc_num", "like", "%/{$date}/%")
                ->orderBy("id", "desc")
                ->first();

            if ($latest) {
                // Extract the increment part from the latest doc_num
                $lastIncrement = (int) substr($latest->doc_num, -3); // Assuming the increment is always 3 digits
            } else {
                $lastIncrement = 0; // No records found for today
            }

            // Calculate the next increment number
            $increment = str_pad($lastIncrement + 1, 3, "0", STR_PAD_LEFT);

            // Build the custom ID
            $customId = "FK/{$date}/{$increment}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }
}
