<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Entities\TicketActivity;
use App\Domains\Ticketing\Enums\ActivityType;
use App\Domains\Ticketing\Enums\TicketPriority;
use App\Domains\Ticketing\Enums\TicketStatus;
use App\Domains\Ticketing\Events\TicketCreated;
use Illuminate\Support\Str;

class CreateTicket
{
    public function execute(string $reporterId, int $categoryId, string $title, string $description, TicketPriority $priority): Ticket
    {
        $ticketNumber = 'IT-' . now()->format('Ym') . '-' . strtoupper(Str::random(4));

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'reporter_id' => $reporterId,
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'status' => TicketStatus::OPEN,
            'priority' => $priority,
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => null, // System action, but triggered by reporter
            'type' => ActivityType::STATUS_CHANGE,
            'old_state' => null,
            'new_state' => TicketStatus::OPEN->value,
            'reason' => 'Ticket created via Support Concierge.',
        ]);

        TicketCreated::dispatch($ticket);

        return $ticket;
    }
}
