<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPageVisit extends Model
{
    protected $fillable = [
        'user_id',
        'route_name',
        'visit_count',
        'last_visited_at',
    ];

    protected $casts = [
        'last_visited_at' => 'datetime',
        'visit_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
