<?php

namespace App\Domains\Ticketing\Entities;

use App\Domains\Ticketing\Enums\ActivityType;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    use HasFactory;

    protected $table = 'ticket_activities';

    public const UPDATED_AT = null; // Immutable log

    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'old_state',
        'new_state',
        'reason',
    ];

    protected $casts = [
        'type' => ActivityType::class,
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
