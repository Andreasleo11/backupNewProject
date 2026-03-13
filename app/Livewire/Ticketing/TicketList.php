<?php

namespace App\Livewire\Ticketing;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Enums\TicketPriority;
use App\Domains\Ticketing\Enums\TicketStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TicketList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $priorityFilter = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingPriorityFilter() { $this->resetPage(); }

    public function gotoDetail($id)
    {
        return $this->redirectRoute('ticketing.show', ['ticket' => $id]);
    }

    #[Layout('new.layouts.app')]
    public function render()
    {
        $query = Ticket::with(['reporter', 'assignee', 'category'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('ticket_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->priorityFilter, fn($q) => $q->where('priority', $this->priorityFilter))
            ->latest('created_at');

        return view('livewire.ticketing.ticket-list', [
            'tickets' => $query->paginate(15),
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
        ]);
    }
}
