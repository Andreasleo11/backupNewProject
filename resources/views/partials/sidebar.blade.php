@php
  $user = Auth::user();
  $department = optional($user->department)->name ?? '';
  $specification = optional($user->specification)->name ?? '';
  $isSuper = optional($user->role)->name === 'SUPERADMIN';
  $deptHead = Auth::user()->is_head;
@endphp

<aside id="sidebar" role="navigation" aria-label="Sidebar">
  <div class="d-flex align-items-center">
    <button class="sidebar-toggle-btn" type="button" aria-control="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
      <i class='bx bx-grid-alt'></i>
    </button>
    <div class="sidebar-logo">
      <a href="{{ route('home') }}">Menu</a>
    </div>
  </div>

  <!-- Search Input Field -->
  <div class="sidebar-search">
    <input type="text" id="sidebar-search-input" placeholder="Search..." aria-label="Search menu">
  </div>

  <ul class="sidebar-nav" id="sidebarNavRoot">
    <li class="sidebar-item">
      <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class='bx bx-line-chart'></i>
        <span>Dashboard</span>
      </a>
    </li>

    @if ($deptHead)
      <li class="sidebar-item" id="sidebar-item-dashboard-employee">
        <a href="{{ route('employee.dashboard') }}" class="sidebar-link">
          <i class='bx bx-line-chart'></i>
          Dashboard Employee
        </a>
      </li>
    @endif

    <!-- Admin -->
    @if ($isSuper)
      <li class="sidebar-item" id="sidebar-item-admin">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#adminGroup" aria-expanded="false" aria-controls="adminGroup">
          <i class='bx bx-bug'></i>
          <span>Admin</span>
        </a>
        <ul id="adminGroup" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
          <li class="sidebar-item">
            <a href="{{ route('superadmin.users') }}" class="sidebar-link {{ request()->routeIs('superadmin.users') ? 'active' : '' }}">
              <i class='bx bx-user'></i>
              Users
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('superadmin.departments') }}" class="sidebar-link">
              <i class='bx bx-building-house'></i>
              Departments
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('superadmin.specifications') }}" class="sidebar-link">
              <i class='bx bx-task'></i>
              Specifications
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('superadmin.users.permissions.index') }}" class="sidebar-link">
              <i class='bx bx-lock-alt'></i>
              Users Permissions
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('superadmin.permissions.index') }}" class="sidebar-link">
              <i class='bx bx-lock-alt'></i>
              Permissions
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('changeemail.page') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Default Email QC
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('pt.index') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
             Project Tracker
            </a>
          </li>
          <li class="sidebar-item">
            <a href="{{ route('md.parts.import') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Master Data Parts
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Computer -->
    @if ($department === 'COMPUTER' || $isSuper)
      <li class="sidebar-item" id="sidebar-item-computer">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#computerGroup" aria-expanded="false" aria-controls="computerGroup">
          <i class='bx bx-desktop'></i>
          <span>Computer</span>
        </a>
        <ul id="computerGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
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

          @if ($user->is_head === 1 || $isSuper)
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

    <!-- Quality -->
    @if (
        $department === 'QA' ||
            $department === 'QC' ||
            $department === 'BUSINESS' ||
            $isSuper || 
            $user->name === 'herlina')
      <li class="sidebar-item" id="sidebar-item-qaqc">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#qualityGroup" aria-expanded="false" aria-controls="qualityGroup">
          <i class='bx bx-badge-check'></i>
          <span>Quality</span>
        </a>
        <ul id="qualityGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
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

    <!-- Production -->
    @if (
        $department === 'PRODUCTION' ||
            $department === 'PE' ||
            $isSuper ||
            $department === 'PPIC')
      <li class="sidebar-item" id="sidebar-item-production">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#productionGroup" aria-expanded="false" aria-controls="productionGroup">
          <i class='bx bxs-factory'></i>
          <span>Production</span>
        </a>
        <ul id="productionGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
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

    <!-- Business -->
    @if ($department === 'BUSINESS' || $isSuper || $department === 'PPIC')
      <li class="sidebar-item" id="sidebar-item-business">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#businessGroup" aria-expanded="false" aria-controls="businessGroup">
          <i class='bx bx-objects-vertical-bottom'></i>
          <span>Business</span>
        </a>
        <ul id="businessGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
          <li class="sidebar-item">
            <a href="{{ route('indexds') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Delivery Schedule
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Maintenance -->
    @if ($department === 'MAINTENANCE' || $isSuper || $department === 'PPIC')
      <li class="sidebar-item" id="sidebar-item-maintenance">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#maintenanceGroup" aria-expanded="false" aria-controls="maintenanceGroup">
          <i class='bx bxs-wrench'></i>
          <span>Maintenance</span>
        </a>
        <ul id="maintenanceGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
          <li class="sidebar-item">
            <a href="{{ route('moulddown.index') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Mould Repair
            </a>
          </li>
        </ul>
        <ul id="maintenance" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
          <li class="sidebar-item">
            <a href="{{ route('linedown.index') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Line Repair
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Human Resource -->
    @if (($department === 'PERSONALIA' && $deptHead) || $isSuper)
      <li class="sidebar-item" id="sidebar-item-hrd">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#humanResourceGroup" aria-expanded="false" aria-controls="humanResourceGroup">
          <i class='bx bxs-user'></i>
          <span>Human Resource</span>
        </a>
        <ul id="humanResourceGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
          <li class="sidebar-item" id="sidebar-item-hrd">
            <a href="{{ route('hrd.importantDocs.index') }}" class="sidebar-link">
              <i class='bx bx-file-blank'></i>
              Important Documents
            </a>
          </li>
        </ul>
      </li>
    @endif
    
    <!-- Purchasing -->
    @if ($department === 'PURCHASING' || $isSuper)
      <li class="sidebar-item" id="sidebar-item-purchasing">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#purchasingGroup" aria-expanded="false" aria-controls="purchasingGroup">
          <i class='bx bx-dollar-circle'></i>
          <span>Purchasing</span>
        </a>
        <ul id="purchasingGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
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

    <!-- Accounting -->
    @if ($department === 'ACCOUNTING' || $isSuper)
      <li class="sidebar-item" id="sidebar-item-accounting">
        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
          data-bs-target="#accountingGroup" aria-expanded="false" aria-controls="accountingGroup">
          <i class='bx bx-dollar'></i>
          <span>Accounting</span>
        </a>
        <ul id="accountingGroup" class="sidebar-dropdown list-unstyled collapse"
          data-bs-parent="#sidebar">
          <li class="sidebar-accounting">
            <a href="{{ route('accounting.purchase-request') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Approved PRs
            </a>
          </li>
        </ul>
      </li>
    @endif
    
    <!-- Inventory -->
    <li class="sidebar-item" id="sidebar-item-list">
      <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
        data-bs-target="#inventoryGroup" aria-expanded="false" aria-controls="inventoryGroup">
        <i class='bx bxs-component'></i>
        <span>Inventory</span>
      </a>
      <ul id="inventoryGroup" class="sidebar-dropdown list-unstyled collapse"
        data-bs-parent="#sidebar">
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

    <!-- Store -->
    <li class="sidebar-item" id="sidebar-item-list">
      <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
        data-bs-target="#storeGroup" aria-expanded="false" aria-controls="storeGroup">
        <i class='bx bxs-component'></i>
        <span>Store</span>
      </a>
      <ul id="storeGroup" class="sidebar-dropdown list-unstyled collapse"
        data-bs-parent="#sidebar">
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

        @if ($user->name === 'raymond')
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

        <li class="sidebar-list">
          <a href="{{ route('delivery-notes.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Delivery Notes
          </a>
        </li>

        <li class="sidebar-list">
          <a href="{{ route('destination.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Destination
          </a>
        </li>

        <li class="sidebar-list">
          <a href="{{ route('vehicles.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Vehicles
          </a>
        </li>
      </ul>
    </li>

    <!-- Stock Management -->
    <li class="sidebar-item" id="sidebar-item-stock-management">
      <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
        data-bs-target="#stockManagementGroup" aria-expanded="false"
        aria-controls="stockManagementGroup">
        <i class='bx bxs-component'></i>
        <span>Stock Management</span>
      </a>
      <ul id="stockManagementGroup" class="sidebar-dropdown list-unstyled collapse"
        data-bs-parent="#sidebar">
        <li class="sidebar-item">
          <a href="{{ route('mastertinta.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Master Stock
          </a>
        </li>
      </ul>
    </li>

    <!-- Other -->
    <li class="sidebar-item" id="sidebar-item-other">
      <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
        data-bs-target="#otherGroup" aria-expanded="false" aria-controls="otherGroup">
        <i class='bx bx-dots-horizontal-rounded'></i>
        <span>Other</span>
      </a>
      <ul id="otherGroup" class="sidebar-dropdown list-unstyled collapse"
        data-bs-parent="#sidebar">
        <li class="sidebar-item">
          <a href="{{ $specification === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home') }}"
            class="sidebar-link">
            <i class='bx bx-file'></i>
            Purchase Request
          </a>
        </li>

        <li class="sidebar-item">
          <a href="{{ route('daily-reports.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Job Report
          </a>
        </li>

        <li class="sidebar-item">
          <a href="{{ route('overtime.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Form Overtime
          </a>
        </li>

        @if ($isSuper)
          <li class="sidebar-item">
            <a href="{{ route('actual.import.form') }}" class="sidebar-link">
              <i class='bx bx-file'></i>
              Import Actual Overtime
            </a>
          </li>
        @endif

        <li class="sidebar-item">
          <a href="{{ route('overtime.summary') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Summary Form Overtime
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
          <a href="#" class="sidebar-link collapsed has-dropdown"
            data-bs-toggle="collapse" data-bs-target="#employeeEvaluationGroup"
            aria-expanded="false" aria-controls="employeeEvaluationGroup">
            <i class='bx bx-file'></i>
            Employee Evaluation
          </a>
          <ul id="employeeEvaluationGroup"
            class="sidebar-dropdown list-unstyled collapse">
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
            @if ($isSuper)
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
          <a href="#" class="sidebar-link collapsed has-dropdown"
            data-bs-toggle="collapse" data-bs-target="#monthlyBudgetGroup"
            aria-expanded="false" aria-controls="monthlyBudgetGroup">
            <i class='bx bx-file'></i>
            Monthly Budget
          </a>
          <ul id="monthlyBudgetGroup"
            class="sidebar-dropdown list-unstyled collapse">
            <li class="sidebar-item">
              <a href="{{ route('monthly.budget.report.index') }}" class="sidebar-link">
                <i class='bx bx-file'></i>
                Reports
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('monthly-budget-summary-report.index') }}"
                class="sidebar-link">
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

        <li class="sidebar-item">
          <a href="{{ route('daily-report.form') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Upload Daily Report
          </a>
        </li>
        <li class="sidebar-item">
          <a href="{{ route('files.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Files
          </a>
        </li>
        <li class="sidebar-item">
          <a href="{{ route('department-expenses.index') }}" class="sidebar-link">
            <i class='bx bx-file'></i>
            Department Expenses
          </a>
        </li>
      </ul>
    </li>
  </ul>
</aside>

{{-- Better search: show parents if any child matches --}}
<script>
  (function () {
    const input = document.getElementById('sidebar-search-input');
    const navRoot = document.getElementById('sidebarNavRoot');
    const debounce = (fn, d=120) => { let t; return (...a)=>{clearTimeout(t); t=setTimeout(()=>fn(...a), d);} };

    function matches(text, q){ return (text||'').toLowerCase().includes(q); }

    function filterMenu() {
      const q = (input.value || '').trim().toLowerCase();
      const groups = navRoot.querySelectorAll(':scope > li.sidebar-item');

      groups.forEach(group => {
        const link = group.querySelector(':scope > a.sidebar-link');
        const sub = group.querySelector(':scope > ul.sidebar-dropdown');

        if (!sub) {
          const text = (link?.innerText || '');
          group.style.display = q ? (matches(text, q) ? '' : 'none') : '';
          return;
        }

        // group with children
        const items = sub.querySelectorAll(':scope > li.sidebar-item');
        let anyChildVisible = false;

        items.forEach(li => {
          const a = li.querySelector('a.sidebar-link');
          const t = (a?.innerText || '');
          const show = !q || matches(t, q);
          li.style.display = show ? '' : 'none';
          anyChildVisible = anyChildVisible || show;
        });

        // show group if itself matches OR any child matches
        const groupText = (link?.innerText || '');
        const groupShow = !q || matches(groupText, q) || anyChildVisible;
        group.style.display = groupShow ? '' : 'none';

        // auto open when searching and has visible children
        if (q && anyChildVisible) {
          sub.classList.add('show');
          link.classList.remove('collapsed');
        } else if (!q) {
          // leave current collapse state alone
        }
      });
    }

    input.addEventListener('input', debounce(filterMenu, 120));

    // quick shortcut to focus search with "/"
    window.addEventListener('keydown', (e) => {
      if (e.key === '/' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
        e.preventDefault(); input.focus();
      }
    });
  })();
</script>
