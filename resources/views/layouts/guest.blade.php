<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>{{ config('app.name') }}</title>
</head>
<body>
    @yield('content')
</body>
</html>

