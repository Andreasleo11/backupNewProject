@php
    $isMobile = $isMobile ?? false;
@endphp

{{-- Sidebar Search Section --}}
<div class="px-4 pt-3 pb-1" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
    <div class="relative group">
        <input type="text" x-model="q" placeholder="Explore menu..."
            class="w-full rounded-xl border border-slate-200/60 bg-white/50 backdrop-blur-md py-2.5 pl-10
                   text-sm text-slate-700 shadow-sm outline-none ring-offset-2
                   focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20
                   placeholder:text-slate-400 transition-all duration-300">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors duration-300"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <button x-show="q.length > 0" @click="q = ''"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors duration-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div x-show="q.length > 0" class="mt-2 text-[10px] font-semibold text-slate-400 uppercase tracking-tighter px-1 flex items-center justify-between">
        <span>Matches found</span>
        <span x-text="getSearchResultCount()" class="text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"></span>
    </div>
</div>

@php
    use App\Services\NavigationService;
    $nav = NavigationService::getPersonalizedMenu();
@endphp

<nav class="flex-1 overflow-y-auto custom-scrollbar px-2 py-2" role="navigation" aria-label="Main system navigation">
    {{-- High-Priority Search Results (Visible only when searching) --}}
    <div x-show="q.length > 0" class="mb-6 space-y-1.5 px-2">
        @php
            $flattenedItems = [];
            foreach ($nav as $item) {
                if ($item['type'] === 'single') $flattenedItems[] = $item;
                if ($item['type'] === 'group') {
                    foreach ($item['children'] ?? [] as $child) {
                        $child['parent_label'] = $item['label'];
                        $flattenedItems[] = $child;
                    }
                }
            }
        @endphp
        @foreach ($flattenedItems as $flatItem)
            <a href="{{ route($flatItem['route']) }}"
               x-show="'{{ strtolower($flatItem['label']) }}'.includes(q.toLowerCase()) || '{{ strtolower($flatItem['parent_label'] ?? '') }}'.includes(q.toLowerCase())"
               class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition-all duration-300 group
                      hover:bg-blue-50/50 hover:translate-x-1
                      {{ $flatItem['active'] ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100/80 text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600">
                    @include('new.layouts.partials.nav-icon', ['name' => $flatItem['icon']])
                </span>
                <div class="flex flex-col min-w-0">
                    <span class="font-bold truncate text-xs" x-html="'{{ $flatItem['label'] }}'.replace(new RegExp(q, 'gi'), match => `<mark class='bg-blue-200/50 text-blue-900 rounded-sm px-0.5'>${match}</mark>`)"></span>
                    @if(isset($flatItem['parent_label']))
                        <span class="text-[9px] text-slate-400 uppercase tracking-widest">{{ $flatItem['parent_label'] }}</span>
                    @endif
                </div>
            </a>
        @endforeach
        <div class="h-[1px] w-full bg-slate-100 my-4"></div>
    </div>

    <ul class="space-y-1.5" role="menubar" x-show="q.length === 0">
        @foreach ($nav as $item)
            @if ($item['type'] === 'quick-access')
                {{-- Premium Quick Access Section --}}
                <div class="px-3 py-4 mt-2 mb-1" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
                    <div class="flex items-center gap-2.5 mb-3.5">
                        <div class="flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm shadow-amber-200">
                            @include('new.layouts.partials.nav-icon', ['name' => $item['icon'] ?? 'star'])
                        </div>
                        <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-[0.15em]">
                            {{ $item['label'] }}
                        </h3>
                    </div>
                    <div class="space-y-1">
                        @foreach ($item['items'] ?? [] as $quickItem)
                            <a href="{{ route($quickItem['route']) }}"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-300 group
                                       hover:bg-amber-50/80 hover:scale-[1.02] active:scale-[0.98]
                                       {{ $quickItem['active'] 
                                          ? 'bg-gradient-to-r from-amber-50 to-white border-l-4 border-amber-500 text-amber-900 shadow-md shadow-amber-100/50 block' 
                                          : 'text-slate-600 block' }}">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-lg transition-all duration-300
                                             {{ $quickItem['active'] 
                                                ? 'bg-amber-100/80 text-amber-600 shadow-inner' 
                                                : 'bg-slate-100/50 text-slate-400 group-hover:bg-amber-100 group-hover:text-amber-600 group-hover:rotate-12' }}">
                                        @include('new.layouts.partials.nav-icon', ['name' => $quickItem['icon']])
                                    </span>
                                    <span class="font-semibold truncate">{{ $quickItem['label'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif ($item['type'] === 'divider')
                {{-- Elegant Section Divider - Only show if there are visible items after it --}}
                @php
                    $currentIndex = $loop->index;
                    $hasItemsAfter = false;
                    // Check if there are any visible items after this divider (until next divider or end)
                    for ($i = $currentIndex + 1; $i < count($nav); $i++) {
                        $nextItem = $nav[$i];
                        // Stop at next divider
                        if ($nextItem['type'] === 'divider') break;
                        // If we find any non-divider item, there's content after this divider
                        if ($nextItem['type'] !== 'divider') {
                            $hasItemsAfter = true;
                            break;
                        }
                    }
                @endphp
                @if($hasItemsAfter)
                    <div class="px-4 py-4" x-show="(!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}) && q.length === 0">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] whitespace-nowrap">
                                {{ $item['label'] }}
                            </span>
                            <div class="h-[1px] w-full bg-gradient-to-r from-slate-200 to-transparent"></div>
                        </div>
                    </div>
                @endif
            @elseif ($item['type'] === 'single')
                @php
                    $label = $item['label'];
                    $isActive = $item['active'] ?? false;
                @endphp
                <li class="relative group/nav-item"
                    x-data="{ hover: false, flyoutTop: 0, myIdx: 'si-{{ $loop->index }}' }"
                    @mouseenter="hover = true; flyoutTop = $el.getBoundingClientRect().top; $dispatch('sbflyout', { idx: myIdx })"
                    @mouseleave="hover = false"
                    x-on:sbflyout.window="if ($event.detail.idx !== myIdx) hover = false"
                    role="none">
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-300
                               hover:bg-blue-50/50 hover:text-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-500/20 active:scale-[0.98]
                               {{ $isActive 
                                  ? 'bg-white shadow-lg shadow-blue-100/50 border border-blue-100 text-blue-700 font-semibold mb-0.5' 
                                  : 'text-slate-600 font-medium' }}"
                        :class="{
                            'justify-center px-0 mx-2': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }},
                            'justify-start': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }},
                        }"
                        role="menuitem"
                        tabindex="0"
                        aria-current="{{ $isActive ? 'page' : 'false' }}">
                        
                        <div class="relative flex items-center justify-center shrink-0">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-300
                                     {{ $isActive 
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-200' 
                                        : 'bg-slate-100/80 text-slate-500 group-hover/nav-item:bg-blue-100 group-hover/nav-item:text-blue-600' }}">
                                @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                            </span>
                            @if($isActive)
                                <span class="absolute -right-0.5 -top-0.5 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                                </span>
                            @endif
                        </div>

                        <span class="truncate text-sm tracking-tight" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
                            {{ $item['label'] }}
                        </span>

                        @if(isset($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-lg min-w-[18px] text-center font-bold shadow-sm shadow-rose-200"
                                  x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>

                    {{-- Glassmorphic Collapsed Flyout --}}
                    @if(!$isMobile)
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && hover" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-x-[-10px]"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-cloak
                             class="fixed z-[90] ml-3 rounded-2xl bg-white/90 backdrop-blur-xl border border-blue-100/50 px-5 py-4 shadow-2xl shadow-blue-900/10 min-w-[200px]"
                             :style="{
                                 top: (flyoutTop) + 'px',
                                 left: '5rem',
                             }">
                             <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                                    @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                                </div>
                                <div class="font-bold text-slate-900 text-base">{{ $item['label'] }}</div>
                             </div>
                            @if(isset($item['description']))
                                <p class="text-[11px] leading-relaxed text-slate-500 font-medium max-w-[180px]">{{ $item['description'] }}</p>
                            @endif
                        </div>
                    </template>
                    @endif
                </li>
            @elseif ($item['type'] === 'group')
                @php
                    $groupLabel = $item['label'];
                    $children = $item['children'] ?? [];
                    $anyActive = collect($children)->contains(fn($c) => $c['active'] ?? false);
                    $defaultOpen = $item['defaultOpen'] ?? $anyActive;
                @endphp
                <li class="relative overflow-hidden"
                    x-data="{
                        hover: false,
                        open: {{ $defaultOpen ? 'true' : 'false' }},
                        flyoutOpen: false,
                        flyoutTop: 0,
                        flyoutTimer: null,
                        myIdx: 'gr-{{ $loop->index }}'
                    }"
                    :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'flex flex-col items-center' : ''"
                    @mouseenter="
                        if (!{{ $isMobile ? 'true' : 'false' }}) {
                            clearTimeout(flyoutTimer);
                            hover = true;
                            flyoutOpen = true;
                            flyoutTop = $el.getBoundingClientRect().top;
                            $dispatch('sbflyout', { idx: myIdx });
                        }
                    "
                    @mouseleave="
                        if (!{{ $isMobile ? 'true' : 'false' }}) {
                            hover = false;
                            flyoutTimer = setTimeout(() => { flyoutOpen = false }, 150);
                        }
                    "
                    x-on:sbflyout.window="if ($event.detail.idx !== myIdx) { clearTimeout(flyoutTimer); flyoutOpen = false; hover = false; }"
                    role="none"
                >
                    {{-- Group Header --}}
                    <button type="button" @click="open = !open"
                        class="group flex w-full items-center rounded-xl px-3 py-2.5 text-sm font-semibold
                               transition-all duration-300 active:scale-[0.98]
                               focus:outline-none focus:ring-2 focus:ring-blue-500/20
                               {{ $anyActive ? 'text-blue-950 bg-blue-50/30' : 'text-slate-600 hover:bg-slate-50' }}"
                        :class="{
                            'justify-between': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }},
                            'justify-center px-0': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }},
                        }"
                        role="menuitem"
                        :aria-expanded="open">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-300 shrink-0
                                     {{ $anyActive 
                                        ? 'bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-md shadow-blue-150' 
                                        : 'bg-slate-100/80 text-slate-500 group-hover:bg-white group-hover:text-blue-600 group-hover:shadow-sm' }}">
                                @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                            </span>
                            <span x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}" class="font-bold tracking-tight text-sm">{{ $groupLabel }}</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            :class="open ? 'rotate-90 text-blue-600' : 'text-slate-300'"
                            class="h-4 w-4 transition-transform duration-300"
                            viewBox="0 0 20 20" fill="currentColor"
                            x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 01.02-1.06L11 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    {{-- Submenu --}}
                    <ul x-show="open && (!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }})"
                        x-transition:enter="transition-all duration-300 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                        x-transition:enter-end="opacity-100 translate-y-0 max-h-[800px]"
                        x-transition:leave="transition-all duration-200 ease-in"
                        x-transition:leave-start="opacity-100 translate-y-0 max-h-[800px]"
                        x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
                        class="mt-1 space-y-1 ml-[1.875rem] pl-4 border-l border-slate-200/60 overflow-hidden"
                        role="menu">
                        @foreach ($children as $child)
                            @php
                                $childLabel = $child['label'];
                                $childActive = $child['active'] ?? false;
                            @endphp
                            <li role="none">
                                <a href="{{ route($child['route']) }}"
                                    class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition-all duration-300
                                          hover:bg-blue-50/50 hover:translate-x-1
                                          {{ $childActive ? 'text-blue-700 font-bold' : 'text-slate-500 font-medium hover:text-slate-900' }}"
                                    role="menuitem">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300
                                               {{ $childActive ? 'bg-blue-500 scale-125 shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'bg-slate-300 group-hover:bg-blue-400' }}"></div>
                                    <span class="truncate tracking-tight">{{ $child['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Glassmorphic Collapsed Group Flyout --}}
                    @if(!$isMobile)
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && flyoutOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-x-[-10px]"
                             x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                             x-cloak
                             class="fixed z-[90] w-64 rounded-2xl bg-white/95 backdrop-blur-xl border border-blue-100/50 p-2 shadow-2xl shadow-blue-900/15"
                             :style="{
                                 top: (flyoutTop) + 'px',
                                 left: '5.5rem',
                             }"
                             @mouseenter="clearTimeout(flyoutTimer); flyoutOpen = true"
                             @mouseleave="flyoutTimer = setTimeout(() => { flyoutOpen = false }, 150)">
                            
                            <div class="px-3 py-3 border-b border-slate-100/80 mb-1">
                                <div class="font-black text-blue-950 uppercase tracking-widest text-[10px] flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                    {{ $groupLabel }}
                                </div>
                            </div>
                            
                            <ul class="p-1 space-y-1">
                                @foreach ($children as $child)
                                    @php
                                        $childActive = $child['active'] ?? false;
                                    @endphp
                                    <li>
                                        <a href="{{ route($child['route']) }}"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-300 
                                                   {{ $childActive 
                                                      ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' 
                                                      : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-lg transition-all duration-300
                                                        {{ $childActive ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }}">
                                                @include('new.layouts.partials.nav-icon', ['name' => $child['icon']])
                                            </span>
                                            <span class="font-bold text-xs truncate">{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </template>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</nav>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(203, 213, 225, 0.4);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.6);
}
mark {
    background: transparent;
    color: inherit;
}
</style>
