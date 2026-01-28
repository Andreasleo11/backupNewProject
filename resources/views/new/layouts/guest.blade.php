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
        body {
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.08), transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.08), transparent 50%),
                        radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.06), transparent 40%),
                        linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
        }
        .gradient-mesh {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.4;
        }
        .gradient-mesh::before,
        .gradient-mesh::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
        }
        .gradient-mesh::before {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
            top: -100px;
            left: -100px;
            animation: mesh1 20s ease-in-out infinite;
        }
        .gradient-mesh::after {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(239, 68, 68, 0.2));
            bottom: -50px;
            right: -50px;
            animation: mesh2 15s ease-in-out infinite;
        }
        @keyframes mesh1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(50px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }
        @keyframes mesh2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-40px, -40px) scale(1.15); }
        }
    </style>

    @stack('head')
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 font-sans antialiased">
    <div class="gradient-mesh"></div>
    
    <div class="w-full max-w-md relative z-10">
        {{-- Logo Section --}}
        <div class="text-center mb-8 animate-float">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 via-blue-600 to-violet-600 shadow-2xl shadow-blue-200 mb-4">
                <img class="h-10 w-10 brightness-0 invert" src="{{ asset('image/Asset 1.svg') }}" alt="logo">
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 mb-1">
                {{ config('app.name') }}
            </h1>
            <p class="text-xs font-bold text-blue-600 uppercase tracking-[0.3em]">
                Authentication Portal
            </p>
        </div>

        {{-- Main Content Card --}}
        <div class="glass-card rounded-3xl border border-white/60 shadow-2xl shadow-blue-900/10 p-8">
            @yield('content')
        </div>

        {{-- Footer --}}
        <p class="mt-6 text-center text-[11px] font-medium text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

    @stack('scripts')
</body>
</html>
