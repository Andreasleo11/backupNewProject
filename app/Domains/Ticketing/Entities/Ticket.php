<?php

namespace App\Domains\Ticketing\Entities;

use App\Domains\Ticketing\Enums\TicketPriority;
use App\Domains\Ticketing\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'ticket_number',
        'reporter_id',
        'assigned_to',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
        'first_response_at',
        'resolved_at',
        'on_hold_since',
        'total_hold_time_minutes',
        'reopen_count',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'on_hold_since' => 'datetime',
        'total_hold_time_minutes' => 'integer',
        'reopen_count' => 'integer',
    ];

    // Relationships
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporter_id', 'nik');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class, 'ticket_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get a human-readable duration of total time on hold.
     */
    public function getFormattedHoldTime(): string
    {
        $minutes = $this->total_hold_time_minutes;

        // If currently on hold, add the elapsed time from on_hold_since
        if ($this->status === TicketStatus::ON_HOLD && $this->on_hold_since) {
            $minutes += now()->diffInMinutes($this->on_hold_since);
        }

        if ($minutes <= 0) {
            return '0m';
        }

        return \Carbon\CarbonInterval::minutes($minutes)->cascade()->forHumans(['short' => true]);
    }
}
