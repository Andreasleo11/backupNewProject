<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Entities\TicketActivity;
use App\Domains\Ticketing\Enums\ActivityType;
use App\Domains\Ticketing\Events\TicketAssigned;
use App\Infrastructure\Persistence\Eloquent\Models\User;

class AssignTicketToPIC
{
    public function execute(Ticket $ticket, User $newAssignee, int $assignedByUserId): Ticket
    {
        $oldAssigneeId = $ticket->assigned_to;

        if ($oldAssigneeId === $newAssignee->id) {
            return $ticket;
        }

        $ticket->update([
            'assigned_to' => $newAssignee->id,
            // If this is the first assignment, set first response
            'first_response_at' => $ticket->first_response_at ?? now(),
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $assignedByUserId,
            'type' => ActivityType::ASSIGNMENT,
            'old_state' => $oldAssigneeId ? "Assigned to {$oldAssigneeId}" : 'Unassigned',
            'new_state' => "Assigned to {$newAssignee->name}",
            'reason' => 'Ticket reassigned',
        ]);

        TicketAssigned::dispatch($ticket, $oldAssigneeId, $newAssignee->id);

        return $ticket;
    }
}
