<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class Bell extends Component
{
    public int $unreadCount = 0;

    public function getListeners(): array
    {
        $id = auth()->id();

        return $id
            ? ["echo-private:users.{$id},NotificationPushed" => 'refreshCount']
            : [];
    }

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function refreshCount(): void
    {
        $user = auth()->user();
        $this->unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    }

    public function markAsRead(string $id): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $notification = $user->notifications()->whereKey($id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
            $this->refreshCount();
        }

        // Return the action URL so Alpine can navigate to it
        return data_get($notification->data, 'action_url')
            ?? data_get($notification->data, 'url')
            ?? null;
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications->markAsRead();
        $this->refreshCount();
    }

    public function render()
    {
        $user = auth()->user();

        $notifications = $user
            ? $user->notifications()->latest()->take(12)->get()->map(fn ($n) => $this->present($n))
            : collect();

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
        ]);
    }

    // ---------------------------------------------------------------------------
    // Presentation helper — same standard contract as Menu
    // ---------------------------------------------------------------------------

    /**
     * Normalize a notification into a stable structure.
     *
     * Each notification class can control its appearance via toArray():
     *   icon     — Boxicons class, e.g. 'bx bx-check-circle'  (optional)
     *   category — 'success' | 'danger' | 'warning' | 'info'  (optional, default: info)
     */
    protected function present(DatabaseNotification $n): array
    {
        $data = is_array($n->data) ? $n->data : (array) $n->data;

        $title = $this->firstFilled($data, ['title', 'subject', 'name'])
            ?? Str::headline(class_basename($n->type));

        $message = $this->firstFilled($data, ['body', 'message', 'content', 'description', 'detail', 'text']);

        $url = $this->firstFilled($data, ['action_url', 'url', 'link']);
        if (! $url && is_array($route = data_get($data, 'route'))) {
            try {
                $url = route($route['name'] ?? '', $route['params'] ?? []);
            } catch (\Throwable) {
                // ignore bad route payloads
            }
        }

        $validCategories = ['success', 'danger', 'warning', 'info'];
        $category = $this->firstFilled($data, ['category']);
        $category = in_array($category, $validCategories, strict: true) ? $category : 'info';

        $icon = $this->firstFilled($data, ['icon']) ?? match ($category) {
            'success' => 'bx bx-check-circle',
            'danger'  => 'bx bx-x-circle',
            'warning' => 'bx bx-error',
            default   => 'bx bx-bell',
        };

        return [
            'id'        => $n->id,
            'title'     => $title,
            'message'   => $message,
            'url'       => $url,
            'icon'      => $icon,
            'category'  => $category,
            'is_unread' => is_null($n->read_at),
            'created_at'=> $n->created_at,
        ];
    }

    protected function firstFilled(array $data, array $keys): ?string
    {
        foreach ($keys as $k) {
            $v = data_get($data, $k);
            if (is_string($v) && trim($v) !== '') {
                return $v;
            }
            if (is_numeric($v)) {
                return (string) $v;
            }
        }

        return null;
    }
}
