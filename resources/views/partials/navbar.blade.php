<nav class="navbar navbar-expand px-4 py-3 border d-flex align-items-center">
  <div class="me-auto">
    <h4 class="pt-1 mb-0">Daijo Industrial Support System</h4>
  </div>

  {{-- Notification --}}
  <div class="dropdown me-3">
    <button class="btn btn-success position-relative rounded-circle" id="notifDropdown"
            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
      <i class='bx bx-bell fs-5'></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" id="notifBadge">
        +99 <span class="visually-hidden">unread messages</span>
      </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notifDropdown" style="min-width: 320px;">
      <div class="p-3 border-bottom fw-semibold">Notifications</div>
      <div class="list-group list-group-flush" style="max-height: 360px; overflow:auto;">
        <div class="list-group-item small text-muted">No notifications yet.</div>
      </div>
      <a class="dropdown-item text-center small py-2" href="#">View all</a>
    </div>
  </div>

  {{-- Profile --}}
  <div class="dropdown">
    <button class="btn d-flex align-items-center gap-2" id="profileDropdown"
            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Profile menu">
      <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}"
           class="rounded-circle" alt="Profile picture" width="40" height="40">
      <span class="fw-bold text-capitalize d-none d-md-inline">{{ Auth::user()->name }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end px-3 mb-2" aria-labelledby="profileDropdown">
      <div class="text-center pt-2">
        <span class="fs-6 fw-bold text-capitalize">{{ Auth::user()->name }}</span><br>
        <span class="fw-medium text-secondary-emphasis">{{ optional(Auth::user()->department)->name }}</span>
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

      <form id="change-password-form" action="{{ route('change.password.show') }}" method="get" class="d-none">@csrf</form>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
  </div>
</nav>
