<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Entities\TicketActivity;
use App\Domains\Ticketing\Enums\ActivityType;

class AddTicketComment
{
    public function execute(Ticket $ticket, int $userId, string $comment): TicketActivity
    {
        if (trim($comment) === '') {
            throw new \InvalidArgumentException('Comment cannot be empty.');
        }

        // We can touch the ticket so its updated_at timestamp changes, which helps with polling/sorting
        $ticket->touch();

        return TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'type' => ActivityType::COMMENT,
            'old_state' => null,
            'new_state' => null,
            'reason' => $comment, // We use 'reason' column as the text payload
        ]);
    }
}
