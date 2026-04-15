<div x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}"
    x-transition:enter="transition-all duration-300 ease-out" x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0" class="px-3 py-4 mt-2 mb-1">

    {{-- Header --}}
    <div class="flex items-center gap-2.5 mb-3.5">
        <div
            class="flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm shadow-amber-200">
            @include('new.layouts.partials.nav-icon', ['name' => 'star'])
        </div>
        <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-[0.15em]">
            Quick Access
        </h3>
    </div>

    {{-- Items --}}
    <div class="space-y-1">
        @forelse ($items as $quickItem)
            <div class="group/qi relative" wire:key="qa-{{ $quickItem['route'] }}" x-data="{ loading: false }">

                <a href="{{ route($quickItem['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-300
                          hover:bg-amber-50/80 hover:scale-[1.02] active:scale-[0.98] pr-8
                          {{ $quickItem['active']
                              ? 'bg-gradient-to-r from-amber-50 to-white border-l-4 border-amber-500 text-amber-900 shadow-md shadow-amber-100/50'
                              : 'text-slate-600' }}">
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all duration-300
                                 {{ $quickItem['active']
                                     ? 'bg-amber-100/80 text-amber-600 shadow-inner'
                                     : 'bg-slate-100/50 text-slate-400 group-hover/qi:bg-amber-100 group-hover/qi:text-amber-600 group-hover/qi:rotate-12' }}">
                        @include('new.layouts.partials.nav-icon', ['name' => $quickItem['icon']])
                    </span>
                    <span class="font-semibold truncate flex-1">{{ $quickItem['label'] }}</span>

                    {{-- Filled star badge for pinned items --}}
                    @if ($quickItem['pinned'])
                        <svg class="h-3 w-3 text-amber-400 shrink-0 group-hover/qi:opacity-0 transition-opacity duration-150"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                    @endif
                </a>

                {{-- Remove button: visible on hover for ALL items --}}
                <button :disabled="loading"
                    @click.prevent="
                            loading = true;
                            @if ($quickItem['pinned']) $dispatch('nav-unpin', { routeName: '{{ $quickItem['route'] }}' });
                            @else
                                $dispatch('nav-remove-visit', { routeName: '{{ $quickItem['route'] }}' }); @endif
                            setTimeout(() => loading = false, 500);
                        "
                    class="absolute right-2 top-1/2 -translate-y-1/2
                               opacity-10 group-hover/qi:opacity-100
                               transition-opacity duration-200 p-1.5 rounded-lg
                               hover:bg-rose-50 text-slate-400 hover:text-rose-500"
                    title="{{ $quickItem['pinned'] ? 'Unpin from Quick Access' : 'Remove from Quick Access' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @empty
            <p class="text-xs text-slate-400 px-3 py-2 italic">No items yet. Visit pages to build your Quick Access.</p>
        @endforelse
    </div>
</div>
