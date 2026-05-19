{{-- Top bar --}}
<header
    class="h-16 flex items-center justify-between border-b border-slate-200/60 bg-white/70 backdrop-blur-md px-6 sticky top-0 z-40 transition-all duration-300">
    <div class="flex items-center gap-6">
        {{-- Mobile menu button --}}
        <button type="button"
            class="md:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all"
            @click="sidebarOpen = true"
            aria-label="Toggle mobile menu"
            aria-controls="mobile-sidebar"
            :aria-expanded="sidebarOpen">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="flex flex-col justify-center space-y-1">
            <nav
                class="flex items-center text-xs space-x-2 text-slate-500 font-bold uppercase tracking-wider">
                <span class="hidden sm:inline hover:text-blue-600 transition-colors cursor-pointer">Main System</span>
                <span class="hidden sm:inline">/</span>
                <span class="text-slate-900 whitespace-nowrap">@yield('page-title', 'Overview')</span>
            </nav>
            @hasSection('page-subtitle')
                <p
                    class="hidden sm:block text-[11px] text-slate-600 font-medium truncate max-w-[300px] mt-0.5 leading-tight">
                    @yield('page-subtitle')</p>
            @endif
        </div>
    </div>

    @php
        $env = app()->environment();
        $envLabel = match ($env) {
            'production' => 'PROD',
            'staging' => 'STAGE',
            default => 'DEV',
        };
        $envColorClasses = match ($env) {
            'production' => 'from-emerald-500 to-teal-600 shadow-emerald-100',
            'staging' => 'from-amber-400 to-orange-500 shadow-amber-100',
            default => 'from-blue-500 to-violet-600 shadow-blue-100',
        };
    @endphp

    <div class="flex items-center gap-4">
        {{-- Status Pills --}}
        <div class="hidden lg:flex items-center gap-3">
            @if(isset($currentBranch) && $currentBranch)
            <div
                class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100/80 border border-slate-200/50">
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse mt-2"></div>
                <span
                    class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">{{ $currentBranch }}</span>
            </div>
            @endif
            <div
                class="px-3 py-1.5 rounded-full bg-gradient-to-r {{ $envColorClasses }} text-white shadow-md">
                <span
                    class="text-[10px] font-black uppercase tracking-widest block pt-[1px]">{{ $envLabel }}</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pl-4 border-l border-slate-200/60">
            @livewire('notifications.bell')

            <div class="relative" x-data="{ userMenuOpen: false }">
                <button type="button" @click="userMenuOpen = !userMenuOpen"
                    @click.outside="userMenuOpen = false"
                    @keydown.escape.window="userMenuOpen = false"
                    aria-haspopup="true"
                    :aria-expanded="userMenuOpen"
                    class="flex items-center gap-3 p-1 rounded-2xl hover:bg-slate-50 transition-all duration-300 group">
                    <div
                        class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-100 group-hover:scale-105 transition-all">
                        {{ strtoupper(mb_substr($initials ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left pr-1.5">
                        <div class="text-[13px] font-bold text-slate-900 leading-tight">
                            {{ $user?->name ?? 'User' }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                            {{ $user?->email ?? '' }}</div>
                    </div>
                    <svg class="hidden sm:block h-4 w-4 text-slate-400 transition-transform duration-300"
                        :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Profile Dropdown --}}
                <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    x-cloak
                    class="absolute right-0 mt-3 w-64 rounded-2xl bg-white shadow-2xl shadow-blue-900/10 border border-slate-200/60 p-2 z-[60]">
                    <div class="px-4 py-3 mb-1 rounded-xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                            Signed in as</p>
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $user?->name ?? 'User' }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <a href="{{ route('account.notifications') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all group">
                            <div
                                class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                @include('new.layouts.partials.nav-icon', ['name' => 'bell'])
                            </div>
                            Notification Settings
                        </a>
                        <a href="{{ route('account.security') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all group">
                            <div
                                class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                @include('new.layouts.partials.nav-icon', ['name' => 'shield'])
                            </div>
                            Security Settings
                        </a>
                        <a href="{{ route('signatures.manage') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all group">
                            <div
                                class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                @include('new.layouts.partials.nav-icon', ['name' => 'wrench'])
                            </div>
                            My Signatures
                        </a>
                    </div>

                    <div class="my-2 border-t border-slate-100"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition-all group">
                            <div
                                class="h-8 w-8 rounded-lg bg-rose-100/50 flex items-center justify-center group-hover:bg-rose-100 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
