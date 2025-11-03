<?php

// App\Infrastructure\Persistence\Eloquent\Models\UserSignature.php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class UserSignature extends Model
{
    protected $fillable = [
        'user_id', 'label', 'kind', 'file_path', 'svg_path', 'sha256', 'is_default', 'metadata', 'revoked_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
        'revoked_at' => 'datetime',
    ];

    public function scopeActive($q)
    {
        return $q->whereNull('revoked_at');
    }
}
