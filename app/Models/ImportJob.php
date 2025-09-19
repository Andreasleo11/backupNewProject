<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        "type",
        "total_rows",
        "processed_rows",
        "status",
        "error",
        "error_log_path",
        "started_at",
        "finished_at",
        "source_disk",
        "source_path",
    ];

    protected $casts = [
        "started_at" => "datetime",
        "finished_at" => "datetime",
    ];
}
