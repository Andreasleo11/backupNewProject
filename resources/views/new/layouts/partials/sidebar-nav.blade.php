{{-- Sidebar search (hidden when collapsed on desktop) --}}
<div class="px-3 pt-3 pb-2 border-b border-slate-100" x-show="!sidebarCollapsed">
    <div class="relative group">
        <input type="text" x-model="q" placeholder="Search menu..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 pl-10
                   text-sm text-slate-700 shadow-sm outline-none
                   focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20
                   transition-all duration-200">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors duration-200"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <button x-show="q.length > 0" @click="q = ''"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div x-show="q.length > 0" class="mt-2 text-xs text-slate-500 transition-opacity duration-200">
        <span x-text="getSearchResultCount()"></span> items found
    </div>
</div>

@php
    use App\Services\NavigationService;
    $nav = NavigationService::getPersonalizedMenu();
@endphp

<nav class="flex-1 overflow-y-auto px-2 py-3 text-sm" role="navigation" aria-label="Main navigation">
    <ul class="space-y-2" role="menubar">
        @foreach ($nav as $item)
            @if ($item['type'] === 'quick-access')
                {{-- Quick Access Section --}}
                <div class="px-3 py-3" x-show="!sidebarCollapsed">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600">
                            @include('new.layouts.partials.nav-icon', ['name' => $item['icon'] ?? 'star'])
                        </div>
                        <h3 class="text-xs font-bold text-amber-700 uppercase tracking-wider">
                            {{ $item['label'] }}
                        </h3>
                    </div>
                    <div class="space-y-1">
                        @foreach ($item['items'] ?? [] as $quickItem)
                            <a href="{{ route($quickItem['route']) }}"
                                x-show="q === '' || '{{ strtolower($quickItem['label']) }}'.includes(q.toLowerCase())"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all duration-200
                                       hover:bg-amber-50 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                                       {{ $quickItem['active'] ? 'bg-amber-50 border-l-4 border-amber-500 text-amber-700 shadow-sm' : 'text-slate-600' }}">
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-md transition-all duration-200
                                         {{ $quickItem['active'] ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500 group-hover:bg-amber-50 group-hover:text-amber-600' }}">
                                    @include('new.layouts.partials.nav-icon', ['name' => $quickItem['icon']])
                                </span>
                                <span class="font-medium truncate">{{ $quickItem['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif ($item['type'] === 'divider')
                {{-- Section Divider --}}
                <div class="px-3 py-3" x-show="!sidebarCollapsed">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-t border-slate-200 pt-3">
                        {{ $item['label'] }}
                    </h3>
                </div>
            @elseif ($item['type'] === 'single')
                @php
                    $label = $item['label'];
                    $isActive = $item['active'] ?? false;
                @endphp
                <li class="relative text-slate-600" x-data="{ hover: false, flyoutTop: 0, label: '{{ strtolower($label) }}' }"
                    @mouseenter="hover = true; flyoutTop = $el.getBoundingClientRect().top" @mouseleave="hover = false"
                    x-show="q === '' || label.includes(q.toLowerCase())" role="none">
                    <a href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-all duration-200
                               hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                               {{ $isActive ? 'bg-blue-50 border-l-4 border-blue-500 text-blue-700 shadow-sm' : 'text-slate-600' }}"
                        :class="{
                            'justify-center': sidebarCollapsed,
                            'justify-start': !sidebarCollapsed,
                        }"
                        role="menuitem"
                        tabindex="0"
                        :aria-current="$isActive ? 'page' : false"
                        @keydown.enter="window.location.href = '{{ route($item['route']) }}'"
                        @keydown.space.prevent="window.location.href = '{{ route($item['route']) }}'">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200
                                 {{ $isActive ? 'bg-blue-100 text-blue-600 shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }}">
                            @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                        </span>
                        <span class="font-medium truncate text-sm" x-show="!sidebarCollapsed">
                            {{ $item['label'] }}
                        </span>
                        @if(isset($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full min-w-[20px] text-center font-medium shadow-sm">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>

                    {{-- Teleported flyout when collapsed --}}
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && hover" x-transition x-cloak
                            class="fixed z-50 ml-2 rounded-xl bg-white border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-xl ring-1 ring-black/5"
                            :style="{
                                top: (flyoutTop + 8) + 'px', // 8px offset for nicer alignment
                                left: '5rem', // collapsed sidebar width (md:w-20 = 5rem)
                            }">
                            <div class="font-medium text-slate-800">{{ $item['label'] }}</div>
                            @if(isset($item['description']))
                                <div class="text-xs text-slate-500 mt-1">{{ $item['description'] }}</div>
                            @endif
                        </div>
                    </template>
                </li>
            @elseif ($item['type'] === 'group')
                @php
                    $groupLabel = $item['label'];
                    $children = $item['children'] ?? [];
                    $anyActive = collect($children)->contains(fn($c) => $c['active'] ?? false);
                @endphp
                @php
                    $defaultOpen = $item['defaultOpen'] ?? $anyActive;
                @endphp
                <li class="relative text-slate-600"
                    x-data="{
                        hover: false,
                        open: {{ $defaultOpen ? 'true' : 'false' }},
                        flyoutOpen: false,
                        flyoutTop: 0,
                        flyoutTimer: null,
                        label: '{{ strtolower($groupLabel) }}',
                        hasMatchingChildren() {
                            if (!q) return false;
                            const query = q.toLowerCase();
                            return {{ json_encode(collect($children)->pluck('label')->map('strtolower')->toArray()) }}.some(childLabel => childLabel.includes(query));
                        }
                    }"
                    x-effect="if (q && hasMatchingChildren()) open = true"
                    @mouseenter="
                        clearTimeout(flyoutTimer);
                        hover = true;
                        flyoutOpen = true;
                        flyoutTop = $el.getBoundingClientRect().top;
                    "
                    @mouseleave="
                        hover = false;
                        flyoutTimer = setTimeout(() => { flyoutOpen = false }, 120);
                    "
                    x-show="q === '' || label.includes(q.toLowerCase()) || hasMatchingChildren()"
                    role="none"
                >
                    {{-- Group header --}}
                    <button type="button" @click="open = !open"
                        class="group flex w-full items-center rounded-lg px-3 py-2.5 text-sm font-semibold
                               uppercase tracking-wide transition-all duration-200
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                               {{ $anyActive ? 'text-blue-700' : 'text-slate-500 hover:text-slate-700' }}"
                        :class="{
                            'justify-between': !sidebarCollapsed,
                            'justify-center': sidebarCollapsed,
                        }"
                        role="menuitem"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        :aria-controls="'group-{{ $loop->index }}'"
                        tabindex="0"
                        @keydown.enter="open = !open"
                        @keydown.space.prevent="open = !open"
                        @keydown.arrow-right="if (!sidebarCollapsed) open = true"
                        @keydown.arrow-left="if (!sidebarCollapsed) open = false">
                        <span class="flex items-center gap-3 flex-1">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-lg
                                     bg-gradient-to-br from-slate-100 to-slate-200
                                     text-slate-500 transition-all duration-200
                                     group-hover:from-blue-50 group-hover:to-blue-100
                                     group-hover:text-blue-600">
                                @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                            </span>
                            <span x-show="!sidebarCollapsed" class="font-medium">{{ $groupLabel }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            :class="open ? 'rotate-90 text-blue-600' : 'text-slate-400'"
                            class="h-4 w-4 transition-transform duration-200"
                            viewBox="0 0 20 20" fill="currentColor"
                            x-show="!sidebarCollapsed"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 01.02-1.06L11 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    {{-- Children (visible only when not collapsed) --}}
                    <ul x-show="open && !sidebarCollapsed"
                        x-transition:enter="transition-all duration-300 ease-out"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-96"
                        x-transition:leave="transition-all duration-200 ease-in"
                        x-transition:leave-start="opacity-100 max-h-96"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-1 pl-4 border-l-2 border-slate-100 overflow-hidden"
                        :id="'group-{{ $loop->index }}'" role="menu" aria-hidden="!open">
                        @foreach ($children as $child)
                            @php
                                $childLabel = $child['label'];
                                $childActive = $child['active'] ?? false;
                            @endphp
                            <li x-data="{ label: '{{ strtolower($childLabel) }}' }" x-show="q === '' || label.includes(q.toLowerCase())" role="none">
                                <a href="{{ route($child['route']) }}"
                                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all duration-200
                                          hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                          {{ $childActive ? 'bg-blue-50 border-l-4 border-blue-500 text-blue-700 shadow-sm' : 'text-slate-600' }}"
                                    role="menuitem"
                                    tabindex="0"
                                    :aria-current="$childActive ? 'page' : false"
                                    @keydown.enter="window.location.href = '{{ route($child['route']) }}'"
                                    @keydown.space.prevent="window.location.href = '{{ route($child['route']) }}'">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-md transition-all duration-200
                                             {{ $childActive ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }}">
                                        @include('new.layouts.partials.nav-icon', ['name' => $child['icon']])
                                    </span>
                                    <span class="font-medium truncate">{{ $child['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Teleported flyout with children when collapsed --}}
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && flyoutOpen"
                            x-transition
                            x-cloak
                            class="fixed z-50 w-64 rounded-xl bg-white border border-slate-200 px-4 py-3 text-sm shadow-xl ring-1 ring-black/5"
                            :style="{
                                top: (flyoutTop + 4) + 'px',
                                left: '5rem',
                            }"
                            @mouseenter="
                                clearTimeout(flyoutTimer);
                                flyoutOpen = true;
                            "
                            @mouseleave="
                                flyoutTimer = setTimeout(() => { flyoutOpen = false }, 120);
                            ">
                            <div class="mb-3 pb-2 border-b border-slate-100">
                                <div class="font-semibold text-slate-800 uppercase tracking-wide text-xs">
                                    {{ $groupLabel }}
                                </div>
                            </div>
                            <ul class="space-y-1">
                                @foreach ($children as $child)
                                    @php
                                        $childActive = $child['active'] ?? false;
                                    @endphp
                                    <li>
                                        <a href="{{ route($child['route']) }}"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 group
                                                   {{ $childActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">
                                            <span
                                                class="flex h-6 w-6 items-center justify-center rounded-md transition-all duration-200
                                                     {{ $childActive ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }}">
                                                @include('new.layouts.partials.nav-icon', [
                                                    'name' => $child['icon'],
                                                ])
                                            </span>
                                            <span class="font-medium truncate">{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </template>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
