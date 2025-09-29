<aside id="sidebar" role="navigation" aria-label="Sidebar">
    <div class="sidebar-head d-flex align-items-center gap-2 px-2">
        {{-- Pin (toggle) – keep JS toggle, or switch to Livewire via wire:click --}}
        <button class="btn-icon btn-sidebar-toggle" type="button" aria-controls="sidebar" aria-expanded="false"
            aria-label="Toggle sidebar">
            <i class='bx bx-chevrons-right'></i>
        </button>

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="sidebar-brand d-flex align-items-center gap-2">
            <span class="brand-mark">DISS</span>
            <span class="brand-text">Menu</span>
        </a>

        {{-- Controls --}}
        <div class="sidebar-controls ms-auto d-none d-sm-flex">
            <button class="btn btn-icon btn-ghost-light me-1" id="btnExpandAll" type="button" data-bs-toggle="tooltip"
                data-bs-placement="bottom" title="Expand all groups">
                <i class="bx bx-expand-vertical"></i>
            </button>
            <button class="btn btn-icon btn-ghost-light" id="btnCollapseAll" type="button" data-bs-toggle="tooltip"
                data-bs-placement="bottom" title="Collapse all groups">
                <i class="bx bx-collapse-vertical"></i>
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="sidebar-search">
        <input type="text" id="sidebar-search-input" placeholder="Search..." aria-label="Search menu">
    </div>

    <ul class="sidebar-nav" id="sidebarNavRoot">
        {{-- Dashboard --}}
        <li class="sidebar-item">
            <x-sidebar.link :href="route('home')" icon="bx bx-line-chart" :active="request()->routeIs('home')">
                Dashboard
            </x-sidebar.link>
        </li>

        {{-- Dashboard Employee (Dept Head) --}}
        @if ($deptHead)
            <li class="sidebar-item" id="sidebar-item-dashboard-employee">
                <x-sidebar.link :href="route('employee.dashboard')" icon="bx bx-line-chart" :active="request()->routeIs('employee.dashboard')">
                    Dashboard Employee
                </x-sidebar.link>
            </li>
        @endif

        {{-- Admin --}}
        @if ($isSuper)
            <x-sidebar.group id="adminGroup" icon="bx bx-bug" title="Admin" :open="$groupOpen['adminGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('superadmin.users')" icon="bx bx-user" :active="request()->routeIs('superadmin.users*')">Users</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('superadmin.departments')" icon="bx bx-building-house"
                        :active="request()->routeIs('superadmin.departments*')">Departments</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('superadmin.specifications')" icon="bx bx-task"
                        :active="request()->routeIs('superadmin.specifications*')">Specifications</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('superadmin.users.permissions.index')" icon="bx bx-lock-alt" :active="request()->routeIs('superadmin.users.permissions*')">Users
                        Permissions</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('superadmin.permissions.index')" icon="bx bx-lock-alt"
                        :active="request()->routeIs('superadmin.permissions*')">Permissions</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('changeemail.page')" icon="bx bx-file" :active="request()->routeIs('changeemail.page')">Change Default
                        Email QC</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('pt.index')" icon="bx bx-file" :active="request()->routeIs('pt.*')">Project
                        Tracker</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('md.parts.import')" icon="bx bx-file" :active="request()->routeIs('md.parts.import')">Master Data
                        Parts</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Computer --}}
        @if ($department === 'COMPUTER' || $isSuper)
            <x-sidebar.group id="computerGroup" icon="bx bx-desktop" title="Computer" :open="$groupOpen['computerGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('mastertinta.index')" icon="bx bx-file" :active="request()->routeIs('mastertinta.*')">Stock
                        Management</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('masterinventory.index')" icon="bx bx-file" :active="request()->routeIs('masterinventory.*')">Inventory
                        Master</x-sidebar.link>
                </li>
                @if ($deptHead || $isSuper)
                    <li class="sidebar-item">
                        <x-sidebar.link :href="route('index.employeesmaster')" icon="bx bx-file" :active="request()->routeIs('index.employeesmaster')">Employee
                            Master</x-sidebar.link>
                    </li>
                @endif
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('maintenance.inventory.index')" icon="bx bx-file" :active="request()->routeIs('maintenance.inventory.*')">Maintenance
                        Inventory</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('masterinventory.typeindex')" icon="bx bx-cube" :active="request()->routeIs('masterinventory.typeindex')">Type
                        Inventory</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Quality --}}
        @if (in_array($department, ['QA', 'QC', 'BUSINESS']) || $isSuper || $user->name === 'herlina')
            <x-sidebar.group id="qualityGroup" icon="bx bx-badge-check" title="Quality" :open="$groupOpen['qualityGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('qaqc.report.index')" icon="bx bx-file-blank" :active="request()->routeIs('qaqc.report.*')">Verification
                        Reports</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('listformadjust')" icon="bx bx-file" :active="request()->routeIs('listformadjust')">Form
                        Adjust</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('qaqc.defectcategory')" icon="bx bx-file" :active="request()->routeIs('qaqc.defectcategory')">Defect
                        Categories</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Production --}}
        @if (in_array($department, ['PRODUCTION', 'PE', 'PPIC']) || $isSuper)
            <x-sidebar.group id="productionGroup" icon="bx bxs-factory" title="Production" :open="$groupOpen['productionGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('indexpps')" icon="bx bx-file" :active="request()->routeIs('indexpps')">PPS
                        Wizard</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('capacityforecastindex')" icon="bx bx-file" :active="request()->routeIs('capacityforecastindex')">Capacity By
                        Forecast</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('pe.formlist')" icon="bx bx-file" :active="request()->routeIs('pe.formlist')">Form Request
                        Trial</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Business --}}
        @if ($department === 'BUSINESS' || $isSuper || $department === 'PPIC')
            <x-sidebar.group id="businessGroup" icon="bx bx-objects-vertical-bottom" title="Business"
                :open="$groupOpen['businessGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery
                        Schedule</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Maintenance --}}
        @if ($department === 'MAINTENANCE' || $isSuper || $department === 'PPIC')
            <x-sidebar.group id="maintenanceGroup" icon="bx bxs-wrench" title="Maintenance" :open="$groupOpen['maintenanceGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('moulddown.index')" icon="bx bx-file" :active="request()->routeIs('moulddown.*')">Mould
                        Repair</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('linedown.index')" icon="bx bx-file" :active="request()->routeIs('linedown.*')">Line
                        Repair</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Human Resource --}}
        @if (($department === 'PERSONALIA' && $deptHead) || $isSuper)
            <x-sidebar.group id="humanResourceGroup" icon="bx bxs-user" title="Human Resource" :open="$groupOpen['humanResourceGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('hrd.importantDocs.index')" icon="bx bx-file-blank" :active="request()->routeIs('hrd.importantDocs.*')">Important
                        Documents</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Purchasing --}}
        @if ($department === 'PURCHASING' || $isSuper)
            <x-sidebar.group id="purchasingGroup" icon="bx bx-dollar-circle" title="Purchasing" :open="$groupOpen['purchasingGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('purchasing_home')" icon="bx bx-file" :active="request()->routeIs('purchasing_home')">Forecast
                        Prediction</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('purchasing.evaluationsupplier.index')" icon="bx bx-file" :active="request()->routeIs('purchasing.evaluationsupplier.*')">Evaluation
                        Supplier</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('reminderindex')" icon="bx bx-file" :active="request()->routeIs('reminderindex')">Reminder</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('purchasingrequirement.index')" icon="bx bx-file" :active="request()->routeIs('purchasingrequirement.*')">Purchasing
                        Requirement</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery
                        Schedule</x-sidebar.link>
                </li>
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('fc.index')" icon="bx bx-file" :active="request()->routeIs('fc.*')">Forecast Customer
                        Master</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Accounting --}}
        @if ($department === 'ACCOUNTING' || $isSuper)
            <x-sidebar.group id="accountingGroup" icon="bx bx-dollar" title="Accounting" :open="$groupOpen['accountingGroup'] ?? false">
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('accounting.purchase-request')" icon="bx bx-file" :active="request()->routeIs('accounting.purchase-request')">Approved
                        PRs</x-sidebar.link>
                </li>
            </x-sidebar.group>
        @endif

        {{-- Inventory --}}
        <x-sidebar.group id="inventoryGroup" icon="bx bxs-component" title="Inventory" :open="$groupOpen['inventoryGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="route('delsched.averagemonth')" icon="bx bx-file" :active="request()->routeIs('delsched.averagemonth')">FG Stock
                    Monitoring</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('inventoryfg')" icon="bx bx-cube" :active="request()->routeIs('inventoryfg')">Inventory
                    FG</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('inventorymtr')" icon="bx bx-cube" :active="request()->routeIs('inventorymtr')">Inventory
                    MTR</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('invlinelist')" icon="bx bx-file" :active="request()->routeIs('invlinelist')">Machine and Line
                    list</x-sidebar.link>
            </li>
        </x-sidebar.group>

        {{-- Store --}}
        <x-sidebar.group id="storeGroup" icon="bx bxs-component" title="Store" :open="$groupOpen['storeGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="route('barcodeindex')" icon="bx bx-cube" :active="request()->routeIs('barcodeindex')">Create
                    Barcode</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('barcode.base.index')" icon="bx bx-file" :active="request()->routeIs('barcode.base.*')">Barcode
                    Feature</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('inandout.index')" icon="bx bx-cube" :active="request()->routeIs('inandout.*')">Scan
                    Barcode</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('missingbarcode.index')" icon="bx bx-file" :active="request()->routeIs('missingbarcode.*')">Missing Barcode
                    Generator</x-sidebar.link>
            </li>
            @if ($user->name === 'raymond')
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('list.barcode')" icon="bx bx-file" :active="request()->routeIs('list.barcode')">Report
                        History</x-sidebar.link>
                </li>
            @endif
            <li class="sidebar-item">
                <x-sidebar.link :href="route('barcode.historytable')" icon="bx bx-file" :active="request()->routeIs('barcode.historytable')">Report History
                    Table Style</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('stockallbarcode')" icon="bx bx-file" :active="request()->routeIs('stockallbarcode')">STOCK
                    Item</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('updated.barcode.item.position')" icon="bx bx-file" :active="request()->routeIs('updated.barcode.item.position')">List All Item
                    Barcode</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('delivery-notes.index')" icon="bx bx-file" :active="request()->routeIs('delivery-notes.*')">Delivery
                    Notes</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('destination.index')" icon="bx bx-file" :active="request()->routeIs('destination.*')">Destination</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('vehicles.index')" icon="bx bx-file" :active="request()->routeIs('vehicles.*')">Vehicles</x-sidebar.link>
            </li>
        </x-sidebar.group>

        {{-- Stock Management --}}
        <x-sidebar.group id="stockManagementGroup" icon="bx bxs-component" title="Stock Management"
            :open="$groupOpen['stockManagementGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="route('mastertinta.index')" icon="bx bx-file" :active="request()->routeIs('mastertinta.*')">Master
                    Stock</x-sidebar.link>
            </li>
        </x-sidebar.group>

        {{-- Monthly Budget --}}
        <x-sidebar.group id="monthlyBudgetGroup" icon="bx bx-file" title="Monthly Budget" :open="$groupOpen['monthlyBudgetGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="route('monthly.budget.report.index')" icon="bx bx-file" :active="request()->routeIs('monthly.budget.report.*')">Reports</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('monthly-budget-summary-report.index')" icon="bx bx-file" :active="request()->routeIs('monthly-budget-summary-report.*')">Summary
                    Reports</x-sidebar.link>
            </li>
        </x-sidebar.group>

        {{-- Employee Evaluation --}}
        <x-sidebar.group id="employeeEvaluationGroup" icon="bx bx-file" title="Employee Evaluation"
            :open="$groupOpen['employeeEvaluationGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="route('discipline.index')" icon="bx bx-file" :active="request()->routeIs('discipline.*')">All</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('yayasan.table')" icon="bx bx-file" :active="request()->routeIs('yayasan.table')">Yayasan</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('magang.table')" icon="bx bx-file" :active="request()->routeIs('magang.table')">Magang</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('format.evaluation.year.allin')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.allin')">Evaluasi Individu
                    All IN</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('format.evaluation.year.yayasan')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.yayasan')">Evaluasi Individu
                    Yayasan</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('format.evaluation.year.magang')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.magang')">Evaluasi Individu
                    Magang</x-sidebar.link>
            </li>
            @if ($isSuper)
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('exportyayasan.dateinput')" icon="bx bx-file" :active="request()->routeIs('exportyayasan.dateinput')">Export Yayasan
                        Jpayroll</x-sidebar.link>
                </li>
            @endif
        </x-sidebar.group>

        {{-- Other --}}
        <x-sidebar.group id="otherGroup" icon="bx bx-dots-horizontal-rounded" title="Other" :open="$groupOpen['otherGroup'] ?? false">
            <li class="sidebar-item">
                <x-sidebar.link :href="$specification === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home')" icon="bx bx-file" :active="request()->routeIs('director.pr.index') || request()->routeIs('purchaserequest.home')">
                    Purchase Request
                </x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('daily-reports.index')" icon="bx bx-file" :active="request()->routeIs('daily-reports.*')">Job
                    Report</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('overtime.index')" icon="bx bx-file" :active="request()->routeIs('overtime.index')">Form
                    Overtime</x-sidebar.link>
            </li>
            @if ($isSuper)
                <li class="sidebar-item">
                    <x-sidebar.link :href="route('actual.import.form')" icon="bx bx-file" :active="request()->routeIs('actual.import.form')">Import Actual
                        Overtime</x-sidebar.link>
                </li>
            @endif
            <li class="sidebar-item">
                <x-sidebar.link :href="route('overtime.summary')" icon="bx bx-file" :active="request()->routeIs('overtime.summary')">Summary Form
                    Overtime</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('formcuti.home')" icon="bx bx-file" :active="request()->routeIs('formcuti.*')">Form
                    Cuti</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('formkeluar.home')" icon="bx bx-file" :active="request()->routeIs('formkeluar.*')">Form
                    Keluar</x-sidebar.link>
            </li>

            <li class="sidebar-item">
                <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery
                    Schedule</x-sidebar.link>
            </li>

            <li class="sidebar-item">
                <x-sidebar.link :href="route('purchaserequest.monthlyprlist')" icon="bx bx-file" :active="request()->routeIs('purchaserequest.monthlyprlist')">Monthly
                    PR</x-sidebar.link>
            </li>

            <li class="sidebar-item">
                <x-sidebar.link :href="route('spk.index')" icon="bx bx-file" :active="request()->routeIs('spk.*')">SPK</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('formkerusakan.index')" icon="bx bx-file" :active="request()->routeIs('formkerusakan.*')">Form
                    Kerusakan/Perbaikan</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('po.dashboard')" icon="bx bx-file" :active="request()->routeIs('po.dashboard')">Purchase
                    Orders</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('waiting_purchase_orders.index')" icon="bx bx-file" :active="request()->routeIs('waiting_purchase_orders.*')">Waiting
                    PO</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('employee_trainings.index')" icon="bx bx-file" :active="request()->routeIs('employee_trainings.*')">Employee
                    Training</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('daily-report.form')" icon="bx bx-file" :active="request()->routeIs('daily-report.form')">Upload Daily
                    Report</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('files.index')" icon="bx bx-file" :active="request()->routeIs('files.*')">Files</x-sidebar.link>
            </li>
            <li class="sidebar-item">
                <x-sidebar.link :href="route('department-expenses.index')" icon="bx bx-file" :active="request()->routeIs('department-expenses.*')">Department
                    Expenses</x-sidebar.link>
            </li>
        </x-sidebar.group>
    </ul>

    <!-- Smart flyout container (one per page) -->
    <div id="sidebar-flyout" class="sidebar-flyout" role="dialog" aria-modal="false" aria-hidden="true">
        <div class="sidebar-flyout-header d-flex align-items-center gap-2 px-3 py-2">
            <i class="" aria-hidden="true"></i>
            <span class="title fw-semibold"></span>
        </div>
        <div class="sidebar-flyout-body py-1"></div>
    </div>

    @push('extraJs')
        <script type="module">
            (function initSidebar() {
                const sidebarEl = document.getElementById('sidebar');
                if (!sidebarEl || sidebarEl.dataset.initialized === '1') return;
                sidebarEl.dataset.initialized = '1';

                const toggleBtn = document.querySelector('.btn-sidebar-toggle');
                const input = document.getElementById('sidebar-search-input');
                const navRoot = document.getElementById('sidebarNavRoot');
                const btnExpandAll = document.getElementById('btnExpandAll');
                const btnCollapseAll = document.getElementById('btnCollapseAll');

                const debounce = (fn, d = 120) => {
                    let t;
                    return (...a) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn(...a), d);
                    }
                };
                const matches = (text, q) => (text || '').toLowerCase().includes(q);

                function applyExpandedState(expanded) {
                    sidebarEl.classList.toggle('expand', expanded);
                    document.body.classList.toggle('sidebar-open', expanded);
                    document.body.classList.toggle('sidebar-closed', !expanded);
                    toggleBtn?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    localStorage.setItem('daijo.sidebar.open', expanded ? '1' : '0');
                }
                toggleBtn?.addEventListener('click', () => applyExpandedState(!sidebarEl.classList.contains(
                    'expand')));
                applyExpandedState(localStorage.getItem('daijo.sidebar.open') === '1');

                function filterMenu() {
                    const q = (input?.value || '').trim().toLowerCase();
                    navRoot?.querySelectorAll(':scope > li.sidebar-item').forEach(group => {
                        const link = group.querySelector(':scope > a.sidebar-link');
                        const sub = group.querySelector(':scope > ul.sidebar-dropdown');

                        if (!sub) {
                            group.style.display = q ? (matches(link?.innerText, q) ? '' : 'none') : '';
                            return;
                        }
                        let anyChildVisible = false;
                        sub.querySelectorAll(':scope > li.sidebar-item').forEach(li => {
                            const a = li.querySelector('a.sidebar-link');
                            const show = !q || matches(a?.innerText, q);
                            li.style.display = show ? '' : 'none';
                            anyChildVisible ||= show;
                        });
                        const groupShow = !q || matches(link?.innerText, q) || anyChildVisible;
                        group.style.display = groupShow ? '' : 'none';
                        if (q && anyChildVisible) {
                            sub.classList.add('show');
                            link?.classList?.remove?.('collapsed');
                        }
                    });
                }

                function expandAll() {
                    applyExpandedState(true);
                    document.querySelectorAll('.sidebar-dropdown').forEach(ul => {
                        bootstrap.Collapse.getOrCreateInstance(ul, {
                            toggle: false
                        }).show();
                    });
                    document.querySelectorAll('.has-dropdown').forEach(a => a.classList.remove('collapsed'));
                    localStorage.setItem('daijo.sidebar.expandAllGroups', '1');
                }

                function collapseAll() {
                    document.querySelectorAll('.sidebar-dropdown').forEach(ul => {
                        bootstrap.Collapse.getOrCreateInstance(ul, {
                            toggle: false
                        }).hide();
                    });
                    document.querySelectorAll('.has-dropdown').forEach(a => a.classList.add('collapsed'));
                    localStorage.setItem('daijo.sidebar.expandAllGroups', '0');
                }

                input?.addEventListener('input', debounce(filterMenu, 120));
                btnExpandAll?.addEventListener('click', expandAll);
                btnCollapseAll?.addEventListener('click', collapseAll);

                if (localStorage.getItem('daijo.sidebar.expandAllGroups') === '1') {
                    expandAll();
                }

                // Tooltips
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    window.bootstrap?.Tooltip && window.bootstrap.Tooltip.getOrCreateInstance(el);
                });

                // Quick-focus search
                window.addEventListener('keydown', e => {
                    if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                        e.preventDefault();
                        input?.focus();
                    }
                });

                // Hover-to-expand (rail pattern)
                const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
                let hoverOpenTimer = null,
                    hoverCloseTimer = null;
                const OPEN_DELAY = 120,
                    CLOSE_DELAY = 180;

                function setHoverOpen(on) {
                    if (sidebarEl.classList.contains('expand')) return; // pinned overrides
                    sidebarEl.classList.toggle('hover-open', on);
                }
                if (canHover) {
                    sidebarEl.addEventListener('mouseenter', () => {
                        clearTimeout(hoverCloseTimer);
                        hoverOpenTimer = setTimeout(() => setHoverOpen(true), OPEN_DELAY);
                    });
                    sidebarEl.addEventListener('mouseleave', () => {
                        clearTimeout(hoverOpenTimer);
                        hoverCloseTimer = setTimeout(() => setHoverOpen(false), CLOSE_DELAY);
                    });
                }
            })();

            // Re-init after Livewire DOM updates
            window.addEventListener('livewire:load', () => setTimeout(() => window.dispatchEvent(new Event(
                'sidebar:init')), 0));
            document.addEventListener('livewire:navigated', () => setTimeout(() => window.dispatchEvent(
                new Event('sidebar:init')), 0));
            window.addEventListener('sidebar:init', () => {
                // call the initializer again safely (it no-ops if already init)
                try {
                    (0, eval)("(function(){})");
                } catch (_) {}
                // you can simply call initSidebar() again if you move it to window.initSidebar
            });
        </script>

        <script type="module">
            (function smartFlyout() {
                const sidebar = document.getElementById('sidebar');
                const flyout = document.getElementById('sidebar-flyout');
                if (!sidebar || !flyout) return;

                const bodyEl = flyout.querySelector('.sidebar-flyout-body');
                const headIco = flyout.querySelector('.sidebar-flyout-header i');
                const headTxt = flyout.querySelector('.sidebar-flyout-header .title');

                // Only on devices that support hover, and only when rail is collapsed (not pinned)
                const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

                let openTimer = null,
                    closeTimer = null,
                    currentAnchor = null;
                const OPEN_DELAY = 110;
                const CLOSE_DELAY = 160;
                const GAP = 8; // gap between rail and flyout

                function isRailCollapsed() {
                    return !sidebar.classList.contains('expand');
                }

                function cleanupClone(root) {
                    // Make any nested groups fully visible and non-collapsing inside the flyout
                    root.querySelectorAll('[data-bs-toggle="collapse"]').forEach(a => {
                        a.removeAttribute('data-bs-toggle');
                        a.removeAttribute('data-bs-target');
                        a.removeAttribute('aria-expanded');
                        a.removeAttribute('aria-controls');
                        a.classList.remove('collapsed');
                    });
                    root.querySelectorAll('.collapse').forEach(ul => {
                        ul.classList.remove('collapse');
                        ul.classList.add('show');
                        ul.removeAttribute('style');
                        ul.removeAttribute('id'); // avoid duplicate IDs
                        ul.dataset.bsParent = '';
                    });
                }

                function setPosition(anchorRect) {
                    // position the flyout next to the rail and clamp to viewport
                    const railWidth = parseFloat(
                        getComputedStyle(document.documentElement).getPropertyValue('--sidebar-collapsed')
                    ) || 70;
                    const GAP = 8;

                    // Anchor to the rail
                    flyout.style.left = `${railWidth + GAP}px`;

                    // temporarily show to measure
                    flyout.style.visibility = 'hidden';
                    flyout.classList.add('visible');
                    const fh = flyout.getBoundingClientRect().height;
                    flyout.classList.remove('visible');
                    flyout.style.visibility = '';

                    const topDesired = Math.round(anchorRect.top - 8);
                    const topMin = 8;
                    const topMax = window.innerHeight - fh - 8;
                    const top = Math.max(topMin, Math.min(topDesired, topMax));
                    flyout.style.top = `${top}px`;

                    // arrow vertical position
                    const arrowTop = Math.max(14, Math.min(anchorRect.top - top + 14, fh - 20));
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    flyout.style.setProperty('--arrow-top', `${arrowTop}px`);
                    // CSS uses ::before at fixed 14px; adjust if you want dynamic arrow
                }

                function openFlyoutFor(anchor) {
                    const li = anchor.closest('li.sidebar-item');
                    const sub = li?.querySelector(':scope > ul.sidebar-dropdown');
                    if (!sub) return;

                    // header
                    const icon = anchor.querySelector('i')?.className || '';
                    const text = anchor.querySelector('span')?.textContent?.trim() || anchor.title || '';
                    headIco.className = icon;
                    headTxt.textContent = text;

                    // body
                    bodyEl.innerHTML = '';
                    const clone = sub.cloneNode(true);
                    cleanupClone(clone);
                    // Use the children of the clone as content (strip outer UL styling if you want)
                    bodyEl.appendChild(clone);

                    // position & show
                    anchor.classList.add('flyout-anchor');
                    setPosition(anchor.getBoundingClientRect());
                    flyout.classList.add('visible');
                    flyout.setAttribute('aria-hidden', 'false');
                    currentAnchor = anchor;
                }

                function closeFlyout() {
                    if (currentAnchor) currentAnchor.classList.remove('flyout-anchor');
                    flyout.classList.remove('visible');
                    flyout.setAttribute('aria-hidden', 'true');
                    currentAnchor = null;
                }

                function scheduleOpen(anchor) {
                    clearTimeout(openTimer);
                    clearTimeout(closeTimer);
                    openTimer = setTimeout(() => openFlyoutFor(anchor), OPEN_DELAY);
                }

                function scheduleClose() {
                    clearTimeout(openTimer);
                    clearTimeout(closeTimer);
                    closeTimer = setTimeout(closeFlyout, CLOSE_DELAY);
                }

                // Hover on group headers when rail is collapsed
                function onEnterAnchor(e) {
                    const a = e.currentTarget;
                    if (!canHover || !isRailCollapsed()) return;
                    scheduleOpen(a);
                }

                function onLeaveAnchor() {
                    if (!canHover || !isRailCollapsed()) return;
                    scheduleClose();
                }

                // Keep open while mouse is inside the flyout
                flyout.addEventListener('mouseenter', () => {
                    clearTimeout(closeTimer);
                });
                flyout.addEventListener('mouseleave', () => {
                    if (isRailCollapsed()) scheduleClose();
                });

                // Clicking a group header while collapsed should open flyout instead of collapsing in-rail
                sidebar.addEventListener('click', (e) => {
                    const a = e.target.closest('a.has-dropdown');
                    if (!a || !isRailCollapsed()) return;
                    e.preventDefault();
                    clearTimeout(openTimer);
                    clearTimeout(closeTimer);
                    if (currentAnchor === a && flyout.classList.contains('visible')) {
                        closeFlyout();
                    } else {
                        openFlyoutFor(a);
                    }
                });

                // Attach hover listeners to all group headers
                function bindAnchors() {
                    sidebar.querySelectorAll('a.has-dropdown').forEach(a => {
                        a.removeEventListener('mouseenter', onEnterAnchor);
                        a.removeEventListener('mouseleave', onLeaveAnchor);
                        a.addEventListener('mouseenter', onEnterAnchor);
                        a.addEventListener('mouseleave', onLeaveAnchor);
                    });
                }
                bindAnchors();

                // Close on outside click (when collapsed)
                document.addEventListener('mousedown', (e) => {
                    if (!isRailCollapsed()) return;
                    if (flyout.contains(e.target)) return;
                    if (sidebar.contains(e.target)) return;
                    closeFlyout();
                });

                // Reposition on resize/scroll
                window.addEventListener('resize', () => {
                    if (!currentAnchor || !flyout.classList.contains('visible')) return;
                    setPosition(currentAnchor.getBoundingClientRect());
                });
                window.addEventListener('scroll', () => {
                    if (!currentAnchor || !flyout.classList.contains('visible')) return;
                    // Ignore scrolls coming from the flyout itself
                    if (e.target === flyout || flyout.contains(e.target)) return;
                    setPosition(currentAnchor.getBoundingClientRect());
                }, true);

                // ESC to close
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeFlyout();
                });

                // Re-bind after Livewire morphs
                document.addEventListener('livewire:navigated', bindAnchors);
            })();
        </script>
    @endpush
</aside>
