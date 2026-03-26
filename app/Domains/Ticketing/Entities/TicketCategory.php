<?php

namespace App\Domains\Ticketing\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    protected $table = 'ticket_categories';

    protected $fillable = [
        'name',
        'description',
        'sla_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sla_hours' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}
