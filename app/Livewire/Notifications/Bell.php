<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Bell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('notification-created')]
    public function refreshCount(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->unreadCount = 0;
            return;
        }

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead(string $id): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->whereKey($id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
            $this->refreshCount();
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications->markAsRead();
        $this->refreshCount();
    }

    public function render()
    {
        $user = Auth::user();

        $notifications = $user
            ? $user->notifications()->latest()->take(10)->get()
            : collect();

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
        ]);
    }
}
