<aside id="sidebar">
    <div class="d-flex">
        <button class="sidebar-toggle-btn" type="button">
            <i class='bx bx-grid-alt'></i>
        </button>
        <div class="sidebar-logo">
            <a href="#">Menu</a>
        </div>
    </div>

    <!-- Search Input Field -->
    <div class="sidebar-search">
        <input type="text" id="sidebar-search-input" placeholder="Search...">
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-item" id="sidebar-item-dashboard">
            <a href="{{ route('home') }}" class="sidebar-link">
                <i class='bx bx-line-chart'></i>
                <span>Dashboard</span>
            </a>
        </li>

        @php
            $deptHead = Auth::user()->is_head;
        @endphp

        @if ($deptHead)
            <li class="sidebar-item" id="sidebar-item-dashboard-employee">
                <a href="{{ route('employee.dashboard') }}" class="sidebar-link">
                    <i class='bx bx-line-chart'></i>
                    <span>Dashboard Employee</span>
                </a>
            </li>
        @endif

        @php
            $user = Auth::user();
            $department = $user->department->name;
            $specification = $user->specification->name;
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
                </ul>
            </li>
        @endif

        @if ($department === 'COMPUTER' || $user->role->name === 'SUPERADMIN')
            <li class="sidebar-item" id="sidebar-item-computer">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#computer" aria-expanded="false" aria-controls="computer">
                    <i class='bx bx-desktop'></i>
                    <span>Computer</span>
                </a>
                <ul id="computer" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('mastertinta.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Stock Management
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('masterinventory.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Inventory Master
                        </a>
                    </li>

                    @if ($user->is_head === 1 || $user->role->name === 'SUPERADMIN')
                        <li class="sidebar-item">
                            <a href="{{ route('index.employeesmaster') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Employee Master
                            </a>
                        </li>
                    @endif

                    <li class="sidebar-item">
                        <a href="{{ route('maintenance.inventory.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Maintenance Inventory
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a href="{{ route('masterinventory.typeindex') }}" class="sidebar-link">
                            <i class='bx bx-cube'></i>
                            Type Inventory
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($department === 'QA' || $department === 'QC' || $department === 'BUSINESS' || $user->role->name === 'SUPERADMIN')
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
                    <li class="sidebar-item">
                        <a href="{{ route('listformadjust') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Adjust
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('qaqc.defectcategory') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Defect Categories
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
        @if (($department === 'PERSONALIA' && auth()->user()->is_head === 1) || $user->role->name === 'SUPERADMIN')
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
        @if ($specification === 'DIRECTOR')
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
                        <a href="{{ route('purchasing.evaluationsupplier.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Evaluation Supplier
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
                            <i class='bx bx-file'></i>
                            Approved PRs
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        {{-- PUBLIC FEATURE --}}

        @php
            use Illuminate\Support\Str;
            $dashboardUser = Str::contains(auth()->user()->email, 'dashboard');
        @endphp

        @if (!$dashboardUser)
            <li class="sidebar-item" id="sidebar-item-list">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#inventoryitem" aria-expanded="false" aria-controls="setting">
                    <i class='bx bxs-component'></i>
                    <span>Inventory</span>
                </a>
                <ul id="inventoryitem" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('delsched.averagemonth') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            FG Stock Monitoring
                        </a>
                    </li>

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


            <li class="sidebar-item" id="sidebar-item-list">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#storeoption" aria-expanded="false" aria-controls="setting">
                    <i class='bx bxs-component'></i>
                    <span>Store</span>
                </a>
                <ul id="storeoption" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-list">
                        <a href="{{ route('barcodeindex') }}" class="sidebar-link">
                            <i class='bx bx-cube'></i>
                            Create Barcode
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('barcode.base.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Barcode Feature
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a href="{{ route('inandout.index') }}" class="sidebar-link">
                            <i class='bx bx-cube'></i>
                            Scan Barcode
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a href="{{ route('missingbarcode.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Missing Barcode Generator
                        </a>
                    </li>

                    @if ($user && $user->name === 'raymond')
                        <li class="sidebar-list">
                            <a href="{{ route('list.barcode') }}" class="sidebar-link">
                                <i class='bx bx-file'></i>
                                Report History
                            </a>
                        </li>
                    @endif

                    <li class="sidebar-list">
                        <a href="{{ route('barcode.historytable') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Report History Table Style
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a href="{{ route('stockallbarcode') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            STOCK Item
                        </a>
                    </li>



                    <li class="sidebar-list">
                        <a href="{{ route('updated.barcode.item.position') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            List All Item Barcode
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-item" id="sidebar-item-stock-management">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#stock-management" aria-expanded="false" aria-controls="stock-management">
                    <i class='bx bxs-component'></i>
                    <span>Stock Management</span>
                </a>
                <ul id="stock-management" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="{{ route('mastertinta.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Master Stock
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
                        <a href="{{ $specification === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home') }}"
                            class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Purchase Request
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('reports.depthead.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Job Report
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('formovertime.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Overtime
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
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#employee-evaluation-nested-dropdown" aria-expanded="false"
                            aria-controls="employee-evaluation-nested-dropdown">
                            <i class='bx bx-file'></i>
                            <span>Employee Evaluation</span>
                        </a>
                        <ul id="employee-evaluation-nested-dropdown" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item">
                                <a href="{{ route('discipline.index') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    All
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('yayasan.table') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Yayasan
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('magang.table') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Magang
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('format.evaluation.year.allin') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Evaluasi Individu ALL IN
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('format.evaluation.year.yayasan') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Evaluasi Individu Yayasan
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('format.evaluation.year.magang') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Evaluasi Individu Magang
                                </a>
                            </li>
                            @if ($user->role->name === 'SUPERADMIN')
                                <li class="sidebar-item">
                                    <a href="{{ route('exportyayasan.dateinput') }}" class="sidebar-link">
                                        <i class='bx bx-file'></i>
                                        Export Yayasan Jpayroll
                                    </a>
                                </li>
                            @endif
                        </ul>
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
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#monthly-budget-nested-dropdown" aria-expanded="false"
                            aria-controls="monthly-budget-nested-dropdown">
                            <i class='bx bx-file'></i>
                            <span>Monthly Budget</span>
                        </a>
                        <ul id="monthly-budget-nested-dropdown" class="sidebar-dropdown list-unstyled collapse">
                            <li class="sidebar-item">
                                <a href="{{ route('monthly.budget.report.index') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Reports
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('monthly.budget.summary.report.index') }}" class="sidebar-link">
                                    <i class='bx bx-file'></i>
                                    Summary Reports
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('spk.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            SPK
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('formkerusakan.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Form Kerusakan / Perbaikan
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('po.dashboard') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Purchase Orders
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('waiting_purchase_orders.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Waiting PO
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ route('employee_trainings.index') }}" class="sidebar-link">
                            <i class='bx bx-file'></i>
                            Employee Training
                        </a>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
</aside>

<script>
    document.getElementById('sidebar-search-input').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const items = document.querySelectorAll('.sidebar-nav .sidebar-item');

        items.forEach(function(item) {
            const text = item.textContent || item.innerText;
            if (text.toLowerCase().includes(filter)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
