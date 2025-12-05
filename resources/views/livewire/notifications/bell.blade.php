<div class="relative" x-data="{ open: false }">
    <button type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="relative flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50">
        {{-- Bell icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a4 4 0 00-4 4v1.1c0 .396-.118.783-.34 1.11L4.3 10.1A1.5 1.5 0 005.5 12.5H7a3 3 0 006 0h1.5a1.5 1.5 0 001.2-2.4l-1.36-1.89A2 2 0 0114 7.1V6a4 4 0 00-4-4z" />
            <path d="M8.75 13.5a1.75 1.75 0 103.5 0h-3.5z" />
        </svg>

        {{-- Unread badge --}}
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        class="absolute right-0 mt-2 w-72 rounded-lg border border-slate-200 bg-white py-1 text-xs shadow-lg z-10"
    >
        <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100">
            <span class="font-semibold text-slate-800">Notifications</span>

            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-[11px] font-medium text-slate-500 hover:text-slate-700">
                    Mark all as read
                </button>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="px-3 py-4 text-[11px] text-slate-400">
                No notifications yet.
            </div>
        @else
            <ul class="max-h-80 overflow-y-auto">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data ?? [];
                    @endphp
                    <li
                        class="border-b border-slate-50 last:border-b-0"
                    >
                        <button type="button"
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="flex w-full items-start gap-2 px-3 py-2 text-left hover:bg-slate-50 {{ $isUnread ? 'bg-slate-50' : '' }}">
                            <div class="mt-0.5">
                                <span class="inline-block h-2 w-2 rounded-full {{ $isUnread ? 'bg-rose-500' : 'bg-slate-300' }}"></span>
                            </div>
                            <div class="flex-1">
                                <div class="text-[11px] font-medium text-slate-800">
                                    {{ $data['title'] ?? 'Notification' }}
                                </div>
                                @if (!empty($data['message']))
                                    <div class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $data['message'] }}
                                    </div>
                                @endif
                                <div class="mt-0.5 text-[10px] text-slate-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
