<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DISS | Daijo Industrial Support') }}</title>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">


    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('extraCss')

    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="sidebar-toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="#">Menu</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item" id="sidebar-item-dashboard">
                    <a href="{{ route('superadmin.home') }}" class="sidebar-link">
                        <i class="lni lni-graph"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (Auth::user()->department === "Production")
                <li class="sidebar-item" id="sidebar-item-production">
                    <a href="{{ route('superadmin.production') }}" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Production</span>
                    </a>
                </li>
                @elseif (Auth::user()->department === "QA" || Auth::user()->department === "QC")
                <li class="sidebar-item" id="sidebar-item-qaqc">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#qaqc" aria-expanded="false" aria-controls="qaqc">
                        <i class="lni lni-protection"></i>
                        <span>QA/QC</span>
                    </a>
                    <ul id="qaqc" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('qaqc.report.view') }}" class="sidebar-link">Reports</a>
                        </li>
                    </ul>
                </li>
                @elseif (Auth::user()->department === "Business")
                <li class="sidebar-item" id="sidebar-item-business">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#business" aria-expanded="false" aria-controls="business">
                        <i class="lni lni-protection"></i>
                        <span>Business</span>
                    </a>
                    <ul id="business" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('superadmin.business') }}" class="sidebar-link">Reports</a>
                        </li>
                    </ul>
                </li>
                @elseif (Auth::user()->department === "HRD")
                <li class="sidebar-item" id="sidebar-item-hrd">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#hrd" aria-expanded="false" aria-controls="hrd">
                        <i class="lni lni-protection"></i>
                        <span>HRD</span>
                    </a>
                    <ul id="hrd" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('hrd.importantDocs') }}" class="sidebar-link">Important Documents</a>
                        </li>
                    </ul>
                </li>
                @else
                <li class="sidebar-item" id="sidebar-item-admin">
                     <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#admin" aria-expanded="false" aria-controls="admin">
                        <i class="lni lni-protection"></i>
                        <span>Admin</span>
                    </a>
                    <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('superadmin.users') }}" class="sidebar-link">Users</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('superadmin.permission') }}" class="sidebar-link">Permission</a>
                        </li>
                    </ul>
                </li>
                @endif

                <li class="sidebar-item" id="sidebar-item-setting">
                    <a href="{{ route('superadmin.settings') }}" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Setting</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                              document.getElementById('logout-form').submit();">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </aside>

        <div class="main">
            <nav class="navbar navbar-expand px-3 py-3 border d-flex">
                <!--Header-->
                <div class="flex-grow-1">
                    <h4 class="pt-1 ps-3">Daijo Industrial Support System</h4>
                </div>

                <!--Notification-->
                <div class="me-3">
                    <button type="button" class="btn btn-success position-relative rounded-circle me-3">
                        <i class="lni lni-popup"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                            +99 <span class="visually-hidden">unread messages</span>
                        </span>
                    </button>
                </div>

                <!-- Profile Icon -->
                <div class="me-2">
                    <div class="navbar navbar-collapse">
                        <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0" type="button">
                            <img src="{{ asset('image/profile.jpg') }}" class="avatar img-fluid rounded-circle " alt="profilePicture">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item">Profile</a>
                            <a href="#" class="dropdown-item">Setting</a>
                            <a href="#" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <main class="content px-5 py-5 height-vh-100">
                @yield('content')
            </main>



            {{-- <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="text-end">
                            <p class="m-2">
                                <a href="#" class="text-muted">
                                    <strong>Daijo Industrial Support System (DS)</strong>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
             --}}
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/modal.js') }}"></script>

    @stack('extraJS')
</body>
</html>
