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

    public function getListeners(): array
    {
        $id = auth()->id();

        return $id
            ? ["echo-private:users.{$id},NotificationPushed" => 'refreshUnread']
            : []; // no listeners if not authed
    }

    public function mount(): void
    {
        $this->refreshUnread();
    }

    public function refreshUnread(): void
    {
        $user = auth()->user();
        $this->unreadCount = $user ? $user->unreadNotifications()->count() : 0;

        // Reset to page 1 so any newly arrived notification appears at the top
        // without the user needing to close and reopen the dropdown.
        $this->resetPage();
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        $this->refreshUnread();

        $this->dispatch('toast', message: 'All notifications marked as read.');

        $this->open = true;
    }

    public function show(string $id): void
    {
        $n = auth()->user()->notifications()->whereKey($id)->firstOrFail();

        if (is_null($n->read_at)) {
            $n->markAsRead();
            $this->refreshUnread();
        }

        $p = $this->present($n);

        $this->selectedId = $n->id;
        $this->selected = [
            'title'      => $p['title'],
            'body'       => $p['body'],
            'url'        => $p['url'] ?? null,
            'type'       => $p['type'],
            'icon'       => $p['icon'],
            'category'   => $p['category'],
            'created_at' => optional($p['created_at'])->toDateTimeString(),
            'read_at'    => optional($p['read_at'])->toDateTimeString(),
            'meta'       => $p['meta'],
        ];

        // Close dropdown before opening the modal (feels snappier)
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

        $q = auth()->user()->notifications()->latest();

        if ($this->filter === 'unread') {
            $q->whereNull('read_at');
        }

        return $q->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.notifications.menu');
    }

    // ---------------------------------------------------------------------------
    // Presentation helpers
    // ---------------------------------------------------------------------------

    /**
     * Normalize any notification into a stable structure for the UI.
     *
     * Any notification class can control its appearance by including these
     * optional keys in its toArray() payload:
     *
     *   - icon      : Boxicons class string, e.g. 'bx bx-check-circle'
     *   - category  : 'success' | 'danger' | 'warning' | 'info' (drives icon color)
     *
     * All other keys are preserved in `meta` and surfaced as extra detail.
     */
    public function present(DatabaseNotification $n): array
    {
        $data = is_array($n->data) ? $n->data : (array) $n->data;

        // 1) Title
        $title = $this->firstFilled($data, ['title', 'subject', 'name'])
                  ?? Str::headline(class_basename($n->type));

        // 2) Body
        $message = $this->firstFilled($data, ['body', 'message', 'content', 'description', 'detail', 'text']);

        // 3) URL (direct key or { route: { name, params } } shape)
        $url = $this->firstFilled($data, ['url', 'link', 'action_url']);
        if (! $url && is_array($route = data_get($data, 'route'))) {
            try {
                $url = route($route['name'] ?? '', $route['params'] ?? []);
            } catch (\Throwable) {
                // ignore bad route payloads
            }
        }

        // 4) Category → drives the icon circle color in the UI
        //    Notification classes should set this in their toArray() payload.
        //    Accepted values: success | danger | warning | info (default)
        $validCategories = ['success', 'danger', 'warning', 'info'];
        $category = $this->firstFilled($data, ['category']);
        $category = in_array($category, $validCategories, strict: true) ? $category : 'info';

        // 5) Icon — notification class can override; otherwise we pick a sensible default by category
        $icon = $this->firstFilled($data, ['icon']) ?? match ($category) {
            'success' => 'bx bx-check-circle',
            'danger'  => 'bx bx-x-circle',
            'warning' => 'bx bx-error',
            default   => 'bx bx-bell',
        };

        // 6) Meta — everything that isn't part of the standard contract
        $knownKeys = [
            'title', 'subject', 'name',
            'body', 'message', 'content', 'description', 'detail', 'text',
            'url', 'link', 'action_url', 'route',
            'icon', 'category',
        ];
        $meta = Arr::except($data, $knownKeys);

        // 7) Brief meta line (status, counts etc.) surfaced below the body in the list
        $metaBriefs = [];
        foreach (['status', 'total_employees', 'count', 'level'] as $k) {
            if (array_key_exists($k, $data)) {
                $label = Str::headline(str_replace('_', ' ', $k));
                $metaBriefs[] = "{$label}: " . $this->stringify($data[$k]);
            }
        }

        return [
            'id'         => $n->id,
            'title'      => $title,
            'body'       => $message ?: null,
            'url'        => $url,
            'icon'       => $icon,
            'category'   => $category,
            'meta'       => $meta,
            'metaBriefs' => $metaBriefs,
            'created_at' => $n->created_at,
            'read_at'    => $n->read_at,
            'type'       => class_basename($n->type),
            'is_unread'  => is_null($n->read_at),
        ];
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
}
