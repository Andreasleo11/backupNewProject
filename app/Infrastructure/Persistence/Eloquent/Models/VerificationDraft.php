<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationDraft extends Model
{
    protected $fillable = [
        'user_id',
        'report_key',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
