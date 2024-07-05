<nav class="navbar navbar-expand px-4 py-3 border d-flex">
    <!--Header-->
    <div class="me-auto">
        <h4 class="pt-1">Daijo Industrial Support System</h4>
    </div>

    {{-- <!--Notification-->
    <div class="me-5">
        <button type="button" class="btn btn-success position-relative rounded-circle me-2">
            <i class='bx bx-bell'></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                +99 <span class="visually-hidden">unread messages</span>
            </span>
        </button>
    </div> --}}


    <!-- Profile Icon -->
    <div class="row align-items-center" data-bs-toggle="dropdown" type="button">
        <div class="col">
            <div class="navbar navbar-collapse">
                <a href="#" class="nav-icon pe-md-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}"
                        class="img-fluid rounded-circle" alt="profilePicture" style="width: 50px">
                </a>
                <div class="dropdown-menu dropdown-menu-end px-3 mb-2">
                    <div class="text-center">
                        <span class="fs-5 fw-bold text-capitalize">{{ Auth::user()->name }} </span> <br>
                        <span class="fw-medium text-secondary-emphasis">{{ Auth::user()->department->name }}</span>
                    </div>
                    <hr>
                    <!--
                        <a href="#" class="dropdown-item">Profile</a>
                        <a href="#" class="dropdown-item">Setting</a>
                    -->
                    @if (Route::has('change.password.show'))
                        <a class="dropdown-item" href="#"
                            onclick="event.preventDefault(); document.getElementById('change-password-form').submit()">
                            <i class='bx bx-reset me-2'></i>
                            {{ __('Change Password') }}
                        </a>
                    @endif

                    <a href="#" class="dropdown-item"
                        onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class='bx bx-exit me-2'></i>
                        Logout
                    </a>

                    <form id="change-password-form" action="{{ route('change.password.show') }}" method="get"
                        class="d-none"> @csrf </form>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"> @csrf </form>
                </div>
            </div>
        </div>
        <div class="col">
            <span class="fw-bold text-capitalize">{{ Auth::user()->name }} </span>
        </div>
    </div>

    <span class="mx-2 d-lg-inline text-gray-600 fw-medium">
    </span>
</nav>
