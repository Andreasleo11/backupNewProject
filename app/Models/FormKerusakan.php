<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormKerusakan extends Model
{
    use HasFactory;

    protected $table = 'form_kerusakan';

    protected $fillable = [
        'customer',
        'release_date',
        'nama_barang',
        'proses',
        'masalah',
        'sebab',
        'penanggulangan',
        'pic',
        'target',
        'keterangan',
    ];
}
