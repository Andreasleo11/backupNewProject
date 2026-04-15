<?php

namespace App\Livewire\Ticketing;

use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Entities\TicketCategory;
use App\Domains\Ticketing\Enums\TicketPriority;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SupportBubble extends Component
{
    public bool $isOpen = false;

    public string $activeTab = 'new'; // 'new' or 'my_tickets'

    // New Ticket Form
    public string $title = '';

    public string $description = '';

    public ?int $category_id = null;

    public string $priority = 'Medium';

    // Notification Polling State
    public bool $hasUnreadUpdates = false;

    #[\Livewire\Attributes\On('open-support-bubble')]
    public function openBubble($data = [])
    {
        $this->isOpen = true;
        $this->hasUnreadUpdates = false;
        if (isset($data['tab'])) {
            $this->activeTab = $data['tab'];
        }
    }

    // We can pull categories dynamically
    public function getCategoriesProperty()
    {
        return TicketCategory::where('is_active', true)->get();
    }

    public function getMyTicketsProperty()
    {
        $user = Auth::user();
        if (! $user || ! $user->employee) {
            return collect();
        }

        return Ticket::with('category')->where('reporter_id', $user->employee->nik)
            ->latest('updated_at')
            ->get();
    }

    public function submitTicket(CreateTicket $action)
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority' => 'required|in:Low,Medium,High,Critical',
        ]);

        $user = Auth::user();
        if (! $user || ! $user->employee) {
            session()->flash('error', 'You must be linked to an employee record to submit a ticket.');

            return;
        }

        $priorityEnum = TicketPriority::from($this->priority);

        $action->execute(
            $user->employee->nik,
            $this->category_id,
            $this->title,
            $this->description,
            $priorityEnum
        );

        $this->reset(['title', 'description', 'category_id', 'priority']);
        $this->activeTab = 'my_tickets';

        session()->flash('success', 'Ticket submitted successfully!');
    }

    // Polled every 30s to check for new PIC activities on the user's active tickets
    public function checkUpdates()
    {
        $user = Auth::user();
        if (! $user || ! $user->employee) {
            return;
        }

        // Has any of my tickets been updated by someone else recently (e.g. last 1 min)?
        // In a true notification system, we'd have a 'read_at' pivot. For MVP KISS, we check latest activity not by the user.
        $recentUpdate = Ticket::where('reporter_id', $user->employee->nik)
            ->whereHas('activities', function ($q) use ($user) {
                $q->where('user_id', '!=', $user->id)
                    ->where('created_at', '>=', now()->subMinutes(1));
            })->exists();

        if ($recentUpdate && ! $this->isOpen) {
            $this->hasUnreadUpdates = true;
        }
    }

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
        if ($this->isOpen) {
            $this->hasUnreadUpdates = false; // Mark as read when opened
        }
    }

    public function render()
    {
        return view('livewire.ticketing.support-bubble');
    }
}
