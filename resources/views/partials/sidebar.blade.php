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
            <a href="{{ route('home') }}" class="sidebar-link">
                <i class="lni lni-graph"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item" id="sidebar-item-dashboard">
            <a href="{{ route('purchaserequest.home') }}" class="sidebar-link">
                <i class='bx bx-file'></i>
                <span>Purchase Request</span>
            </a>
        </li>

        @if (Auth::user()->department->name === "Production")
            <li class="sidebar-item" id="sidebar-item-production">
                <a href="{{ route('superadmin.production') }}" class="sidebar-link">
                    <i class="lni lni-agenda"></i>
                    <span>Production</span>
                </a>
            </li>
        @elseif (Auth::user()->department->name === "Business")
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
        @elseif (Auth::user()->department->name === "QA" || Auth::user()->department->name === "QC")
            <li class="sidebar-item" id="sidebar-item-hrd">
                <a href="{{ route('qaqc.report.index') }}" class="sidebar-link">
                    <i class='bx bx-file-blank'></i>
                    <span>Verification Reports</span>
                </a>
            </li>
        @elseif (Auth::user()->department->name === "HRD")
            <li class="sidebar-item" id="sidebar-item-hrd">
                <a href="{{ route('hrd.importantDocs.index') }}" class="sidebar-link">
                    <i class='bx bx-file-blank'></i>
                    <span>Important Documents</span>
                </a>
            </li>
        @elseif (Auth::user()->department->name === "DIRECTOR")
            <li class="sidebar-item" id="sidebar-item-director">
                <a href="{{ route('director.qaqc.index') }}" class="sidebar-link">
                    <i class='bx bxs-report'></i>
                    <span>QA/QC Reports</span>
                </a>
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
                        <a href="#" class="sidebar-link">Users</a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">Permission</a>
                    </li>
                </ul>
             </li>
             <li class="sidebar-item" id="sidebar-item-admin">
                <a href="{{ route('superadmin.users') }}" class="sidebar-link">
                    <i class='bx bx-user'></i>
                    <span>Users</span>
                </a>
            </li>
             <li class="sidebar-item" id="sidebar-item-admin">
                <a href="{{ route('superadmin.permissions') }}" class="sidebar-link">
                    <i class='bx bx-lock-alt'></i>
                    <span>Permissions</span>
                </a>
            </li>
        @endif

        {{-- <li class="sidebar-item" id="sidebar-item-setting">
            <a href="{{ route('superadmin.settings') }}" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Setting</span>
            </a>
        </li> --}}
    </ul>
    {{-- <div class="sidebar-footer">
        <a href="#" class="sidebar-link" href="{{ route('logout') }}"
        onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="lni lni-exit"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div> --}}
</aside>
