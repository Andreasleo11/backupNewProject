@php
  use Illuminate\Support\Str;

  $user = Auth::user();
  $department = optional($user->department)->name ?? '';
  $specification = optional($user->specification)->name ?? '';
  $isSuper = optional($user->role)->name === 'SUPERADMIN';
  $deptHead = (bool) $user->is_head;

  // Helper: returns true if current route matches any pattern
  function groupOpen(array $patterns): bool {
      return request()->routeIs($patterns);
  }

  // ---- Route pattern maps for groups ----
  $adminPatterns = [
    'superadmin.users*',
    'superadmin.departments*',
    'superadmin.specifications*',
    'superadmin.users.permissions*',
    'superadmin.permissions*',
    'changeemail.page',
    'pt.*',
    'md.parts.import',
  ];

  $computerPatterns = [
    'mastertinta.*', 'masterinventory.*', 'index.employeesmaster',
    'maintenance.inventory.*', 'masterinventory.typeindex'
  ];

  $qualityPatterns = [
    'qaqc.report.*','listformadjust','qaqc.defectcategory'
  ];

  $productionPatterns = [
    'indexpps','capacityforecastindex','pe.formlist'
  ];

  $businessPatterns = ['indexds'];

  $maintenancePatterns = ['moulddown.*','linedown.*'];

  $hrdPatterns = ['hrd.importantDocs.*'];

  $purchasingPatterns = [
    'purchasing_home','purchasing.evaluationsupplier.*','reminderindex',
    'purchasingrequirement.*','indexds','fc.*'
  ];

  $accountingPatterns = ['accounting.purchase-request'];

  $inventoryPatterns = ['delsched.averagemonth','inventoryfg','inventorymtr','invlinelist'];

  $storePatterns = [
    'barcodeindex','barcode.base.*','inandout.*','missingbarcode.*','list.barcode',
    'barcode.historytable','stockallbarcode','updated.barcode.item.position',
    'delivery-notes.*','destination.*','vehicles.*'
  ];

  $stockMgmtPatterns = ['mastertinta.*'];

  $otherPatterns = [
    'director.pr.index','purchaserequest.home','daily-reports.*','overtime.*',
    'actual.import.form','formcuti.*','formkeluar.*','discipline.*','yayasan.table','magang.table',
    'format.evaluation.year.*','exportyayasan.dateinput','purchaserequest.monthlyprlist',
    'monthly.budget.report.*','monthly-budget-summary-report.*','spk.*','formkerusakan.*',
    'po.dashboard','waiting_purchase_orders.*','employee_trainings.*','daily-report.form',
    'files.*','department-expenses.*','indexds'
  ];

  $employeeEvalSubPatterns = [
    'discipline.*','yayasan.table','magang.table','format.evaluation.year.*','exportyayasan.dateinput'
  ];

  $monthlyBudgetSubPatterns = [
    'monthly.budget.report.*','monthly-budget-summary-report.*'
  ];
@endphp

<aside id="sidebar" role="navigation" aria-label="Sidebar">
  <div class="d-flex align-items-center">
    <button class="sidebar-toggle-btn" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
      <i class='bx bx-grid-alt'></i>
    </button>
    <div class="sidebar-logo">
      <a href="{{ route('home') }}">Menu</a>
    </div>

    {{-- Expand/Collapse all groups --}}
    <div class="ms-auto pe-2 d-none d-sm-block">
      <button class="btn btn-sm btn-outline-light me-1" id="btnExpandAll" type="button">Expand all</button>
      <button class="btn btn-sm btn-outline-light" id="btnCollapseAll" type="button">Collapse all</button>
    </div>
  </div>

  <!-- Search Input Field -->
  <div class="sidebar-search">
    <input type="text" id="sidebar-search-input" placeholder="Search..." aria-label="Search menu">
  </div>

  <ul class="sidebar-nav" id="sidebarNavRoot">
    {{-- Dashboard --}}
    <li class="sidebar-item">
      <x-sidebar.link
        :href="route('home')"
        icon="bx bx-line-chart"
        :active="request()->routeIs('home')">
        Dashboard
      </x-sidebar.link>
    </li>

    {{-- Dashboard Employee (Dept Head) --}}
    @if ($deptHead)
      <li class="sidebar-item" id="sidebar-item-dashboard-employee">
        <x-sidebar.link
          :href="route('employee.dashboard')"
          icon="bx bx-line-chart"
          :active="request()->routeIs('employee.dashboard')">
          Dashboard Employee
        </x-sidebar.link>
      </li>
    @endif

    {{-- Admin --}}
    @if ($isSuper)
      <x-sidebar.group id="adminGroup" icon="bx bx-bug" title="Admin" :open="groupOpen($adminPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('superadmin.users')" icon="bx bx-user" :active="request()->routeIs('superadmin.users*')">Users</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('superadmin.departments')" icon="bx bx-building-house" :active="request()->routeIs('superadmin.departments*')">Departments</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('superadmin.specifications')" icon="bx bx-task" :active="request()->routeIs('superadmin.specifications*')">Specifications</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('superadmin.users.permissions.index')" icon="bx bx-lock-alt" :active="request()->routeIs('superadmin.users.permissions*')">Users Permissions</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('superadmin.permissions.index')" icon="bx bx-lock-alt" :active="request()->routeIs('superadmin.permissions*')">Permissions</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('changeemail.page')" icon="bx bx-file" :active="request()->routeIs('changeemail.page')">Change Default Email QC</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('pt.index')" icon="bx bx-file" :active="request()->routeIs('pt.*')">Project Tracker</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('md.parts.import')" icon="bx bx-file" :active="request()->routeIs('md.parts.import')">Master Data Parts</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Computer --}}
    @if ($department === 'COMPUTER' || $isSuper)
      <x-sidebar.group id="computerGroup" icon="bx bx-desktop" title="Computer" :open="groupOpen($computerPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('mastertinta.index')" icon="bx bx-file" :active="request()->routeIs('mastertinta.*')">Stock Management</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('masterinventory.index')" icon="bx bx-file" :active="request()->routeIs('masterinventory.*')">Inventory Master</x-sidebar.link>
        </li>
        @if ($user->is_head === 1 || $isSuper)
          <li class="sidebar-item">
            <x-sidebar.link :href="route('index.employeesmaster')" icon="bx bx-file" :active="request()->routeIs('index.employeesmaster')">Employee Master</x-sidebar.link>
          </li>
        @endif
        <li class="sidebar-item">
          <x-sidebar.link :href="route('maintenance.inventory.index')" icon="bx bx-file" :active="request()->routeIs('maintenance.inventory.*')">Maintenance Inventory</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('masterinventory.typeindex')" icon="bx bx-cube" :active="request()->routeIs('masterinventory.typeindex')">Type Inventory</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Quality --}}
    @if (in_array($department, ['QA','QC','BUSINESS']) || $isSuper || $user->name === 'herlina')
      <x-sidebar.group id="qualityGroup" icon="bx bx-badge-check" title="Quality" :open="groupOpen($qualityPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('qaqc.report.index')" icon="bx bx-file-blank" :active="request()->routeIs('qaqc.report.*')">Verification Reports</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('listformadjust')" icon="bx bx-file" :active="request()->routeIs('listformadjust')">Form Adjust</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('qaqc.defectcategory')" icon="bx bx-file" :active="request()->routeIs('qaqc.defectcategory')">Defect Categories</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Production --}}
    @if (in_array($department, ['PRODUCTION','PE','PPIC']) || $isSuper)
      <x-sidebar.group id="productionGroup" icon="bx bxs-factory" title="Production" :open="groupOpen($productionPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('indexpps')" icon="bx bx-file" :active="request()->routeIs('indexpps')">PPS Wizard</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('capacityforecastindex')" icon="bx bx-file" :active="request()->routeIs('capacityforecastindex')">Capacity By Forecast</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('pe.formlist')" icon="bx bx-file" :active="request()->routeIs('pe.formlist')">Form Request Trial</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Business --}}
    @if ($department === 'BUSINESS' || $isSuper || $department === 'PPIC')
      <x-sidebar.group id="businessGroup" icon="bx bx-objects-vertical-bottom" title="Business" :open="groupOpen($businessPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery Schedule</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Maintenance --}}
    @if ($department === 'MAINTENANCE' || $isSuper || $department === 'PPIC')
      <x-sidebar.group id="maintenanceGroup" icon="bx bxs-wrench" title="Maintenance" :open="groupOpen($maintenancePatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('moulddown.index')" icon="bx bx-file" :active="request()->routeIs('moulddown.*')">Mould Repair</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('linedown.index')" icon="bx bx-file" :active="request()->routeIs('linedown.*')">Line Repair</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Human Resource --}}
    @if (($department === 'PERSONALIA' && $deptHead) || $isSuper)
      <x-sidebar.group id="humanResourceGroup" icon="bx bxs-user" title="Human Resource" :open="groupOpen($hrdPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('hrd.importantDocs.index')" icon="bx bx-file-blank" :active="request()->routeIs('hrd.importantDocs.*')">Important Documents</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Purchasing --}}
    @if ($department === 'PURCHASING' || $isSuper)
      <x-sidebar.group id="purchasingGroup" icon="bx bx-dollar-circle" title="Purchasing" :open="groupOpen($purchasingPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('purchasing_home')" icon="bx bx-file" :active="request()->routeIs('purchasing_home')">Forecast Prediction</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('purchasing.evaluationsupplier.index')" icon="bx bx-file" :active="request()->routeIs('purchasing.evaluationsupplier.*')">Evaluation Supplier</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('reminderindex')" icon="bx bx-file" :active="request()->routeIs('reminderindex')">Reminder</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('purchasingrequirement.index')" icon="bx bx-file" :active="request()->routeIs('purchasingrequirement.*')">Purchasing Requirement</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery Schedule</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('fc.index')" icon="bx bx-file" :active="request()->routeIs('fc.*')">Forecast Customer Master</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Accounting --}}
    @if ($department === 'ACCOUNTING' || $isSuper)
      <x-sidebar.group id="accountingGroup" icon="bx bx-dollar" title="Accounting" :open="groupOpen($accountingPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('accounting.purchase-request')" icon="bx bx-file" :active="request()->routeIs('accounting.purchase-request')">Approved PRs</x-sidebar.link>
        </li>
      </x-sidebar.group>
    @endif

    {{-- Inventory --}}
    <x-sidebar.group id="inventoryGroup" icon="bx bxs-component" title="Inventory" :open="groupOpen($inventoryPatterns)">
      <li class="sidebar-item">
        <x-sidebar.link :href="route('delsched.averagemonth')" icon="bx bx-file" :active="request()->routeIs('delsched.averagemonth')">FG Stock Monitoring</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('inventoryfg')" icon="bx bx-cube" :active="request()->routeIs('inventoryfg')">Inventory FG</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('inventorymtr')" icon="bx bx-cube" :active="request()->routeIs('inventorymtr')">Inventory MTR</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('invlinelist')" icon="bx bx-file" :active="request()->routeIs('invlinelist')">Machine and Line list</x-sidebar.link>
      </li>
    </x-sidebar.group>

    {{-- Store --}}
    <x-sidebar.group id="storeGroup" icon="bx bxs-component" title="Store" :open="groupOpen($storePatterns)">
      <li class="sidebar-item">
        <x-sidebar.link :href="route('barcodeindex')" icon="bx bx-cube" :active="request()->routeIs('barcodeindex')">Create Barcode</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('barcode.base.index')" icon="bx bx-file" :active="request()->routeIs('barcode.base.*')">Barcode Feature</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('inandout.index')" icon="bx bx-cube" :active="request()->routeIs('inandout.*')">Scan Barcode</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('missingbarcode.index')" icon="bx bx-file" :active="request()->routeIs('missingbarcode.*')">Missing Barcode Generator</x-sidebar.link>
      </li>
      @if ($user->name === 'raymond')
        <li class="sidebar-item">
          <x-sidebar.link :href="route('list.barcode')" icon="bx bx-file" :active="request()->routeIs('list.barcode')">Report History</x-sidebar.link>
        </li>
      @endif
      <li class="sidebar-item">
        <x-sidebar.link :href="route('barcode.historytable')" icon="bx bx-file" :active="request()->routeIs('barcode.historytable')">Report History Table Style</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('stockallbarcode')" icon="bx bx-file" :active="request()->routeIs('stockallbarcode')">STOCK Item</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('updated.barcode.item.position')" icon="bx bx-file" :active="request()->routeIs('updated.barcode.item.position')">List All Item Barcode</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('delivery-notes.index')" icon="bx bx-file" :active="request()->routeIs('delivery-notes.*')">Delivery Notes</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('destination.index')" icon="bx bx-file" :active="request()->routeIs('destination.*')">Destination</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('vehicles.index')" icon="bx bx-file" :active="request()->routeIs('vehicles.*')">Vehicles</x-sidebar.link>
      </li>
    </x-sidebar.group>

    {{-- Stock Management --}}
    <x-sidebar.group id="stockManagementGroup" icon="bx bxs-component" title="Stock Management" :open="groupOpen($stockMgmtPatterns)">
      <li class="sidebar-item">
        <x-sidebar.link :href="route('mastertinta.index')" icon="bx bx-file" :active="request()->routeIs('mastertinta.*')">Master Stock</x-sidebar.link>
      </li>
    </x-sidebar.group>

    {{-- Other --}}
    <x-sidebar.group id="otherGroup" icon="bx bx-dots-horizontal-rounded" title="Other" :open="groupOpen($otherPatterns)">
      <li class="sidebar-item">
        <x-sidebar.link :href="$specification === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home')" icon="bx bx-file" :active="request()->routeIs('director.pr.index') || request()->routeIs('purchaserequest.home')">
          Purchase Request
        </x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('daily-reports.index')" icon="bx bx-file" :active="request()->routeIs('daily-reports.*')">Job Report</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('overtime.index')" icon="bx bx-file" :active="request()->routeIs('overtime.index')">Form Overtime</x-sidebar.link>
      </li>
      @if ($isSuper)
        <li class="sidebar-item">
          <x-sidebar.link :href="route('actual.import.form')" icon="bx bx-file" :active="request()->routeIs('actual.import.form')">Import Actual Overtime</x-sidebar.link>
        </li>
      @endif
      <li class="sidebar-item">
        <x-sidebar.link :href="route('overtime.summary')" icon="bx bx-file" :active="request()->routeIs('overtime.summary')">Summary Form Overtime</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('formcuti.home')" icon="bx bx-file" :active="request()->routeIs('formcuti.*')">Form Cuti</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('formkeluar.home')" icon="bx bx-file" :active="request()->routeIs('formkeluar.*')">Form Keluar</x-sidebar.link>
      </li>

      {{-- Employee Evaluation (Sub‑Group) --}}
      <x-sidebar.group id="employeeEvaluationGroup" icon="bx bx-file" title="Employee Evaluation" :open="groupOpen($employeeEvalSubPatterns)">
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
          <x-sidebar.link :href="route('format.evaluation.year.allin')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.allin')">Evaluasi Individu ALL IN</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('format.evaluation.year.yayasan')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.yayasan')">Evaluasi Individu Yayasan</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('format.evaluation.year.magang')" icon="bx bx-file" :active="request()->routeIs('format.evaluation.year.magang')">Evaluasi Individu Magang</x-sidebar.link>
        </li>
        @if ($isSuper)
          <li class="sidebar-item">
            <x-sidebar.link :href="route('exportyayasan.dateinput')" icon="bx bx-file" :active="request()->routeIs('exportyayasan.dateinput')">Export Yayasan Jpayroll</x-sidebar.link>
          </li>
        @endif
      </x-sidebar.group>

      <li class="sidebar-item">
        <x-sidebar.link :href="route('indexds')" icon="bx bx-file" :active="request()->routeIs('indexds')">Delivery Schedule</x-sidebar.link>
      </li>

      <li class="sidebar-item">
        <x-sidebar.link :href="route('purchaserequest.monthlyprlist')" icon="bx bx-file" :active="request()->routeIs('purchaserequest.monthlyprlist')">Monthly PR</x-sidebar.link>
      </li>

      {{-- Monthly Budget (Sub‑Group) --}}
      <x-sidebar.group id="monthlyBudgetGroup" icon="bx bx-file" title="Monthly Budget" :open="groupOpen($monthlyBudgetSubPatterns)">
        <li class="sidebar-item">
          <x-sidebar.link :href="route('monthly.budget.report.index')" icon="bx bx-file" :active="request()->routeIs('monthly.budget.report.*')">Reports</x-sidebar.link>
        </li>
        <li class="sidebar-item">
          <x-sidebar.link :href="route('monthly-budget-summary-report.index')" icon="bx bx-file" :active="request()->routeIs('monthly-budget-summary-report.*')">Summary Reports</x-sidebar.link>
        </li>
      </x-sidebar.group>

      <li class="sidebar-item">
        <x-sidebar.link :href="route('spk.index')" icon="bx bx-file" :active="request()->routeIs('spk.*')">SPK</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('formkerusakan.index')" icon="bx bx-file" :active="request()->routeIs('formkerusakan.*')">Form Kerusakan / Perbaikan</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('po.dashboard')" icon="bx bx-file" :active="request()->routeIs('po.dashboard')">Purchase Orders</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('waiting_purchase_orders.index')" icon="bx bx-file" :active="request()->routeIs('waiting_purchase_orders.*')">Waiting PO</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('employee_trainings.index')" icon="bx bx-file" :active="request()->routeIs('employee_trainings.*')">Employee Training</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('daily-report.form')" icon="bx bx-file" :active="request()->routeIs('daily-report.form')">Upload Daily Report</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('files.index')" icon="bx bx-file" :active="request()->routeIs('files.*')">Files</x-sidebar.link>
      </li>
      <li class="sidebar-item">
        <x-sidebar.link :href="route('department-expenses.index')" icon="bx bx-file" :active="request()->routeIs('department-expenses.*')">Department Expenses</x-sidebar.link>
      </li>
    </x-sidebar.group>
  </ul>
</aside>

{{-- Better search: show parents if any child matches --}}
{{-- Enhanced search + expand/collapse all --}}
<script>
  (function () {
    const input = document.getElementById('sidebar-search-input');
    const navRoot = document.getElementById('sidebarNavRoot');
    const btnExpandAll = document.getElementById('btnExpandAll');
    const btnCollapseAll = document.getElementById('btnCollapseAll');
    const sidebarEl = document.getElementById('sidebar');

    const debounce = (fn, d=120) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d);} };
    const matches = (text, q) => (text||'').toLowerCase().includes(q);

    function ensureExpandedWidth() {
      if (!sidebarEl.classList.contains('expand')) sidebarEl.classList.add('expand');
      document.body.classList.add('sidebar-open');
      document.body.classList.remove('sidebar-closed');
      localStorage.setItem('daijo.sidebar.open', '1');
    }

    function filterMenu() {
      const q = (input.value || '').trim().toLowerCase();
      const groups = navRoot.querySelectorAll(':scope > li.sidebar-item, :scope > x-sidebar-group, :scope > *[data-sidebar-group]');

      // Fallback: keep legacy behaviour – hide/show direct children and expand matches
      navRoot.querySelectorAll(':scope > li.sidebar-item').forEach(group => {
        const link = group.querySelector(':scope > a.sidebar-link');
        const sub = group.querySelector(':scope > ul.sidebar-dropdown');

        if (!sub) {
          const text = (link?.innerText || '');
          group.style.display = q ? (matches(text, q) ? '' : 'none') : '';
          return;
        }

        const items = sub.querySelectorAll(':scope > li.sidebar-item');
        let anyChildVisible = false;

        items.forEach(li => {
          const a = li.querySelector('a.sidebar-link');
          const t = (a?.innerText || '');
          const show = !q || matches(t, q);
          li.style.display = show ? '' : 'none';
          anyChildVisible ||= show;
        });

        const groupText = (link?.innerText || '');
        const groupShow = !q || matches(groupText, q) || anyChildVisible;
        group.style.display = groupShow ? '' : 'none';

        if (q && anyChildVisible) {
          sub.classList.add('show');
          link?.classList?.remove?.('collapsed');
        }
      });
    }

    function expandAll() {
      ensureExpandedWidth();
      document.querySelectorAll('.sidebar-dropdown').forEach(ul => {
        if (!ul.classList.contains('show')) {
          bootstrap.Collapse.getOrCreateInstance(ul, {toggle: false}).show();
        }
      });
      document.querySelectorAll('.has-dropdown').forEach(a => a.classList.remove('collapsed'));
      localStorage.setItem('daijo.sidebar.expandAllGroups', '1');
    }

    function collapseAll() {
      document.querySelectorAll('.sidebar-dropdown').forEach(ul => {
        if (ul.classList.contains('show')) {
          bootstrap.Collapse.getOrCreateInstance(ul, {toggle: false}).hide();
        }
      });
      document.querySelectorAll('.has-dropdown').forEach(a => a.classList.add('collapsed'));
      localStorage.setItem('daijo.sidebar.expandAllGroups', '0');
    }

    input.addEventListener('input', debounce(filterMenu, 120));
    window.addEventListener('keydown', (e) => {
      if (e.key === '/' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
        e.preventDefault(); input.focus();
      }
    });
    btnExpandAll?.addEventListener('click', expandAll);
    btnCollapseAll?.addEventListener('click', collapseAll);

    if (localStorage.getItem('daijo.sidebar.expandAllGroups') === '1') {
      expandAll();
    }
  })();
</script>
