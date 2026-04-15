<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    {{-- Bell button --}}
    <button type="button" @click="open = !open"
        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 transition-all duration-300 active:scale-95">

        {{-- Bell icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
            class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread badge --}}
        @if ($unreadCount > 0)
            <span
                class="absolute -top-[5px] -right-[5px] flex h-[18px] min-w-[18px] items-center justify-center rounded-full
                         bg-gradient-to-r from-rose-500 to-pink-600 shadow-lg shadow-rose-500/40
                         px-1 text-[9px] font-black text-white ring-[2.5px] ring-white animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="absolute right-[-60px] sm:right-0 mt-3 w-[calc(100vw-48px)] sm:w-80 rounded-2xl bg-white/90 backdrop-blur-xl
                border border-slate-200/60 shadow-2xl shadow-blue-900/10 p-2 z-[60] ring-1 ring-black/5 origin-top sm:origin-top-right">

        {{-- Header --}}
        <div class="flex items-center justify-between px-3 py-3 border-b border-slate-100 mb-1">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900 text-sm">Notifications</span>
                @if ($unreadCount > 0)
                    <span
                        class="px-1.5 py-0.5 rounded-lg bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-tight">
                        {{ $unreadCount }} New
                    </span>
                @endif
            </div>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                    class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-wider">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- List --}}
        @if ($notifications->isEmpty())
            <div class="px-4 py-8 text-center">
                <div
                    class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3 text-slate-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-900">All caught up!</p>
                <p class="text-[10px] text-slate-400 mt-1">No notifications right now.</p>
            </div>
        @else
            <ul class="max-h-96 overflow-y-auto custom-scrollbar space-y-0.5">
                @foreach ($notifications as $n)
                    @php
                        // Category → gradient icon container
                        $iconBg = match ($n['category']) {
                            'success' => 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-200',
                            'danger' => 'bg-gradient-to-br from-rose-500 to-pink-600 shadow-rose-200',
                            'warning' => 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-amber-200',
                            default => 'bg-gradient-to-br from-blue-500 to-violet-600 shadow-blue-200',
                        };
                    @endphp

                    <li>
                        {{-- Click marks as read + navigates to action_url if present --}}
                        <button type="button" wire:click.prevent="markAsRead('{{ $n['id'] }}')"
                            x-on:click="
                                    $wire.markAsRead('{{ $n['id'] }}').then(url => {
                                        if (url) window.location.href = url;
                                    });
                                    open = false;
                                "
                            class="group flex w-full items-start gap-3 p-2.5 text-left rounded-xl transition-all duration-200
                                       {{ $n['is_unread'] ? 'bg-blue-50/60 hover:bg-blue-50' : 'hover:bg-slate-50' }}">

                            {{-- Category icon container --}}
                            <div class="relative shrink-0 mt-0.5">
                                <div
                                    class="h-8 w-8 rounded-lg flex items-center justify-center text-white shadow-lg {{ $iconBg }}">
                                    <i class="{{ $n['icon'] }} text-xs"></i>
                                </div>
                                {{-- Small Unread Pulse Dot (absolute on top of icon) --}}
                                @if ($n['is_unread'])
                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span
                                            class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white"></span>
                                    </span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 pt-0.5">
                                <div
                                    class="text-[11px] font-bold text-slate-800 leading-tight group-hover:text-blue-600 transition-colors truncate">
                                    {{ $n['title'] }}
                                </div>
                                @if (!empty($n['message']))
                                    <p
                                        class="mt-0.5 text-[11px] text-slate-500 line-clamp-2 leading-normal group-hover:text-slate-600">
                                        {{ $n['message'] }}
                                    </p>
                                @endif
                                <p class="mt-1 text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                                    {{ $n['created_at']->diffForHumans() }}
                                </p>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
