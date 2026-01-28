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

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    @stack('head')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: false,
    q: '',
    getSearchResultCount() {
        if (!q) return 0;
        const query = q.toLowerCase();
        let count = 0;

        // Count all navigation items that match the search
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
        <div class="fixed inset-0 z-40 bg-slate-900/40" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white border-r border-slate-200 shadow-2xl">
            {{-- Header --}}
            <div class="flex items-center justify-between h-14 px-4 border-b border-slate-200 bg-gradient-to-r from-white to-slate-50/50">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 shadow-sm">
                        <img class="h-6 w-6" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    <span class="text-sm font-semibold text-slate-900 group-hover:text-blue-700 transition-colors duration-200">
                        {{ config('app.name') }}
                    </span>
                </a>
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    @click="sidebarOpen = false"
                    aria-label="Close sidebar">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @include('new.layouts.partials.sidebar-nav')

            <div class="border-t border-slate-200 px-4 py-3 text-xs text-slate-500">
                Logged in as<br>
                <span class="font-medium text-slate-700">
                    {{ auth()->user()->name ?? 'User' }}
                </span>
            </div>
        </aside>
    </div>

    <div class="min-h-screen flex">
        {{-- Desktop sidebar --}}
        <aside class="hidden md:flex flex-col border-r border-slate-200 bg-white transition-all duration-200"
            :class="sidebarCollapsed ? 'md:w-20' : 'md:w-64'">
            {{-- Header --}}
            <div class="flex items-center justify-between h-14 px-3 border-b border-slate-200 bg-gradient-to-r from-white to-slate-50/50">
                @php
                    $appName = config('app.name');
                    $words = preg_split('/\s+/', trim($appName));
                    $appAcronym = '';
                    foreach ($words as $word) {
                        $appAcronym .= mb_substr($word, 0, 1);
                    }
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-3 group" :title="$appName">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 shadow-sm transition-all duration-200 group-hover:shadow-md">
                        <img class="h-6 w-6" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    </div>
                    {{-- Animated name / acronym --}}
                    <span x-data="{ hover: false }" x-show="!sidebarCollapsed" x-cloak
                        class="relative inline-block text-sm font-semibold text-slate-900 group-hover:text-blue-700 transition-colors duration-200"
                        @mouseenter="hover = true" @mouseleave="hover = false">
                        {{-- Full name --}}
                        <span class="block transition-opacity duration-200"
                            :class="hover ? 'opacity-0' : 'opacity-100'">
                            {{ $appName }}
                        </span>

                        {{-- Acronym (DISS, etc.) --}}
                        <span
                            class="pointer-events-none absolute inset-0 flex items-center transition-opacity duration-200 font-bold text-blue-600"
                            :class="hover ? 'opacity-100' : 'opacity-0'">
                            {{ strtoupper($appAcronym) }}
                        </span>
                    </span>
                </a>
                <button type="button"
                    class="hidden md:inline-flex items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor"
                        :class="sidebarCollapsed ? 'rotate-180' : ''">
                        <path fill-rule="evenodd"
                            d="M12.79 4.21a.75.75 0 010 1.06L9.06 9l3.73 3.73a.75.75 0 11-1.06 1.06L7.47 9.53a.75.75 0 010-1.06l4.26-4.26a.75.75 0 011.06 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            @include('new.layouts.partials.sidebar-nav')

            <div class="border-t border-slate-200 px-4 py-4 bg-gradient-to-r from-slate-50 to-white">
                <div x-show="!sidebarCollapsed" class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-slate-500">Logged in as</div>
                        <div class="font-semibold text-slate-800 truncate">
                            {{ auth()->user()->name ?? 'User' }}
                        </div>
                    </div>
                </div>
                <div x-show="sidebarCollapsed" class="flex justify-center">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6 shadow-sm">
                <div class="flex items-center gap-4">
                    {{-- Mobile menu button --}}
                    <button type="button"
                        class="md:hidden flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        @click="sidebarOpen = true"
                        aria-label="Open sidebar menu">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="text-sm font-semibold text-slate-900 truncate">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        @hasSection('page-subtitle')
                            <p class="text-xs text-slate-500 truncate mt-0.5">
                                @yield('page-subtitle')
                            </p>
                        @endif
                    </div>
                </div>

                @php
                    $user = auth()->user();
                    // Example: branch from user/employee/session – adjust to your real source
                    $currentBranch = session('branch', 'JAKARTA'); // or $user?->employee?->branch ?? 'JAKARTA';

                    $env = app()->environment();
                    $envLabel = match ($env) {
                        'production' => 'PROD',
                        'staging' => 'STAGE',
                        default => 'DEV',
                    };
                    $envColorClasses = match ($env) {
                        'production' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'staging' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                    };

                    $initials = $user
                        ? collect(explode(' ', $user->name))
                            ->filter()
                            ->map(fn($part) => mb_substr($part, 0, 1))
                            ->join('')
                        : 'U';
                @endphp

                <div class="flex items-center gap-3">
                    {{-- Branch badge --}}
                    <span
                        class="hidden sm:inline-flex items-center rounded-full border border-slate-300 bg-gradient-to-r from-slate-50 to-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm">
                        <span class="mr-2 h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                        {{ strtoupper($currentBranch) }}
                    </span>

                    {{-- Environment badge --}}
                    <span
                        class="hidden sm:inline-flex items-center rounded-full border px-3 py-1.5 text-[11px] font-semibold shadow-sm {{ $envColorClasses }}">
                        {{ $envLabel }}
                    </span>

                    {{-- Notification bell --}}
                    <div class="relative">
                        @livewire('notifications.bell')
                    </div>

                    {{-- User menu --}}
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button type="button" @click="userMenuOpen = !userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm hover:bg-slate-50 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-slate-800 to-slate-900 text-[11px] font-bold text-white shadow-sm">
                                {{ strtoupper($initials) }}
                            </span>

                            <span class="hidden sm:flex flex-col text-left leading-tight">
                                <span class="text-xs font-semibold text-slate-800">
                                    {{ $user->name ?? 'User' }}
                                </span>
                                <span class="text-[11px] text-slate-500 truncate max-w-[140px]">
                                    {{ $user->email ?? '' }}
                                </span>
                            </span>

                            <svg class="hidden sm:block h-4 w-4 text-slate-400 transition-transform duration-200"
                                 :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="userMenuOpen" x-transition x-cloak
                            class="absolute right-0 mt-3 w-56 rounded-xl border border-slate-200 bg-white py-2 text-sm shadow-xl ring-1 ring-black/5">
                            <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                                <div class="font-semibold text-slate-900 truncate">
                                    {{ $user->name ?? 'User' }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-0.5">
                                    {{ $user->email ?? '' }}
                                </div>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('account.security') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 group">
                                    <svg class="h-4 w-4 text-slate-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Account Security
                                </a>

                                <a href="{{ route('signatures.manage') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 group">
                                    <svg class="h-4 w-4 text-slate-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    {{ __('My Signatures') }}
                                </a>
                            </div>

                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-red-50 hover:text-red-700 transition-colors duration-200 group">
                                        <svg class="h-4 w-4 text-slate-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main content --}}
            <main class="flex-1 overflow-y-auto px-4 py-6 md:px-8 md:py-8 bg-slate-50/50 min-h-0">
                <div class="max-w-7xl mx-auto">
                    <livewire:layout.flash-messages />
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts

    @stack('scripts')
</body>

</html>
