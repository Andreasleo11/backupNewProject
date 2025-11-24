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

    {{-- Tailwind CDN (if you switch to Vite later, remove this and use @vite) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        @yield('content')

        <p class="mt-4 text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>

    @stack('scripts')
</body>
</html>
