@extends('new.layouts.guest')

@section('title', 'Login')

@section('content')
    {{-- Logo / App name --}}
    <div class="mb-6 text-center">
        <img class="mx-auto mb-2 flex h-10 items-center justify-center" src="{{ asset('image/Asset 1.svg') }}" alt=""
            srcset="">
        <h1 class="text-lg font-semibold text-slate-900">
            Sign in
        </h1>
        <p class="mt-1 text-xs text-slate-500">
            Use your account to access the DISS.
        </p>
    </div>

    {{-- Card --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
        {{-- Session / error messages --}}
        @if (session('status'))
            <div class="mb-3 rounded-md bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-3 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-xs font-medium text-slate-700">
                    Email
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-900 focus:ring-1 focus:ring-slate-900">
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-medium text-slate-700">
                        Password
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-xs font-medium text-slate-500 hover:text-slate-700">
                            Forgot?
                        </a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-900 focus:ring-1 focus:ring-slate-900">
            </div>

            {{-- Remember me --}}
            <div class="flex items-center justify-between text-xs">
                <label class="inline-flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember"
                        class="h-3.5 w-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                        {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-white hover:text-slate-900 hover:font-medium hover:ring-1 hover:ring-offset-0 hover:ring-slate-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1">
                Sign in
            </button>

            <div class="flex mt-4 text-sm justify-end items-center font-light gap-x-2">
                Daily Employee Job?
                <a href="{{ route('employee.login') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium bg-slate-900 text-white shadow-sm transition hover:bg-white hover:text-slate-900 hover:font-medium hover:ring-1 hover:ring-offset-0 hover:ring-slate-900 focus:outline-none focus-ring-2 focus:ring-slate-900 focus:ring-offset-1">Click
                    Here</a>
            </div>
        </form>
    </div>
@endsection
