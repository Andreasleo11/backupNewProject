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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Boxicons CDN --}}
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">



    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-radius: 1rem;
        }
        .main-gradient {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent),
                        radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.05), transparent),
                        #f8fafc;
        }
        .premium-shadow {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 0.5);
            border-radius: 20px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.8);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    @stack('head')
</head>

<body class="min-h-screen main-gradient text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-900" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: $persist(false),
    q: '',
    getSearchResultCount() {
        if (!this.q) return 0;
        const query = this.q.toLowerCase();
        let count = 0;
        @php
            $navItems = App\Services\NavigationService::getPersonalizedMenu();
            foreach ($navItems as $item) {
                if ($item['type'] === 'single') {
                    $label = strtolower($item['label']);
                    echo "if ('{$label}'.includes(query)) count++;\n";
                } elseif ($item['type'] === 'group') {
                    $groupLabel = strtolower($item['label']);
                    echo "if ('{$groupLabel}'.includes(query)) count++;\n";
                    foreach ($item['children'] ?? [] as $child) {
                        $childLabel = strtolower($child['label']);
                        echo "if ('{$childLabel}'.includes(query)) count++;\n";
                    }
                }
            }
        @endphp
        return count;
    }
}" x-cloak>
    {{-- Mobile sidebar --}}
    <div class="md:hidden" x-show="sidebarOpen" x-transition.opacity>
        <div class="fixed inset-0 z-[70] bg-slate-950/40 backdrop-blur-sm" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-[80] flex w-72 flex-col bg-white/95 backdrop-blur-xl border-r border-slate-200/50 shadow-2xl"
               x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300 transform"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200 transform"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full">
            {{-- Header --}}
            <div class="flex items-center justify-between h-16 px-5 border-b border-slate-100/60">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-lg shadow-blue-200 shrink-0">
                        <img class="h-5 w-5 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="text-[15px] font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700 leading-none">
                            {{ config('app.name') }}
                        </span>
                    </div>
                </a>
                <button type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all duration-200"
                    @click="sidebarOpen = false">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                @include('new.layouts.partials.sidebar-nav', ['isMobile' => true])
            </div>

            <div class="border-t border-slate-100/60 p-5 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-200">
                        {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="flex-1 flex flex-col justify-center min-w-0 pr-2">
                        <p class="text-[13px] font-bold text-slate-800 truncate leading-none pt-[1px]">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 leading-none">Authenticated</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="min-h-screen flex">
        {{-- Desktop sidebar --}}
        <aside class="hidden md:flex flex-col border-r border-slate-200/60 bg-white/80 backdrop-blur-xl transition-all duration-500 ease-in-out sticky top-0 h-screen z-50 overflow-hidden"
            :class="sidebarCollapsed ? 'w-[5rem]' : 'w-72'">
            {{-- Header --}}
            <div class="flex items-center h-16 border-b border-slate-100"
                 :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-5'">
                @php
                    $appName = config('app.name');
                    $words = preg_split('/\s+/', trim($appName));
                    $appAcronym = '';
                    foreach ($words as $word) { $appAcronym .= mb_substr($word, 0, 1); }
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-lg shadow-blue-100 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 shrink-0">
                        <img class="h-6 w-6 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="transition-opacity duration-300 flex flex-col justify-center">
                        <span class="text-base font-extrabold tracking-tight text-slate-900 block leading-none">{{ $appName }}</span>
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.2em] mt-1 block leading-none">{{ strtoupper($appAcronym) }} SYSTEM</span>
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
                        <path fill-rule="evenodd" d="M12.79 4.21a.75.75 0 010 1.06L9.06 9l3.73 3.73a.75.75 0 11-1.06 1.06L7.47 9.53a.75.75 0 010-1.06l4.26-4.26a.75.75 0 011.06 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="h-16 flex items-center justify-between border-b border-slate-200/60 bg-white/70 backdrop-blur-md px-6 sticky top-0 z-40 transition-all duration-300">
                <div class="flex items-center gap-6">
                    {{-- Mobile menu button --}}
                    <button type="button"
                        class="md:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all"
                        @click="sidebarOpen = true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden sm:flex flex-col justify-center">
                        <nav class="flex items-center text-xs space-x-2 text-slate-400 font-bold uppercase tracking-wider">
                            <span class="hover:text-blue-600 transition-colors cursor-pointer">Main System</span>
                            <span>/</span>
                            <span class="text-slate-900 whitespace-nowrap">@yield('page-title', 'Overview')</span>
                        </nav>
                        @hasSection('page-subtitle')
                            <p class="text-[11px] text-slate-500 font-medium truncate max-w-[300px] mt-0.5 leading-tight">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                @php
                    $user = auth()->user();
                    $currentBranch = session('branch', 'JAKARTA');
                    $env = app()->environment();
                    $envLabel = match ($env) { 'production' => 'PROD', 'staging' => 'STAGE', default => 'DEV' };
                    $envColorClasses = match ($env) {
                        'production' => 'from-emerald-500 to-teal-600 shadow-emerald-100',
                        'staging' => 'from-amber-400 to-orange-500 shadow-amber-100',
                        default => 'from-blue-500 to-violet-600 shadow-blue-100',
                    };
                    $initials = $user ? collect(explode(' ', $user->name))->filter()->map(fn($part) => mb_substr($part, 0, 1))->join('') : 'U';
                @endphp

                <div class="flex items-center gap-4">
                    {{-- Status Pills --}}
                    <div class="hidden lg:flex items-center gap-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100/80 border border-slate-200/50">
                            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse mt-2"></div>
                            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">{{ $currentBranch }}</span>
                        </div>
                        <div class="px-3 py-1.5 rounded-full bg-gradient-to-r {{ $envColorClasses }} text-white shadow-md">
                            <span class="text-[10px] font-black uppercase tracking-widest block pt-[1px]">{{ $envLabel }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200/60">
                        @livewire('notifications.bell')

                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button type="button" @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                                class="flex items-center gap-3 p-1 rounded-2xl hover:bg-slate-50 transition-all duration-300 group">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-100 group-hover:scale-105 transition-all">
                                    {{ strtoupper(mb_substr($initials, 0, 2)) }}
                                </div>
                                <div class="hidden sm:block text-left pr-1.5">
                                    <div class="text-[13px] font-bold text-slate-900 leading-tight">{{ $user->name ?? 'User' }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $user->email ?? '' }}</div>
                                </div>
                                <svg class="hidden sm:block h-4 w-4 text-slate-400 transition-transform duration-300" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Profile Dropdown --}}
                            <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-cloak
                                class="absolute right-0 mt-3 w-64 rounded-2xl bg-white shadow-2xl shadow-blue-900/10 border border-slate-200/60 p-2 z-[60]">
                                <div class="px-4 py-4 mb-1 rounded-xl bg-slate-50 border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Signed in as</p>
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $user->name ?? 'User' }}</p>
                                </div>
                                
                                <div class="space-y-0.5">
                                    <a href="{{ route('account.security') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all group">
                                        <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                            @include('new.layouts.partials.nav-icon', ['name' => 'shield'])
                                        </div>
                                        Security Settings
                                    </a>
                                    <a href="{{ route('signatures.manage') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all group">
                                        <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                            @include('new.layouts.partials.nav-icon', ['name' => 'wrench'])
                                        </div>
                                        My Signatures
                                    </a>
                                </div>

                                <div class="my-2 border-t border-slate-100"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition-all group">
                                        <div class="h-8 w-8 rounded-lg bg-rose-100/50 flex items-center justify-center group-hover:bg-rose-100 transition-all">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                        </div>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main content --}}
            <main class="flex-1 overflow-y-auto px-6 py-8 md:px-10 md:py-10">
                <div class="max-w-7xl mx-auto space-y-6">
                    <livewire:layout.flash-messages />
                    
                    <div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 ease-out">
                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts
    <div id="modals-container"></div>
    @stack('modals')
    
    {{-- Toast Notification System --}}
    <div x-data="toastManager()" 
         @toast.window="addToast($event.detail)"
         class="fixed bottom-6 right-6 z-[100] space-y-3 max-w-sm"
        x-init="
            // Safe: runs AFTER this component has initialized
            $nextTick(() => {
                @if(session()->has('toast_success') || session()->has('toast_error') || session()->has('toast_warning') || session()->has('toast_info'))
                    @if(session()->has('toast_success'))
                        $dispatch('toast', { type: 'success', message: @json(session('toast_success')) })
                    @endif
                    @if(session()->has('toast_error'))
                        $dispatch('toast', { type: 'error', message: @json(session('toast_error')) })
                    @endif
                    @if(session()->has('toast_warning'))
                        $dispatch('toast', { type: 'warning', message: @json(session('toast_warning')) })
                    @endif
                    @if(session()->has('toast_info'))
                        $dispatch('toast', { type: 'info', message: @json(session('toast_info')) })
                    @endif
                @endif
            })
        "
         x-cloak>
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full opacity-0 scale-95"
                 x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                 x-transition:leave-end="translate-x-full opacity-0 scale-95"
                 class="flex items-start gap-3 rounded-2xl px-5 py-4 shadow-2xl backdrop-blur-xl border min-w-[320px] max-w-sm"
                 :class="{
                     'bg-emerald-500/95 text-white border-emerald-400/50': toast.type === 'success',
                     'bg-rose-500/95 text-white border-rose-400/50': toast.type === 'error',
                     'bg-amber-500/95 text-white border-amber-400/50': toast.type === 'warning',
                     'bg-blue-500/95 text-white border-blue-400/50': toast.type === 'info'
                 }">
                {{-- Icon --}}
                <div class="flex-shrink-0 mt-0.5" x-html="getIcon(toast.type)"></div>
                
                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold leading-tight" x-text="toast.message"></p>
                    <div class="mt-2 h-1 bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-white/60 transition-all ease-linear" 
                             :style="`width: ${toast.progress}%; transition-duration: 100ms`"></div>
                    </div>
                </div>
                
                {{-- Close button --}}
                <button @click="removeToast(toast.id)" 
                        class="flex-shrink-0 opacity-75 hover:opacity-100 transition-opacity">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Toast Manager Alpine Component --}}
    <script>
        function toastManager() {
            return {
                toasts: [],
                nextId: 1,
                
                addToast(data) {
                    const id = this.nextId++;
                    const duration = data.duration || 5000;
                    const toast = { 
                        id, 
                        type: data.type || 'info',
                        message: data.message,
                        visible: false,
                        progress: 100
                    };
                    
                    this.toasts.push(toast);
                    
                    // Trigger enter animation
                    this.$nextTick(() => {
                        toast.visible = true;
                        
                        // Progress bar animation
                        const interval = 100;
                        const steps = duration / interval;
                        let currentStep = 0;
                        
                        const progressInterval = setInterval(() => {
                            currentStep++;
                            toast.progress = 100 - (currentStep / steps * 100);
                            
                            if (currentStep >= steps) {
                                clearInterval(progressInterval);
                                this.removeToast(id);
                            }
                        }, interval);
                        
                        // Store interval ID for manual clearing
                        toast.intervalId = progressInterval;
                    });
                },
                
                removeToast(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        // Clear progress interval
                        if (toast.intervalId) {
                            clearInterval(toast.intervalId);
                        }
                        
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300); // Wait for exit animation
                    }
                },
                
                getIcon(type) {
                    const icons = {
                        success: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"/></svg>',
                        error: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"/></svg>',
                        warning: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"/></svg>',
                        info: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"/></svg>'
                    };
                    return icons[type] || icons.info;
                }
            }
        }
    </script>
    
    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @stack('scripts')
</body>
</html>
