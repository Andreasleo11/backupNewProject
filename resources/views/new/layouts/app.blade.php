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

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui'],
                    },
                },
            },
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .main-gradient {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03), transparent),
                        radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.03), transparent),
                        #f8fafc;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
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

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    @stack('head')
</head>

<body class="min-h-screen main-gradient text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-900" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: $persist(false),
    q: '',
    getSearchResultCount() {
        if (!q) return 0;
        const query = q.toLowerCase();
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
            <div class="flex items-center justify-between h-16 px-5 border-b border-slate-100">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 shadow-lg shadow-blue-200">
                        <img class="h-6 w-6 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <span class="text-base font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-600">
                        {{ config('app.name') }}
                    </span>
                </a>
                <button type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all duration-200"
                    @click="sidebarOpen = false">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar">
                @include('new.layouts.partials.sidebar-nav', ['isMobile' => true])
            </div>

            <div class="border-t border-slate-100 p-5 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-slate-800 to-slate-950 flex items-center justify-center text-white font-bold shadow-md">
                        {{ strtoupper(auth()->user()->name[0] ?? 'U') }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Authenticated as</p>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="min-h-screen flex">
        {{-- Desktop sidebar --}}
        <aside class="hidden md:flex flex-col border-r border-slate-200/60 bg-white/80 backdrop-blur-xl transition-all duration-500 ease-in-out sticky top-0 h-screen z-50"
            :class="sidebarCollapsed ? 'w-20' : 'w-72'">
            {{-- Header --}}
            <div class="flex items-center justify-between h-16 px-4 border-b border-slate-100">
                @php
                    $appName = config('app.name');
                    $words = preg_split('/\s+/', trim($appName));
                    $appAcronym = '';
                    foreach ($words as $word) { $appAcronym .= mb_substr($word, 0, 1); }
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-3.5 group" :class="sidebarCollapsed ? 'mx-auto' : ''">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-lg shadow-blue-100 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <img class="h-6 w-6 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="transition-opacity duration-300">
                        <span class="text-base font-extrabold tracking-tight text-slate-900 block leading-none">{{ $appName }}</span>
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.2em] mt-0.5 block leading-none">{{ strtoupper($appAcronym) }} SYSTEM</span>
                    </div>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar">
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

                    <div class="hidden sm:block">
                        <nav class="flex text-xs space-x-2 text-slate-400 font-medium uppercase tracking-wider">
                            <span class="hover:text-blue-600 transition-colors">Main System</span>
                            <span>/</span>
                            <span class="text-slate-900 font-bold whitespace-nowrap">@yield('page-title', 'Overview')</span>
                        </nav>
                        @hasSection('page-subtitle')
                            <p class="text-[11px] text-slate-500 font-medium truncate max-w-[300px] mt-0.5">@yield('page-subtitle')</p>
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
                    <div class="hidden lg:flex items-center gap-2">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100/80 border border-slate-200/50">
                            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">{{ $currentBranch }}</span>
                        </div>
                        <div class="px-3 py-1.5 rounded-full bg-gradient-to-r {{ $envColorClasses }} text-white shadow-md">
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $envLabel }}</span>
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
                                <div class="hidden sm:block text-left pr-2">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $user->name ?? 'User' }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-tighter">{{ $user->email ?? '' }}</p>
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
    @stack('scripts')
</body>
</html>
