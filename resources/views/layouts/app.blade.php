<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-user-id" content="{{ auth()->id() }}">

  <title>@yield('title', config('app.name'))</title>

  <script>
    const meta = document.querySelector('meta[name="app-user-id"]');
    window.Laravel = {
      userId: meta ? meta.content : null
    };
  </script>
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])

  <link rel="dns-prefetch" href="//fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
  <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @livewireStyles
  @stack('extraCss')
</head>

<body class="{{ session('sidebar_open', false) ? 'sidebar-open' : 'sidebar-closed' }}">
  <div class="wrapper">
    <livewire:sidebar />

    <div class="main">
      @include('partials.navbar')

      <main class="content px-4 px-md-5 py-4">
        {{ $slot ?? '' }}
        @yield('content')
      </main>
    </div>
  </div>

  {{-- Toast: always present --}}
  <div x-data class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"
    @toast.window="
        const el = $refs.toastOk;
        el.querySelector('.toast-body').textContent = ($event.detail?.message ?? '');
        bootstrap.Toast.getOrCreateInstance(el).show();
       ">
    <div x-ref="toastOk" class="toast" role="alert" aria-live="assertive" aria-atomic="true"
      data-bs-autohide="true">
      <div class="toast-header">
        <strong class="me-auto">Info</strong>
        <small>Now</small>
        <button class="btn-close" data-bs-dismiss="toast" type="button"
          aria-label="Close"></button>
      </div>
      <div class="toast-body"></div>
    </div>
  </div>

  @livewireScripts
  @stack('extraJs')
  <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js" defer></script>
</body>

</html>

