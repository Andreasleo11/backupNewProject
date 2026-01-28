<div class="relative" x-data="{ open: false }">
    <button type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all duration-300">
        {{-- Bell icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread badge --}}
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white ring-2 ring-white animate-bounce">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-cloak
        class="absolute right-0 mt-3 w-80 rounded-2xl bg-white/95 backdrop-blur-xl border border-slate-200/60 shadow-2xl shadow-indigo-900/10 p-2 z-[60]"
    >
        <div class="flex items-center justify-between px-3 py-3 border-b border-slate-100 mb-1">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900 text-sm">Notifications</span>
                @if($unreadCount > 0)
                    <span class="px-1.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-tight">
                        {{ $unreadCount }} New
                    </span>
                @endif
            </div>

            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-wider">
                    Mark all read
                </button>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="px-4 py-8 text-center">
                <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3 text-slate-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-900">Your inbox is empty</p>
                <p class="text-[10px] text-slate-400 mt-1">We'll notify you when something happens.</p>
            </div>
        @else
            <ul class="max-h-96 overflow-y-auto custom-scrollbar space-y-0.5">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data ?? [];
                    @endphp
                    <li>
                        <button type="button"
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="group flex w-full items-start gap-3 p-3 text-left rounded-xl transition-all duration-200 
                                      {{ $isUnread ? 'bg-indigo-50/50 hover:bg-indigo-50' : 'hover:bg-slate-50' }}">
                            <div class="relative shrink-0 mt-1">
                                <div class="h-2 w-2 rounded-full transition-all duration-300 
                                           {{ $isUnread ? 'bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)] animate-pulse' : 'bg-slate-300' }}"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] font-bold text-slate-800 leading-tight group-hover:text-indigo-950">
                                    {{ $data['title'] ?? 'Notification' }}
                                </div>
                                @if (!empty($data['message']))
                                    <p class="mt-1 text-[11px] text-slate-500 line-clamp-2 leading-normal">
                                        {{ $data['message'] }}
                                    </p>
                                @endif
                                <p class="mt-1 text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
