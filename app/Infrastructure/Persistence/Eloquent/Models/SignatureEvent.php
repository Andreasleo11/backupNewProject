<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class SignatureEvent extends Model
{
    public $timestamps = false; // we manage created_at

    protected $table = 'signature_events';

    protected $fillable = [
        'user_signature_id', 'event', 'context', 'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
