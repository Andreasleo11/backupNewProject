@extends('layouts.guest')

@section('content')
    {{-- Ideally move this to layouts.guest if Tailwind is used globally --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            {{-- Card --}}
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-100 px-6 py-7">
                {{-- Header --}}
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-slate-900">
                        Employee Login
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Masuk untuk mengisi dan melihat laporan harian.
                    </p>
                </div>

                {{-- Global error message --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('employee.login.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- NIK --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1" for="nik">
                            NIK
                        </label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik') }}"
                            class="block w-full rounded-xl border px-3 py-2.5 text-sm
                                   border-slate-200 text-slate-900 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   @error('nik') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                            placeholder="Masukkan NIK"
                            autocomplete="username"
                        >
                        @error('nik')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1" for="password">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="block w-full rounded-xl border px-3 py-2.5 text-sm
                                   border-slate-200 text-slate-900 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   @error('password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full inline-flex justify-center items-center rounded-xl bg-indigo-600
                                   px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/30
                                   hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 focus:ring-offset-slate-900 transition"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>

            {{-- Small footer --}}
            <p class="mt-4 text-center text-[11px] text-slate-400">
                © {{ date('Y') }} {{ env('APP_NAME') }} — Employee Portal
            </p>
        </div>
    </div>
@endsection
