<aside id="sidebar">
    <div class="d-flex">
        <button class="sidebar-toggle-btn" type="button">
            <i class='bx bx-grid-alt'></i>
        </button>
        <div class="sidebar-logo">
            <a href="#">Menu</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="sidebar-item" id="sidebar-item-dashboard">
            <a href="{{ route('home') }}" class="sidebar-link">
                <i class='bx bx-line-chart'></i>
                <span>Dashboard</span>
            </a>
        </li>

        @if (Auth::user()->department->name === 'PRODUCTION')
            <li class="sidebar-item" id="sidebar-item-production">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#productionitem" aria-expanded="false" aria-controls="purchaseRequest">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                    <span>Production</span>
                </a>
                <ul id="productionitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('indexpps') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            PPS Wizard
                        </a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->department->name === 'BUSINESS')
            <li class="sidebar-item" id="sidebar-item-business">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#businessitem" aria-expanded="false" aria-controls="purchaseRequest">
                    <i class='bx bx-objects-vertical-bottom'></i>
                    <span>Business</span>
                </a>
                <ul id="businessitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('indexds') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Delivery Schedule
                        </a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->department->name === 'QA' || Auth::user()->department->name === 'QC')
            <li class="sidebar-item" id="sidebar-item-qaqc">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#qaqc" aria-expanded="false" aria-controls="qaqc">
                    <i class='bx bx-badge-check'></i>
                    <span>Qaqc</span>
                </a>
                <ul id="qaqc" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('qaqc.report.index') }}" class="sidebar-link">
                            <i class='bx bx-file-blank'></i>
                            Verification Reports
                        </a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->department->name === 'HRD')
            <li class="sidebar-item" id="sidebar-item-hrd">
                <a href="{{ route('hrd.importantDocs.index') }}" class="sidebar-link">
                    <i class='bx bx-file-blank'></i>
                    <span>Important Documents</span>
                </a>
            </li>
        @elseif (Auth::user()->department->name === 'DIRECTOR')
            <li class="sidebar-item" id="sidebar-item-director">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#director" aria-expanded="false" aria-controls="director">
                    <i class='bx bx-dots-horizontal-rounded'></i>
                    <span>Qaqc</span>
                </a>
                <ul id="director" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('director.qaqc.index') }}" class="sidebar-link">
                            <i class='bx bxs-report'></i>
                            QA/QC Reports
                        </a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->department->name === 'PURCHASING')
            <li class="sidebar-item" id="sidebar-item-purchasing">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#purchasingitem" aria-expanded="false" aria-controls="setting">
                    <i class='bx bx-dollar-circle'></i>
                    <span>Purchasing</span>
                </a>
                <ul id="purchasingitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-purchasing">
                        <a href="{{ route('purchasing_home') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Forecast Prediction
                        </a>
                    </li>

                    <li class="sidebar-purchasing">
                        <a href="{{ route('reminderindex') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Reminder
                        </a>
                    </li>

                    <li class="sidebar-purchasing">
                        <a href="{{ route('purchasingrequirement.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Purchasing Requirement
                        </a>
                    </li>
                </ul>
            </li>
        @elseif (Auth::user()->department->name === 'ADMIN')
            <li class="sidebar-item" id="sidebar-item-admin">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#admin" aria-expanded="false" aria-controls="admin">
                    <i class="lni lni-protection"></i>
                    <span>Admin</span>
                </a>
                <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item" id="sidebar-item-users">
                        <a href="{{ route('superadmin.users') }}" class="sidebar-link">
                            <i class='bx bx-user'></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item" id="sidebar-item-departments">
                        <a href="{{ route('superadmin.departments') }}" class="sidebar-link">
                            <i class='bx bx-user'></i>
                            <span>Departments</span>
                        </a>
                    </li>
                </ul>
            </li>
            {{-- TODO: UNDER DEVELOPMENT --}}
            {{-- <li class="sidebar-item" id="sidebar-item-users">
                <a href="{{ route('superadmin.users') }}" class="sidebar-link">
                    <i class='bx bx-user'></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="sidebar-item" id="sidebar-item-departments">
                <a href="{{ route('superadmin.departments') }}" class="sidebar-link">
                    <i class='bx bx-user'></i>
                    <span>Departments</span>
                </a>
            </li> --}}
            {{-- <li class="sidebar-item" id="sidebar-item-permission">
                <a href="{{ route('superadmin.permissions') }}" class="sidebar-link">
                    <i class='bx bx-lock-alt'></i>
                    <span>Permissions</span>
                </a>
            </li> --}}
        @endif

        <li class="sidebar-item" id="sidebar-item-list">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#inventoryitem" aria-expanded="false" aria-controls="setting">
                <i class='bx bxs-component'></i>
                <span>Inventory</span>
            </a>
            <ul id="inventoryitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-list">
                    <a href="{{ route('inventoryfg') }}" class="sidebar-link">
                        <i class='bx bx-cube'></i>
                        Inventory FG
                    </a>
                </li>

                <li class="sidebar-list">
                    <a href="{{ route('inventorymtr') }}" class="sidebar-link">
                        <i class='bx bx-cube'></i>
                        Inventory MTR
                    </a>
                </li>
            </ul>



        <li class="sidebar-item" id="sidebar-item-setting">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#setting" aria-expanded="false" aria-controls="setting">
                <i class='bx bx-cog'></i>
                <span>Setting</span>
            </a>
            <ul id="setting" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="{{ route('indexholiday') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Holiday List
                    </a>
                </li>
            </ul>


        <li class="sidebar-item" id="sidebar-item-purchaseRequest">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#purchaseRequest" aria-expanded="false" aria-controls="purchaseRequest">
                <i class='bx bx-dots-horizontal-rounded'></i>
                <span>Other</span>
            </a>
            <ul id="purchaseRequest" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="{{ route('purchaserequest.home') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Purchase Request
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('purchaserequest.monthlyprlist') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Monthly PR
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('formcuti.home') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Form Cuti
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('formkeluar.home') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Form Keluar
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('qaqc.defectcategory') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Defect Categories
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('pt.index') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Project Tracker
                    </a>
                </li>
            </ul>
        </li>

        {{-- <li class="sidebar-item" id="sidebar-item-setting">
            <a href="{{ route('superadmin.settings') }}" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Setting</span>
            </a>
        </li> --}}
    </ul>
</aside>
