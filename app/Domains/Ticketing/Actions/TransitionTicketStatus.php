<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Entities\TicketActivity;
use App\Domains\Ticketing\Enums\ActivityType;
use App\Domains\Ticketing\Enums\TicketStatus;
use App\Domains\Ticketing\Events\TicketResolved;
use Carbon\Carbon;

class TransitionTicketStatus
{
    public function execute(Ticket $ticket, TicketStatus $newStatus, int $userId, ?string $reason = null): Ticket
    {
        $oldStatus = $ticket->status;

        if ($oldStatus === $newStatus) {
            return $ticket;
        }

        // 1. SLA Logic: Leaving ON_HOLD state
        if ($oldStatus === TicketStatus::ON_HOLD && $ticket->on_hold_since) {
            // Note: In MVP we calculate raw minutes. For strict business hours, this can be swapped with a Business Time calculator.
            $minutesOnHold = now()->diffInMinutes($ticket->on_hold_since);
            $ticket->total_hold_time_minutes += $minutesOnHold;
            $ticket->on_hold_since = null;
        }

        // 2. SLA Logic: Entering ON_HOLD state
        if ($newStatus === TicketStatus::ON_HOLD) {
            if (empty($reason)) {
                throw new \InvalidArgumentException('A reason must be provided when placing a ticket on hold.');
            }
            $ticket->on_hold_since = now();
        }

        // 3. Mark resolution
        if ($newStatus === TicketStatus::RESOLVED || $newStatus === TicketStatus::CLOSED) {
            if (!$ticket->resolved_at) {
                $ticket->resolved_at = now();
            }
        }

        // 4. Re-open tracking (Quality KPI)
        if (($oldStatus === TicketStatus::RESOLVED || $oldStatus === TicketStatus::CLOSED) && $newStatus === TicketStatus::OPEN) {
            $ticket->reopen_count++;
            $ticket->resolved_at = null; // Re-open means resolution is void
        }

        $ticket->status = $newStatus;
        $ticket->save();

        // 5. Immutable Audit Trail
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'type' => ActivityType::STATUS_CHANGE,
            'old_state' => $oldStatus->value,
            'new_state' => $newStatus->value,
            'reason' => $reason,
        ]);

        // 6. Fire Domain Events
        if ($newStatus === TicketStatus::RESOLVED) {
            // Calculate Current TTR in minutes
            $ttrMinutes = now()->diffInMinutes($ticket->created_at) - $ticket->total_hold_time_minutes;
            TicketResolved::dispatch($ticket, max(0, $ttrMinutes));
        }

        return $ticket;
    }
}
