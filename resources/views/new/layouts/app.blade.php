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

    {{-- Livewire styles --}}
    @livewireStyles

    @stack('head')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900" x-data="{ sidebarOpen: false, sidebarCollapsed: false, q: '' }" x-cloak>
    {{-- Mobile sidebar --}}
    <div class="md:hidden" x-show="sidebarOpen" x-transition.opacity>
        <div class="fixed inset-0 z-40 bg-slate-900/40" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white border-r border-slate-200">
            {{-- Header --}}
            <div class="flex items-center justify-between h-14 px-4 border-b border-slate-200">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img class="h-10" src="{{ asset('image/Asset 1.svg') }}" alt="logo" srcset="">
                    <span class="text-sm font-semibold text-slate-900">
                        {{ config('app.name') }}
                    </span>
                </a>
                <button type="button" class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100"
                    @click="sidebarOpen = false">
                    ✕
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
            <div class="flex items-center justify-between h-14 px-3 border-b border-slate-200">
                @php
                    $appName = config('app.name');
                    $words = preg_split('/\s+/', trim($appName));
                    $appAcronym = '';
                    foreach ($words as $word) {
                        $appAcronym .= mb_substr($word, 0, 1);
                    }
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img class="h-10" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
                    {{-- Animated name / acronym --}}
                    <span x-data="{ hover: false }" x-show="!sidebarCollapsed" x-cloak
                        class="relative inline-block text-sm font-semibold text-slate-900" @mouseenter="hover = true"
                        @mouseleave="hover = false">
                        {{-- Full name --}}
                        <span class="block transition-opacity duration-150"
                            :class="hover ? 'opacity-0' : 'opacity-100'">
                            {{ $appName }}
                        </span>

                        {{-- Acronym (DISS, etc.) --}}
                        <span
                            class="pointer-events-none absolute inset-0 flex items-center transition-opacity duration-150"
                            :class="hover ? 'opacity-100' : 'opacity-0'">
                            {{ strtoupper($appAcronym) }}
                        </span>
                    </span>
                </a>
                <button type="button"
                    class="hidden md:inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-slate-100"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"
                        :class="sidebarCollapsed ? 'rotate-180' : ''">
                        <path fill-rule="evenodd"
                            d="M12.79 4.21a.75.75 0 010 1.06L9.06 9l3.73 3.73a.75.75 0 11-1.06 1.06L7.47 9.53a.75.75 0 010-1.06l4.26-4.26a.75.75 0 011.06 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            @include('new.layouts.partials.sidebar-nav')

            <div class="border-t border-slate-200 px-4 py-3 text-xs text-slate-500">
                <div x-show="!sidebarCollapsed">
                    Logged in as<br>
                    <span class="font-medium text-slate-700">
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                </div>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6">
                <div class="flex items-center gap-3">
                    {{-- Mobile menu button --}}
                    <button type="button" class="md:hidden rounded-md border border-slate-200 p-1.5 text-slate-500"
                        @click="sidebarOpen = true">
                        ☰
                    </button>

                    <div>
                        <h1 class="text-sm font-semibold text-slate-900">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        @hasSection('page-subtitle')
                            <p class="text-xs text-slate-500">
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
                        class="hidden sm:inline-flex items-center rounded-full border bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                        <span class="mr-1 h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                        {{ strtoupper($currentBranch) }}
                    </span>

                    {{-- Environment badge --}}
                    <span
                        class="hidden sm:inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $envColorClasses }}">
                        {{ $envLabel }}
                    </span>

                    {{-- Notification bell --}}
                    @livewire('notifications.bell')

                    {{-- User menu --}}
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button type="button" @click="userMenuOpen = !userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-xs shadow-sm hover:bg-slate-50">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-[11px] font-semibold text-white">
                                {{ strtoupper($initials) }}
                            </span>

                            <span class="hidden sm:flex flex-col text-left leading-tight">
                                <span class="text-xs font-medium text-slate-800">
                                    {{ $user->name ?? 'User' }}
                                </span>
                                <span class="text-[11px] text-slate-400 truncate max-w-[140px]">
                                    {{ $user->email ?? '' }}
                                </span>
                            </span>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="userMenuOpen" x-transition x-cloak
                            class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 text-xs shadow-lg">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <div class="font-medium text-slate-900 truncate">
                                    {{ $user->name ?? 'User' }}
                                </div>
                                <div class="text-[11px] text-slate-400 truncate">
                                    {{ $user->email ?? '' }}
                                </div>
                            </div>

                            <a href="{{ route('account.security') }}"
                                class="block px-3 py-2 text-slate-600 hover:bg-slate-50">
                                Account security
                            </a>


                            {{-- Future: Profile, Settings, etc.
                            <a href="#" class="block px-3 py-2 hover:bg-slate-50 text-slate-600">
                                Profile
                            </a> --}}

                            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center px-3 py-2 text-left text-slate-600 hover:bg-slate-50">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main content --}}
            <main class="flex-1 overflow-y-auto px-4 py-4 md:px-6 md:py-6">
                <livewire:layout.flash-messages />
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts

    @stack('scripts')
</body>

</html>
