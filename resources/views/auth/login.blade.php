@extends('new.layouts.guest')

@section('title', 'Login')

@section('content')
    {{-- Header --}}
    <div class="mb-6 text-center">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
            Welcome Back
        </h2>
        <p class="mt-1.5 text-sm text-slate-500 font-medium">
            Sign in to access your dashboard
        </p>
    </div>

    {{-- Session / error messages --}}
    @if (session('status'))
        <div class="mb-4 flex items-start gap-3 rounded-2xl bg-emerald-50/80 backdrop-blur-sm border border-emerald-100 px-4 py-3.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-emerald-700 leading-relaxed">
                {{ session('status') }}
            </p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl bg-rose-50/80 backdrop-blur-sm border border-rose-100 px-4 py-3.5">
            <div class="flex items-start gap-3 mb-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-rose-700 mb-2">Please fix the following errors:</p>
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs font-medium text-rose-600 flex items-start gap-1.5">
                                <span class="text-rose-400 mt-0.5">•</span>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Email Address
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="block w-full rounded-xl border border-slate-200/60 bg-white/50 backdrop-blur-sm pl-12 pr-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm outline-none transition-all duration-300
                           focus:border-blue-300 focus:bg-white focus:ring-4 focus:ring-blue-500/10
                           hover:border-slate-300"
                    placeholder="your.email@example.com">
            </div>
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                        Forgot?
                    </a>
                @endif
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required
                    class="block w-full rounded-xl border border-slate-200/60 bg-white/50 backdrop-blur-sm pl-12 pr-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 shadow-sm outline-none transition-all duration-300
                           focus:border-blue-300 focus:bg-white focus:ring-4 focus:ring-blue-500/10
                           hover:border-slate-300"
                    placeholder="••••••••">
            </div>
        </div>

        {{-- Remember me --}}
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                <input type="checkbox" name="remember"
                    class="h-4 w-4 rounded-md border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-0 transition-all"
                    {{ old('remember') ? 'checked' : '' }}>
                <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Remember me</span>
            </label>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition-all duration-300
                   hover:shadow-xl hover:shadow-blue-300 hover:scale-[1.02]
                   focus:outline-none focus:ring-4 focus:ring-blue-500/20
                   active:scale-[0.98]">
            <span class="relative z-10 flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-violet-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </button>

        {{-- Divider --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-white px-3 py-1 rounded-full text-slate-400 font-bold uppercase tracking-wider">Or</span>
            </div>
        </div>

        {{-- Employee Login Link --}}
        <a href="{{ route('employee.login') }}"
            class="group flex items-center justify-center gap-3 w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition-all duration-300
                   hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700
                   focus:outline-none focus:ring-4 focus:ring-blue-500/10">
            <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Daily Employee Login
        </a>
    </form>
@endsection
