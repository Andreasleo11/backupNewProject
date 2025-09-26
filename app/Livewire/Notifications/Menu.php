<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

final class Menu extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public bool $open = false; // dropdown open state

    public bool $ready = false; // lazy-load feed after first open

    public int $unreadCount = 0;

    public ?string $selectedId = null; // currently opened notification

    public ?array $selected = null; // payload for modal

    public string $filter = 'all'; // 'all' | 'unread'

    public $authUser;

    public function getListeners()
    {
        $id = auth()->id();

        return $id
            ? ["echo:users.{$id},notification.pushed" => 'refreshUnread']
            : []; // no listeners if not authed
    }

    public function mount(): void
    {
        $this->authUser = auth()->user();
        $this->refreshUnread();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
        if ($this->open && ! $this->ready) {
            $this->ready = true; // first open → fetch feed
        }
    }

    public function refreshUnread(): void
    {
        $user = $this->authUser;
        $this->unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    }

    public function markAsRead(string $id): void
    {
        $n = $this->authUser->notifications()->whereKey($id)->firstOrFail();
        if (is_null($n->read_at)) {
            $n->markAsRead();
            $this->refreshUnread();
        }
        // keep dropdown open (UX)
        $this->open = true;
    }

    public function markAllRead(): void
    {
        $user = $this->authUser;
        $user->unreadNotifications->markAsRead();
        $this->refreshUnread();

        $this->dispatch('toast', message: 'All notifications marked as read.');

        $this->open = true;
    }

    public function show(string $id): void
    {
        $n = $this->authUser->notifications()->whereKey($id)->firstOrFail();

        if (is_null($n->read_at)) {
            $n->markAsRead();
            $this->refreshUnread();
        }

        $p = $this->present($n);

        $this->selectedId = $n->id;
        $this->selected = [
            'title' => $p['title'],
            'body' => $p['body'],
            'url' => $p['url'] ?? null,
            'type' => $p['type'],
            'created_at' => optional($p['created_at'])->toDateTimeString(),
            'read_at' => optional($p['read_at'])->toDateTimeString(),
            'meta' => $p['meta'],
        ];

        // Close dropdown before opening the modal (optional but feels snappier)
        $this->open = false;

        // Open Bootstrap modal from Livewire
        $this->dispatch('show-notif-modal');
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        if (! $this->ready) {
            return collect(); // empty before first open
        }

        $q = $this->authUser->notifications()->latest();
        if ($this->filter === 'unread') {
            $q->whereNull('read_at');
        }

        return $q->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.notifications.menu');
    }

    // Return the first non-empty string/number from a list of keys
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

    protected function stringify(mixed $v): string
    {
        if (is_null($v)) {
            return '';
        }
        if (is_scalar($v)) {
            return (string) $v;
        }

        return trim(json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?: '';
    }

    /**
     * Normalize any notification into a stable structure for the UI.
     */
    public function present(DatabaseNotification $n): array
    {
        $data = is_array($n->data) ? $n->data : (array) $n->data;

        // 1) Title
        $title = $this->firstFilled($data, ['title', 'subject', 'name'])
                  ?? Str::headline(class_basename($n->type));

        // 2) Body (message/description/etc). You can join multiple pieces.
        $message = $this->firstFilled($data, ['body', 'message', 'content', 'description', 'detail', 'text']);

        // 3) URL (supports direct url or a route payload)
        $url = $this->firstFilled($data, ['url', 'link', 'action_url']);
        if (! $url && is_array($route = data_get($data, 'route'))) {
            try {
                $url = route($route['name'] ?? '', $route['params'] ?? []);
            } catch (\Throwable $e) {
                // ignore bad route payloads
            }
        }

        // 4) Icon (or fall back by type)
        $icon = $this->firstFilled($data, ['icon', 'icon_class']) ?? match (class_basename($n->type)) {
            'OrderShipped' => 'bx bx-package',
            'UserJoined' => 'bx bx-user-plus',
            default => 'bx bx-bell',
        };

        // 5) Meta (surface a few useful extras like total_employees, status, etc)
        $knownKeys = [
            'title', 'subject', 'name', 'status', 'type', 'body', 'message', 'content', 'description', 'detail', 'text',
            'url', 'link', 'action_url', 'route', 'icon', 'icon_class',
        ];
        $meta = Arr::except($data, $knownKeys);

        // Optional: build a short “meta brief” line (2–3 items)
        $metaBriefs = [];
        foreach (['status', 'total_employees', 'count', 'level'] as $k) {
            if (array_key_exists($k, $data)) {
                $label = Str::headline(str_replace('_', ' ', $k));
                $metaBriefs[] = "{$label}: ".$this->stringify($data[$k]);
            }
        }

        return [
            'id' => $n->id,
            'title' => $title,
            'body' => $message ?: null,
            'url' => $url,
            'icon' => $icon,
            'meta' => $meta,        // full leftovers
            'metaBriefs' => $metaBriefs,  // short summary line
            'created_at' => $n->created_at,
            'read_at' => $n->read_at,
            'type' => class_basename($n->type),
            'is_unread' => is_null($n->read_at),
        ];
    }
}
