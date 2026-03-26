<div class="dropdown position-relative" wire:key="notif-menu" x-data="{ open: @entangle('open').live, ready: @js($ready) }" @click.outside="open = false"
    @keydown.escape.window="open = false">
    {{-- Bell button --}}
    <button type="button" class="btn btn-icon btn-ghost-light position-relative" :aria-expanded="open.toString()"
        aria-haspopup="true" x-ref="trigger"
        @click="
      open = !open;
      if (open && !ready) { ready = true; $wire.set('ready', true); }
      $nextTick(() => { if (open) $refs.menu?.focus(); });
    ">
        <i class='bx bx-bell fs-5'></i>

        {{-- Floating badge with subtle pulse --}}
        <span class="badge-floating {{ $unreadCount ? '' : 'd-none' }}">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            <span class="visually-hidden">unread</span>
            <span class="dot-pulse" aria-hidden="true"></span>
        </span>
    </button>

    {{-- Dropdown --}}
    <div x-ref="menu" class="dropdown-menu notif-menu shadow-lg p-0" x-cloak x-show="open"
        x-transition.opacity.scale.origin.top.right :class="{ 'show': open }" role="menu" tabindex="-1"
        @keydown.escape.stop="open = false; $nextTick(() => $refs.trigger.focus())">

        {{-- Header --}}
        <div class="notif-header d-flex align-items-center justify-content-between px-3 py-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-bell fs-5"></i>
                <span class="fw-semibold">Notifications</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-light" wire:click="refreshUnread" title="Refresh">
                    <i class='bx bx-refresh'></i>
                </button>
                @if ($unreadCount > 0)
                    <button class="btn btn-sm btn-primary" wire:click="markAllRead">
                        Mark all read
                    </button>
                @endif
            </div>
        </div>

        {{-- Filter chips --}}
        <div class="px-3 pt-2 pb-1">
            <div class="d-flex flex-wrap gap-2">
                <button class="chip {{ $filter === 'all' ? 'chip-active' : '' }}" wire:click="$set('filter','all')">
                    All
                </button>
                <button class="chip {{ $filter === 'unread' ? 'chip-active' : '' }}"
                    wire:click="$set('filter','unread')">
                    Unread ({{ $unreadCount }})
                </button>
            </div>
        </div>

        {{-- List --}}
        <div class="list-group list-group-flush notif-list">
            @if (!$ready)
                {{-- Skeleton placeholders before first open --}}
                @for ($i = 0; $i < 4; $i++)
                    <div class="list-group-item">
                        <div class="d-flex gap-3">
                            <span class="skeleton skeleton-avatar"></span>
                            <div class="flex-grow-1">
                                <div class="skeleton skeleton-line w-75 mb-2"></div>
                                <div class="skeleton skeleton-line w-50"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            @else
                @php $items = $this->items; @endphp

                @forelse ($items as $n)
                    @php $p = $this->present($n); @endphp

                    <a href="#" wire:key="notif-{{ $p['id'] }}"
                        class="list-group-item notif-item {{ $p['is_unread'] ? 'unread' : '' }}"
                        wire:click.prevent="show('{{ $p['id'] }}')" role="menuitem">
                        <div class="d-flex gap-3">

                            {{-- Icon circle — color driven by category --}}
                            <div class="notif-icon notif-icon--{{ $p['category'] }}">
                                <i class="{{ $p['icon'] }}"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 title {{ $p['is_unread'] ? 'fw-semibold' : '' }}">
                                        {{ $p['title'] }}</h6>
                                    <small
                                        class="text-muted ms-2">{{ optional($p['created_at'])->diffForHumans() }}</small>
                                </div>

                                @if (!empty($p['body']))
                                    <p class="mb-0 small text-muted clamp-2">{{ $p['body'] }}</p>
                                @endif

                                @if (!empty($p['metaBriefs']))
                                    <div class="mt-1 small text-muted">{{ implode(' • ', $p['metaBriefs']) }}</div>
                                @endif

                                @if ($p['is_unread'])
                                    <span class="pill-new">NEW</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-inbox fs-1 d-block mb-2"></i>
                            No notifications yet
                        </div>
                    </div>
                @endforelse

                @if ($items instanceof \Illuminate\Contracts\Pagination\Paginator && $items->hasMorePages())
                    <div class="p-2 text-center bg-body-tertiary rounded-bottom">
                        <button class="btn btn-sm btn-outline-secondary" wire:click="loadMore">
                            Load more
                        </button>
                    </div>
                @endif
            @endif
        </div>

    </div>

    {{-- Modal (detail) --}}
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true"
        aria-labelledby="notificationModalLabel" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-xl text-black">
                <div class="modal-header border-0">
                    {{-- Icon + title row --}}
                    <div class="d-flex align-items-center gap-2">
                        @if ($selected)
                            <span class="notif-icon notif-icon--{{ $selected['category'] ?? 'info' }} notif-icon--sm">
                                <i class="{{ $selected['icon'] ?? 'bx bx-bell' }}"></i>
                            </span>
                        @endif
                        <h6 class="modal-title mb-0" id="notificationModalLabel">
                            {{ $selected['title'] ?? 'Notification' }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"
                        @click="$nextTick(() => $refs.trigger.focus())"></button>
                </div>

                <div class="modal-body">
                    @if ($selected)
                        <div class="mb-2">
                            <small class="text-muted">
                                {{ \Illuminate\Support\Carbon::parse($selected['created_at'])->diffForHumans() }} •
                                {{ $selected['type'] }}
                            </small>
                        </div>
                        <hr class="my-2">

                        @if (!empty($selected['body']))
                            <div class="mb-3 fs-6">{!! nl2br(e($selected['body'])) !!}</div>
                        @else
                            <p class="text-muted fst-italic mb-3">No additional details provided.</p>
                        @endif

                        @if (!empty($selected['url']))
                            <div class="text-end">
                                <a href="{{ $selected['url'] }}" class="btn btn-primary btn-sm">View details</a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap modal trigger via Livewire event --}}
    <script type="module">
        document.addEventListener('livewire:initialized', () => {
            window.addEventListener('show-notif-modal', () => {
                const el = document.getElementById('notificationModal');
                bootstrap.Modal.getOrCreateInstance(el).show();
            });
        });
    </script>
</div>
