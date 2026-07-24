<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') — {{ config('app.name') }}
        @else
            {{ config('app.name') }}
        @endif
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">


    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">





    <script>
        // Global toast initialization to prevent timing issues with Vite modules
        window.__toastQueue = [];
        window.__toastReady = false;
        window.__toastAdd = function (data) {
            if (Array.isArray(data)) data = data[0];
            if (typeof data === 'string') data = { message: data, type: 'info' };
            if (!data || typeof data !== 'object') return;
            if (window.__toastReady && typeof window.__toastHandler === 'function') {
                window.__toastHandler(data);
            } else {
                window.__toastQueue.push(data);
            }
        };
    </script>

    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    @stack('head')
</head>

@php
    $user = auth()->user();
    $initials = $user
        ? collect(explode(' ', $user->name))
            ->filter()
            ->map(fn($part) => mb_substr($part, 0, 1))
            ->join('')
        : 'U';
    $searchableMenu = App\Services\NavigationService::getSearchableMenu() ?? [];
    $appName = config('app.name');
    $words = preg_split('/\s+/', trim($appName));
    $appAcronym = '';
    foreach ($words as $word) {
        $appAcronym .= mb_substr($word, 0, 1);
    }
@endphp

<body
    class="min-h-screen main-gradient text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-900"
    x-data='{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem("sidebarCollapsed") === "true",
        q: "",
        searchableMenu: @json($searchableMenu),
        init() {
            this.$watch("sidebarCollapsed", val => localStorage.setItem("sidebarCollapsed", val));
        },
        getSearchResultCount() {
            if (!this.q) return 0;
            const query = this.q.toLowerCase();
            return this.searchableMenu.filter(item => 
                item.label.toLowerCase().includes(query) || 
                (item.parent_label && item.parent_label.toLowerCase().includes(query))
            ).length;
        }
    }'
    @keydown.window.cmd.b.prevent="sidebarCollapsed = !sidebarCollapsed"
    @keydown.window.ctrl.b.prevent="sidebarCollapsed = !sidebarCollapsed"
    x-cloak>
    {{-- Top-level Progress Bar for any Livewire transition --}}
    <div wire:loading.delay.shorter class="fixed top-0 left-0 right-0 z-[200] pointer-events-none">
        <div
            class="h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 w-full animate-[progress_1s_ease-in-out_infinite] origin-left">
        </div>
    </div>


    {{-- Mobile sidebar --}}
    <div class="md:hidden" x-show="sidebarOpen" x-transition.opacity>
        <div class="fixed inset-0 z-[70] bg-slate-950/40 backdrop-blur-sm" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-[80] flex w-72 flex-col bg-white/95 backdrop-blur-xl border-r border-slate-200/50 shadow-2xl"
            x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full">
            {{-- Header --}}
            <div class="flex items-center justify-between h-16 px-5 border-b border-slate-100/60">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-lg shadow-blue-200 shrink-0">
                        <img class="h-5 w-5 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <div class="flex flex-col justify-center">
                        <span
                            class="text-[15px] font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700 leading-none">
                            {{ config('app.name') }}
                        </span>
                    </div>
                </a>
                <button type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all duration-200"
                    @click="sidebarOpen = false">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                @include('new.layouts.partials.sidebar-nav', ['isMobile' => true])
            </div>

            <div class="border-t border-slate-100/60 p-4 bg-slate-50/50">
                <div class="flex items-center gap-3 mb-4 px-1">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-200">
                        {{ strtoupper(mb_substr($initials, 0, 2)) }}
                    </div>
                    <div class="flex-1 flex flex-col justify-center min-w-0 pr-2">
                        <p class="text-[13px] font-bold text-slate-800 truncate leading-none pt-[1px]">{{ $user->name ?? 'User' }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 leading-none">{{ $user->email ?? '' }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <a href="{{ route('account.security') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-200/50 transition-colors">
                        <span class="w-5 h-5 flex items-center justify-center">@include('new.layouts.partials.nav-icon', ['name' => 'shield'])</span> Security Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                            <span class="w-5 h-5 flex items-center justify-center">@include('new.layouts.partials.nav-icon', ['name' => 'logout'])</span> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>

    <div class="min-h-screen flex">
        {{-- Desktop sidebar --}}
        <aside
            class="hidden md:flex flex-col border-r border-slate-200/60 bg-white/95 backdrop-blur-sm transition-all duration-500 ease-in-out sticky top-0 h-screen z-50 overflow-hidden"
            :class="sidebarCollapsed ? 'w-[5rem]' : 'w-72'">
            {{-- Header --}}
            <div class="flex items-center h-16 border-b border-slate-100"
                :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-5'">
                <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-lg shadow-blue-100 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shrink-0">
                        <img class="h-6 w-6 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak
                        class="transition-opacity duration-300 flex flex-col justify-center">
                        <span
                            class="text-base font-extrabold tracking-tight text-slate-900 block leading-none">{{ $appName }}</span>
                        <span
                            class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.2em] mt-1 block leading-none">{{ strtoupper($appAcronym) }}
                            SYSTEM</span>
                    </div>
                </a>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                @include('new.layouts.partials.sidebar-nav')
            </div>

            <div class="border-t border-slate-100 p-4">
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                    class="w-full flex items-center justify-center h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-500"
                        :class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.79 4.21a.75.75 0 010 1.06L9.06 9l3.73 3.73a.75.75 0 11-1.06 1.06L7.47 9.53a.75.75 0 010-1.06l4.26-4.26a.75.75 0 011.06 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Desktop Minimal Topbar --}}
            <header class="hidden md:flex h-16 items-center justify-between border-b border-slate-200/60 bg-white/90 backdrop-blur-sm px-6 sticky top-0 z-40 transition-all duration-300">
                {{-- Command Palette Trigger --}}
                <button type="button" @click="$dispatch('open-cmd-k')"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all duration-200 group border border-slate-200/60">
                    <svg class="h-4 w-4 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-500">Quick search commands...</span>
                    <span class="ml-4 rounded-md bg-white px-2 py-0.5 text-xs font-bold text-slate-400 shadow-sm border border-slate-200">Ctrl K</span>
                </button>
                <div class="flex items-center gap-4">
                    @livewire('notifications.bell')

                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button type="button" @click="userMenuOpen = !userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            class="flex items-center gap-3 p-1 rounded-2xl hover:bg-slate-50 transition-all duration-300 group">
                            <div class="text-right pr-1.5 hidden lg:block">
                                <div class="text-[13px] font-bold text-slate-900 leading-tight">{{ $user?->name ?? 'User' }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $user?->email ?? '' }}</div>
                            </div>
                            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-100 group-hover:scale-105 transition-all shrink-0">
                                {{ strtoupper(mb_substr($initials ?? 'U', 0, 2)) }}
                            </div>
                            <svg class="h-4 w-4 text-slate-400 transition-transform duration-300"
                                :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Profile Dropdown --}}
                        <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 top-full mt-2 w-56 rounded-2xl bg-white p-2 premium-shadow ring-1 ring-slate-900/5 focus:outline-none z-[100]"
                            x-cloak>
                            
                            <div class="px-2 py-2 mb-1 border-b border-slate-100 lg:hidden">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Signed in as</p>
                                <p class="text-sm font-bold text-slate-900 truncate mt-0.5">{{ $user?->name ?? 'User' }}</p>
                            </div>
                            
                            <a href="{{ route('signatures.manage') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium">
                                <span class="w-4 h-4 text-slate-400 flex items-center justify-center">@include('new.layouts.partials.nav-icon', ['name' => 'document-text'])</span> Manage Signature
                            </a>
                            <a href="{{ route('account.security') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium">
                                <span class="w-4 h-4 text-slate-400 flex items-center justify-center">@include('new.layouts.partials.nav-icon', ['name' => 'shield'])</span> Security Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                    <span class="w-4 h-4 text-rose-500 flex items-center justify-center">@include('new.layouts.partials.nav-icon', ['name' => 'logout'])</span> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Mobile Minimal Header --}}
            <header class="md:hidden h-16 flex items-center justify-between border-b border-slate-200/60 bg-white/90 backdrop-blur-sm px-4 sticky top-0 z-40">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-sm shrink-0">
                        <img class="h-5 w-5 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <span class="text-[15px] font-extrabold text-slate-900 leading-none tracking-tight">{{ config('app.name') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @livewire('notifications.bell')
                    <button type="button" @click="sidebarOpen = true"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </header>

            {{-- Main content --}}
            <main class="flex-1 overflow-y-auto px-6 py-8 md:px-10 md:py-10 scroll-smooth custom-scrollbar">
                <div x-data="{ loaded: false }" x-init="$nextTick(() => { setTimeout(() => loaded = true, 50) })"
                    :class="loaded ? 'opacity-100' : 'opacity-0 translate-y-4'"
                    class="transition-all duration-700 cubic-bezier(0.4, 0, 0.2, 1)">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts
    @stack('modals')

    <livewire:navigation.command-palette />
    <livewire:ticketing.support-bubble />

    {{-- ============================================================ --}}
    {{-- TOAST NOTIFICATION SYSTEM                                    --}}
    {{-- Handles: Livewire PHP dispatch(), Alpine $dispatch(), JS API --}}
    {{-- ============================================================ --}}
    <script>
        // Global toast queue — collects toasts before Alpine is ready
        window.__toastQueue = [];
        window.__lastToast = { message: '', time: 0 };
        window.__toastAdd = function(data) {
            // Normalize payload: Livewire 3 PHP dispatch() wraps as [{...}], $dispatch sends {...}
            if (Array.isArray(data)) data = data[0];
            if (!data || typeof data !== 'object') return;
            
            const now = Date.now();
            const msg = data.message || data.body || '';
            if (window.__lastToast.message === msg && (now - window.__lastToast.time) < 100) {
                return;
            }
            window.__lastToast = { message: msg, time: now };

            if (window.__toastReady) {
                window.__toastHandler(data);
            } else {
                window.__toastQueue.push(data);
            }
        };

        // Register Livewire.on as soon as Livewire is available (may be before alpine:init)
        document.addEventListener('livewire:init', function() {
            Livewire.on('toast', function(params) {
                window.__toastAdd(params);
            });
            Livewire.on('flash', function(params) {
                window.__toastAdd(params);
            });
        });

        // Also capture window-level toast events (from Alpine $dispatch or manual JS)
        window.addEventListener('toast', function(e) {
            window.__toastAdd(e.detail);
        });

        // Register the Alpine component
        document.addEventListener('alpine:init', function() {
            Alpine.data('toastManager', function() {
                return {
                    toasts: [],
                    nextId: 1,

                    init() {
                        // Mark as ready and flush queue
                        window.__toastHandler = (data) => this.addToast(data);
                        window.__toastReady = true;
                        window.__toastQueue.forEach(d => this.addToast(d));
                        window.__toastQueue = [];

                        // Flash Session Bridge
                        @if (session()->has('success'))
                            this.addToast({ type: 'success', message: @json(session('success')) });
                        @endif
                        @if (session()->has('error'))
                            this.addToast({ type: 'error', message: @json(session('error')) });
                        @endif
                        @if (session()->has('warning'))
                            this.addToast({ type: 'warning', message: @json(session('warning')) });
                        @endif
                        @if (session()->has('info'))
                            this.addToast({ type: 'info', message: @json(session('info')) });
                        @endif
                    },

                    addToast(data) {
                        const id = this.nextId++;
                        const duration = data.duration || 5000;
                        const toast = {
                            id,
                            type: data.type || 'info',
                            message: data.message || data.body || '',
                            visible: false,
                            progress: 100
                        };
                        this.toasts.push(toast);
                        
                        // Use setTimeout to guarantee browser render cycle completes
                        setTimeout(() => {
                            // Fetch the reactive proxy from the array, do not mutate the raw object!
                            const reactiveToast = this.toasts.find(t => t.id === id);
                            if (reactiveToast) {
                                reactiveToast.visible = true;
                                
                                const steps = duration / 100;
                                let step = 0;
                                reactiveToast._timer = setInterval(() => {
                                    step++;
                                    reactiveToast.progress = 100 - (step / steps * 100);
                                    if (step >= steps) {
                                        clearInterval(reactiveToast._timer);
                                        this.removeToast(id);
                                    }
                                }, 100);
                            }
                        }, 50);
                    },

                    removeToast(id) {
                        const t = this.toasts.find(t => t.id === id);
                        if (!t) return;
                        if (t._timer) clearInterval(t._timer);
                        t.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 400);
                    },

                    icon(type) {
                        return {
                            success: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`,
                            error: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`,
                            warning: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
                            info: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                        } [type] ||
                        `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                    }
                };
            });

            Alpine.data('navItem', (isMobile, idx) => ({
                hover: false,
                flyoutTop: 0,
                myIdx: idx,
                pinned: false,
                pinLoading: false,
                flyoutTimer: null,
                
                handleMouseEnter() {
                    if (isMobile) return;
                    clearTimeout(this.flyoutTimer);
                    this.flyoutTimer = setTimeout(() => {
                        this.hover = true;
                        this.flyoutTop = this.$el.getBoundingClientRect().top;
                        this.$dispatch('sbflyout', { idx: this.myIdx });
                        this.$nextTick(() => {
                            const el = document.getElementById('flyout-' + this.myIdx);
                            if (el) {
                                const rect = el.getBoundingClientRect();
                                if (rect.bottom > window.innerHeight - 20) {
                                    this.flyoutTop = Math.max(20, window.innerHeight - rect.height - 20);
                                }
                            }
                        });
                    }, 125);
                },

                handleMouseLeave() {
                    if (isMobile) return;
                    clearTimeout(this.flyoutTimer);
                    this.hover = false;
                },

                handleFlyoutWindow(e) {
                    if (e.detail.idx !== this.myIdx) {
                        clearTimeout(this.flyoutTimer);
                        this.hover = false;
                    }
                },
                
                togglePin(routeName) {
                    this.pinLoading = true;
                    if (this.pinned) {
                        this.$dispatch('nav-unpin', { routeName: routeName });
                    } else {
                        this.$dispatch('nav-pin', { routeName: routeName });
                    }
                    setTimeout(() => this.pinLoading = false, 600);
                },
                
                handlePinStateChangedWindow(e, routeName) {
                    if (e.detail.routeName === routeName) this.pinned = e.detail.pinned;
                }
            }));

            Alpine.data('navGroup', (isMobile, idx, defaultOpen) => ({
                hover: false,
                open: defaultOpen,
                flyoutOpen: false,
                flyoutTop: 0,
                flyoutTimer: null,
                myIdx: idx,

                init() {
                    if (this.open) {
                        this.$nextTick(() => this.$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
                    }
                },

                handleMouseEnter() {
                    if (isMobile) return;
                    clearTimeout(this.flyoutTimer);
                    this.flyoutTimer = setTimeout(() => {
                        this.hover = true;
                        this.flyoutOpen = true;
                        this.flyoutTop = this.$el.getBoundingClientRect().top;
                        this.$dispatch('sbflyout', { idx: this.myIdx });
                        this.$nextTick(() => {
                            const el = document.getElementById('flyout-' + this.myIdx);
                            if (el) {
                                const rect = el.getBoundingClientRect();
                                if (rect.bottom > window.innerHeight - 20) {
                                    this.flyoutTop = Math.max(20, window.innerHeight - rect.height - 20);
                                }
                            }
                        });
                    }, 125);
                },

                handleMouseLeave() {
                    if (isMobile) return;
                    clearTimeout(this.flyoutTimer);
                    this.flyoutTimer = setTimeout(() => { 
                        this.flyoutOpen = false; 
                        this.hover = false; 
                    }, 150);
                },

                handleFlyoutWindow(e) {
                    if (e.detail.idx !== this.myIdx) {
                        clearTimeout(this.flyoutTimer);
                        this.flyoutOpen = false;
                        this.hover = false;
                    }
                }
            }));

            Alpine.data('navChild', (routeName) => ({
                pinned: false, 
                pinLoading: false,
                routeName: routeName,
                
                togglePin() {
                    this.pinLoading = true;
                    if (this.pinned) {
                        this.$dispatch('nav-unpin', { routeName: this.routeName });
                    } else {
                        this.$dispatch('nav-pin', { routeName: this.routeName });
                    }
                    setTimeout(() => this.pinLoading = false, 600);
                },
                
                handlePinStateChangedWindow(e) {
                    if (e.detail.routeName === this.routeName) this.pinned = e.detail.pinned;
                }
            }));
        });
    </script>

    <div x-data="toastManager()" class="fixed bottom-6 right-6 z-[9999] space-y-3 max-w-sm pointer-events-none"
        x-cloak>
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transform transition ease-out duration-500"
                x-transition:enter-start="translate-x-full opacity-0 scale-90"
                x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                x-transition:leave-end="translate-x-full opacity-0 scale-90"
                class="pointer-events-auto flex items-start gap-4 rounded-2xl px-5 py-4 shadow-2xl backdrop-blur-xl border min-w-[320px] max-w-sm overflow-hidden relative"
                :class="{
                    'bg-emerald-600/90 text-white border-emerald-400/30': toast.type === 'success',
                    'bg-rose-600/90 text-white border-rose-400/30': toast.type === 'error',
                    'bg-amber-500/90 text-white border-amber-400/30': toast.type === 'warning',
                    'bg-blue-600/90 text-white border-blue-400/30': toast.type === 'info'
                }">
                {{-- Progress Bar --}}
                <div class="absolute top-0 left-0 h-1 bg-white/20 w-full">
                    <div class="h-full bg-white/40 transition-all ease-linear"
                        :style="`width: ${toast.progress}%; transition-duration: 100ms`"></div>
                </div>

                <div class="flex-shrink-0 mt-1" x-html="icon(toast.type)"></div>

                <div class="flex-1 min-w-0 py-0.5">
                    <p class="text-sm font-bold leading-tight tracking-tight" x-text="toast.message"></p>
                </div>

                <button @click="removeToast(toast.id)"
                    class="flex-shrink-0 opacity-70 hover:opacity-100 transition-all hover:scale-110 mt-0.5">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
        </template>
    </div>


    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @stack('scripts')
</body>

</html>
