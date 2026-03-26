<?php

namespace App\Livewire\Ticketing;

use App\Domains\Ticketing\Actions\AssignTicketToPIC;
use App\Domains\Ticketing\Entities\Ticket;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TicketDetail extends Component
{
    public Ticket $ticket;

    // Refresh when modal updates status
    protected $listeners = ['ticketUpdated' => '$refresh'];

    public string $newComment = '';

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function assignToMe(AssignTicketToPIC $action)
    {
        $action->execute($this->ticket, Auth::user(), Auth::id());
        $this->ticket->refresh();
    }

    public function postComment(\App\Domains\Ticketing\Actions\AddTicketComment $action)
    {
        $this->validate(['newComment' => 'required|string']);
        
        $action->execute($this->ticket, Auth::id(), $this->newComment);
        
        $this->newComment = '';
        $this->ticket->refresh();
    }

    #[Layout('new.layouts.app')]
    public function render()
    {
        return view('livewire.ticketing.ticket-detail', [
            'ticketData' => Ticket::with(['reporter', 'assignee', 'category', 'activities.user'])
                ->findOrFail($this->ticket->id),
        ]);
    }
}
