<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DISS | Daijo Industrial Support') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/app.css">
</head>
<body data-bs-theme="dark">
    <div class="wrapper">
        {{-- <aside id="sidebar" class="js-sidebar">
            <div class="h-100">
                <div class="sidebar-logo">
                    <a href="#">
                        <img src="image/daijo-logo.png" alt="LOGO">
                    </a>
                </div>
                <ul class="sidebar-nav">
                    <hr>
                    <li class="sidebar-item">
                        <a href="/" class="sidebar-link">
                            <i class="fa-solid fa-list pe-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed" data-bs-target="#admin" data-bs-toggle="collapse"
                            aria-expanded="false"><i class="fa-regular fa-user pe-2"></i>
                            Admin
                        </a>
                        <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="/list-user" class="sidebar-link">List User</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Permission</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed" data-bs-target="#business" data-bs-toggle="collapse"
                            aria-expanded="false"><i class="fa-solid fa-file-lines pe-2"></i>
                            Business
                        </a>
                        <ul id="business" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 1</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 2</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed" data-bs-target="#computer" data-bs-toggle="collapse"
                            aria-expanded="false"><i class="fa-solid fa-sliders pe-2"></i>
                            Computer
                        </a>
                        <ul id="computer" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 1</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 2</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 3</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed" data-bs-target="#production" data-bs-toggle="collapse"
                            aria-expanded="false"><i class="fa-solid fa-sliders pe-2"></i>
                            Production
                        </a>
                        <ul id="production" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 1</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 2</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Link 3</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </aside> --}}
        
        <button class="btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
            <i class="fa-solid fa-caret-right"></i>
        </button>
          
          <div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" >
            <div class="offcanvas-header">
              <h5 class="offcanvas-title" id="sidebarLabel"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
              
                    <div class="h-100">
                        <div class="sidebar-logo">
                            <a href="#">
                                <img src="image/daijo-logo.png" alt="LOGO">
                            </a>
                        </div>
                        <ul class="sidebar-nav">
                            <hr>
                            <li class="sidebar-item">
                                <a href="/" class="sidebar-link">
                                    <i class="fa-solid fa-list pe-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link collapsed" data-bs-target="#admin" data-bs-toggle="collapse"
                                    aria-expanded="false"><i class="fa-regular fa-user pe-2"></i>
                                    Admin
                                </a>
                                <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                    <li class="sidebar-item">
                                        <a href="/list-user" class="sidebar-link">List User</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Permission</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link collapsed" data-bs-target="#business" data-bs-toggle="collapse"
                                    aria-expanded="false"><i class="fa-solid fa-file-lines pe-2"></i>
                                    Business
                                </a>
                                <ul id="business" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 1</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 2</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link collapsed" data-bs-target="#computer" data-bs-toggle="collapse"
                                    aria-expanded="false"><i class="fa-solid fa-sliders pe-2"></i>
                                    Computer
                                </a>
                                <ul id="computer" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 1</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 2</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 3</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link collapsed" data-bs-target="#production" data-bs-toggle="collapse"
                                    aria-expanded="false"><i class="fa-solid fa-sliders pe-2"></i>
                                    Production
                                </a>
                                <ul id="production" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 1</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 2</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 3</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
            
            </div>
          </div>
          
        <div class="main px-3">
            <nav class="navbar navbar-expand ">
                {{-- <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button> --}}
                <h3>DISS</h3>
                <div class="navbar-collapse navbar">
                    <ul class="navbar-nav">
                        <li>
                            {{-- <a href="#" class="theme-toggle">
                                <i class="fa-regular fa-moon"></i>
                                <i class="fa-regular fa-sun"></i>
                            </a> --}}
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0" type="button">
                                <img src="image/profile.jpg" class="avatar img-fluid rounded-circle" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item">Profile</a>
                                <a href="#" class="dropdown-item">Setting</a>
                                <a href="#" class="dropdown-item">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content px-3 py-2">
                @yield('content')
            </main>
            <footer class="footer">
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
        </div> 
        
        
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script src="js/modal.js"></script>
</body>
</html>
