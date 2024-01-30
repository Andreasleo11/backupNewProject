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
                        <a href="{{ route('qaqc.report.index') }}" class="sidebar-link">Reports</a>
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
        @elseif (Auth::user()->department === "DIREKTUR")
            <li class="sidebar-item" id="sidebar-item-setting">
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
                        <a href="{{ route('superadmin.users') }}" class="sidebar-link">Users</a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('superadmin.permission') }}" class="sidebar-link">Permission</a>
                    </li>
                </ul>
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
