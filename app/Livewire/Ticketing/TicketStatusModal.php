<?php

namespace App\Livewire\Ticketing;

use App\Domains\Ticketing\Actions\TransitionTicketStatus;
use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Enums\TicketStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TicketStatusModal extends Component
{
    public ?Ticket $ticket = null;

    public string $newStatus = '';

    public string $reason = '';

    public bool $isOpen = false;

    #[\Livewire\Attributes\On('openStatusModal')]
    public function open($ticket)
    {
        $this->ticket = Ticket::find($ticket);
        if (! $this->ticket) {
            return;
        }

        $this->newStatus = $this->ticket->status->value;
        $this->reason = '';
        $this->isOpen = true;
    }

    public function save(TransitionTicketStatus $action)
    {
        $this->validate([
            'newStatus' => 'required',
            'reason' => 'required_if:newStatus,On Hold',
        ]);

        $statusEnum = TicketStatus::from($this->newStatus);

        $action->execute($this->ticket, $statusEnum, Auth::id(), $this->reason ?: null);

        $this->isOpen = false;
        $this->dispatch('ticketUpdated');
    }

    public function render()
    {
        return view('livewire.ticketing.ticket-status-modal', [
            'statuses' => TicketStatus::cases(),
        ]);
    }
}
