<?php

namespace App\Domains\Ticketing\Events;

use App\Domains\Ticketing\Entities\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Ticket $ticket;
    public int $timeToResolveMinutes;

    public function __construct(Ticket $ticket, int $timeToResolveMinutes)
    {
        $this->ticket = $ticket;
        $this->timeToResolveMinutes = $timeToResolveMinutes;
    }
}
