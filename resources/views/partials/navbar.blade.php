<nav class="navbar px-4 py-3 shadow-sm" style="background: linear-gradient(90deg,#14385e 0%, #1e4e84 100%);">
    <h4 class="mb-0 text-white">Daijo Industrial Support System</h4>
    <div class="d-flex align-items-center gap-3">
        <div>
            <livewire:notifications.menu />
        </div>

        {{-- Profile --}}
        <div class="dropdown">
            <button class="btn text-white d-flex align-items-center gap-2" id="profileDropdown" data-bs-toggle="dropdown"
                aria-expanded="false" aria-label="Profile menu">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" class="rounded-circle"
                    alt="Profile picture" width="40" height="40">
                <span class="fw-bold text-capitalize d-none d-md-inline">{{ Auth::user()->name }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end px-3 mb-2" aria-labelledby="profileDropdown">
                <div class="text-center pt-2">
                    <span class="fs-6 fw-bold text-capitalize">{{ Auth::user()->name }}</span><br>
                    <span
                        class="fw-medium text-secondary-emphasis">{{ optional(Auth::user()->department)->name }}</span>
                </div>
                <hr>
                @if (Route::has('change.password.show'))
                    <a class="dropdown-item" href="#"
                        onclick="event.preventDefault(); document.getElementById('change-password-form').submit()">
                        <i class='bx bx-reset me-2'></i>{{ __('Change Password') }}
                    </a>
                @endif
                <a class="dropdown-item" href="#"
                    onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class='bx bx-exit me-2'></i>Logout
                </a>

                <form id="change-password-form" action="{{ route('change.password.show') }}" method="get"
                    class="d-none">@csrf</form>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </div>
</nav>
