<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'snapshot',
        'after_snapshot',
        'changes',
        'description',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'after_snapshot' => 'array',
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
