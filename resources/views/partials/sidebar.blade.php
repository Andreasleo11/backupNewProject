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

        @php
            $user = Auth::user();
            $department = $user->department->name;
        @endphp

        @if ($department === 'ADMIN' || $user->role->name === 'SUPERADMIN')
            <li class="sidebar-item" id="sidebar-item-admin">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#admin" aria-expanded="false" aria-controls="admin">
                    <i class='bx bx-bug'></i>
                    <span>Admin</span>
                </a>
                <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item" id="sidebar-item-users">
                        <a href="{{ route('superadmin.users') }}" class="sidebar-link">
                            <i class='bx bx-user'></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item" id="sidebar-item-amdmin">
                        <a href="{{ route('superadmin.departments') }}" class="sidebar-link">
                            <i class='bx bx-building-house'></i>
                            <span>Departments</span>
                        </a>
                    </li>
                    <li class="sidebar-item" id="sidebar-item-admin">
                        <a href="{{ route('superadmin.specifications') }}" class="sidebar-link">
                            <i class='bx bx-task'></i>
                            <span>Specifications</span>
                        </a>
                    </li>
                    <li class="sidebar-item" id="sidebar-item-admin">
                        <a href="{{ route('superadmin.users.permissions.index') }}" class="sidebar-link">
                            <i class='bx bx-lock-alt'></i>
                            <span>Users Permissions</span>
                        </a>
                    </li>

                    <li class="sidebar-item" id="sidebar-item-admin">
                        <a href="{{ route('superadmin.permissions.index') }}" class="sidebar-link">
                            <i class='bx bx-lock-alt'></i>
                            <span>Permissions</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($department === 'QA' || $department === 'QC' || $user->role->name === 'SUPERADMIN')
            <li class="sidebar-item" id="sidebar-item-qaqc">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#qaqc" aria-expanded="false" aria-controls="qaqc">
                    <i class='bx bx-badge-check'></i>
                    <span>Quality</span>
                </a>
                <ul id="qaqc" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('qaqc.report.index') }}" class="sidebar-link">
                            <i class='bx bx-file-blank'></i>
                            Verification Reports
                        </a>
                    </li>
                </ul>
                <ul id="qaqc" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('delsched.averagemonth') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Average Delivery Schedule
                        </a>
                    </li>
                </ul>

            </li>
        @endif
        @if (
            $department === 'PRODUCTION' ||
                $department === 'PE' ||
                $user->role->name === 'SUPERADMIN' ||
                $department === 'PPIC')
            <li class="sidebar-item" id="sidebar-item-production">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#productionitem" aria-expanded="false" aria-controls="purchaseRequest">
                    <i class='bx bxs-factory'></i>
                    <span>Production</span>
                </a>
                <ul id="productionitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('indexpps') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            PPS Wizard
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('capacityforecastindex') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Capacity By Forecast
                        </a>
                    </li>
                    <li class="sidebar-pe">
                        <a href="{{ route('pe.formlist') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Request Trial
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if ($department === 'BUSINESS' || $user->role->name === 'SUPERADMIN' || $department === 'PPIC')
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
        @endif

        @if ($department === 'MAINTENANCE' || $user->role->name === 'SUPERADMIN' || $department === 'PPIC')
            <li class="sidebar-item" id="sidebar-item-maintenance">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#maintenance" aria-expanded="false" aria-controls="setting">
                    <i class='bx bxs-wrench'></i>
                    <span>Maintenance</span>
                </a>
                <ul id="maintenance" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('moulddown.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Mould Repair
                        </a>
                    </li>
                </ul>
                <ul id="maintenance" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('linedown.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Line Repair
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if ($department === 'HRD' || $user->role->name === 'SUPERADMIN')
            <li class="sidebar-item" id="sidebar-item-hrd">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#hrd" aria-expanded="false" aria-controls="hrd">
                    <i class='bx bxs-user'></i>
                    <span>Human Resource</span>
                </a>
                <ul id="hrd" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item" id="sidebar-item-hrd">
                        <a href="{{ route('hrd.importantDocs.index') }}" class="sidebar-link">
                            <i class='bx bx-file-blank'></i>
                            <span>Important Documents</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if ($department === 'DIRECTOR')
            <li class="sidebar-item" id="sidebar-item-director">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#director" aria-expanded="false" aria-controls="director">
                    <i class='bx bx-badge-check'></i>
                    <span>Quality</span>
                </a>
                <ul id="director" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('director.qaqc.index') }}" class="sidebar-link">
                            <i class='bx bxs-report'></i>
                            VQC Reports
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if ($department === 'PURCHASING' || $user->role->name === 'SUPERADMIN')
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
                    <li class="sidebar-purchasing">
                        <a href="{{ route('indexds') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Delivery Schedule
                        </a>
                    </li>

                    <li class="sidebar-purchasing">
                        <a href="{{ route('fc.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Forecast Customer Master
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($department === 'ACCOUNTING' || $user->role->name === 'SUPERADMIN')
            <li class="sidebar-item" id="sidebar-item-accounting">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#accountingitem" aria-expanded="false" aria-controls="setting">
                    <i class='bx bx-dollar'></i>
                    <span>Accounting</span>
                </a>
                <ul id="accountingitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-accounting">
                        <a href="{{ route('accounting.purchase-request') }}" class="sidebar-link">
                            Approved Purchase Requests
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        {{-- PUBLIC FEATURE --}}

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

                <li class="sidebar-list">
                    <a href="{{ route('invlinelist') }}" class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Machine and Line list
                    </a>
                </li>
            </ul>
        </li>


        <li class="sidebar-item" id="sidebar-item-other">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#other" aria-expanded="false" aria-controls="other">
                <i class='bx bx-dots-horizontal-rounded'></i>
                <span>Other</span>
            </a>
            <ul id="other" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="{{ $department === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home') }}"
                        class="sidebar-link">
                        <i class='bx bx-file'></i>
                        Purchase Request
                    </a>
                </li>

                <ul id="other" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('listformadjust') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Adjust
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('discipline.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Discipline Evaluation
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('formovertime.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Overtime
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('discipline.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Discipline Evaluation
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('yayasan.table') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Discipline Evaluation Yayasan
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('indexds') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Delivery Schedule
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

                    @if ($department === 'QA' || $department === 'QC')
                        <li class="sidebar-item">
                            <a href="{{ route('qaqc.defectcategory') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Defect Categories
                            </a>
                        </li>
                    @endif

                    @if ($user->role->name === 'SUPERADMIN')
                        <li class="sidebar-item">
                            <a href="{{ route('changeemail.page') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Change Default Email QC
                            </a>
                        </li>


                        <li class="sidebar-item">
                            <a href="{{ route('pt.index') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Project Tracker
                            </a>
                        </li>
                    @endif

                    <li class="sidebar-item">
                        <a href="{{ route('monthly.budget.report.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Monthly Budget Reports
                        </a>
                    </li>

                    @if ($user->email === 'nur@daijo.co.id' || $user->role->name === 'SUPERADMIN')
                        <li class="sidebar-item">
                            <a href="{{ route('monthly.budget.summary.report.index') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Summary Monthly Budget
                            </a>
                        </li>
                    @endif
                </ul>
            </ul>
        </li>
    </ul>
</aside>
