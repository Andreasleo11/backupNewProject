{{-- Sidebar search (hidden when collapsed on desktop) --}}
<div class="px-3 pt-3 pb-2 border-b border-slate-100" x-show="!sidebarCollapsed">
    <div class="relative">
        <input type="text" x-model="q" placeholder="Search menu..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm outline-none focus:border-slate-900 focus:bg-white focus:ring-1 focus:ring-slate-900">
        <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9 3.5a5.5 5.5 0 103.478 9.8l3.611 3.611a.75.75 0 101.06-1.06l-3.61-3.612A5.5 5.5 0 009 3.5zm-4 5.5a4 4 0 118 0 4 4 0 01-8 0z"
                    clip-rule="evenodd" />
            </svg>
        </span>
    </div>
</div>

@php
    $nav = [
        [
            'type' => 'single',
            'label' => 'Dashboard',
            'route' => 'home',
            'icon' => 'home',
            'active' => request()->routeIs('home'),
        ],
        [
            'type' => 'group',
            'label' => 'Admin',
            'icon' => 'shield',
            'children' => [
                [
                    'label' => 'Access Overview',
                    'route' => 'admin.access-overview.index',
                    'icon' => 'key',
                    'active' => request()->routeIs('admin.access-overview.index'),
                ],
                [
                    'label' => 'Users',
                    'route' => 'admin.users.index',
                    'icon' => 'key',
                    'active' => request()->routeIs('admin.users.index'),
                ],
                [
                    'label' => 'Roles',
                    'route' => 'admin.roles.index',
                    'icon' => 'key',
                    'active' => request()->routeIs('admin.roles.index'),
                ],
                [
                    'label' => 'Approval Rules',
                    'route' => 'admin.approval-rules.index',
                    'icon' => 'key',
                    'active' => request()->routeIs('admin.approval-rules.index'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Master Data',
            'icon' => 'database',
            'children' => [
                [
                    'label' => 'Departments',
                    'route' => 'admin.departments.index',
                    'icon' => 'building',
                    'active' => request()->routeIs('admin.departments.*'),
                ],
                [
                    'label' => 'Employees',
                    'route' => 'admin.employees.index',
                    'icon' => 'users',
                    'active' => request()->routeIs('admin.employees.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Computer',
            'icon' => 'computer-desktop',
            'children' => [
                [
                    'label' => 'Stock Management',
                    'route' => 'mastertinta.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('mastertinta.index'),
                ],
                [
                    'label' => 'Inventory Master',
                    'route' => 'masterinventory.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('masterinventory.index'),
                ],
                [
                    'label' => 'Maintenance Inventory',
                    'route' => 'maintenance.inventory.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('maintenance.inventory.index'),
                ],
                [
                    'label' => 'Type Inventory',
                    'route' => 'masterinventory.typeindex',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('masterinventory.typeindex'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Quality',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Verification Reports',
                    'route' => 'qaqc.report.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('qaqc.report.*'),
                ],
                [
                    'label' => 'Form Adjust',
                    'route' => 'listformadjust',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('listformadjust'),
                ],
                [
                    'label' => 'Defect Categories',
                    'route' => 'qaqc.defectcategory',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('qaqc.defectcategory'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Production',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'PPS Wizard',
                    'route' => 'indexpps',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('indexpps'),
                ],
                [
                    'label' => 'Capacity By Forecast',
                    'route' => 'capacityforecastindex',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('capacityforecastindex'),
                ],
                [
                    'label' => 'Form Request Trail',
                    'route' => 'pe.formlist',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('pe.formlist'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Maintenance',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Mould Repair',
                    'route' => 'moulddown.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('mouldown.*'),
                ],
                [
                    'label' => 'Line Repair',
                    'route' => 'linedown.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('linedown.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Human Resource',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Important Documents',
                    'route' => 'hrd.importantDocs.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('hrd.importantDocs.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Purchasing',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Forecast Prediction',
                    'route' => 'purchasing_home',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('purchasing_home'),
                ],
                [
                    'label' => 'Evaluation Supplier',
                    'route' => 'purchasing.evaluationsupplier.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('purchasing.evaluationsupplier.*'),
                ],
                [
                    'label' => 'Reminder',
                    'route' => 'reminderindex',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('reminderindex'),
                ],
                [
                    'label' => 'Purchasing Requirement',
                    'route' => 'purchasingrequirement.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('purchasingrequirement.index'),
                ],
                [
                    'label' => 'Delivery Schedule',
                    'route' => 'indexds',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('indexds'),
                ],
                [
                    'label' => 'Forecast Customer Master',
                    'route' => 'fc.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('fc.index'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Accounting',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Approved PRs',
                    'route' => 'accounting.purchase-request',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('accounting.purchase-request'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Inventory',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'FG Stock Monitoring',
                    'route' => 'delsched.averagemonth',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('delsched.averagemonth'),
                ],
                [
                    'label' => 'Inventory FG',
                    'route' => 'inventoryfg',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('inventoryfg'),
                ],
                [
                    'label' => 'Inventory MTR',
                    'route' => 'inventorymtr',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('inventorymtr'),
                ],
                [
                    'label' => 'Machine and Line list',
                    'route' => 'invlinelist',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('invlinelist'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Store',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Create Barcode',
                    'route' => 'barcodeindex',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('barcodeindex'),
                ],
                [
                    'label' => 'Barcode Feature',
                    'route' => 'barcode.base.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('barcode.base.*'),
                ],
                [
                    'label' => 'Scan Barcode',
                    'route' => 'inandout.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('inandout.*'),
                ],
                [
                    'label' => 'Missing Barcode Generator',
                    'route' => 'missingbarcode.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('missingbarcode.*'),
                ],
                [
                    'label' => 'Report History',
                    'route' => 'barcode.historytable',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('barcode.historytable'),
                ],
                [
                    'label' => 'STOCK Item',
                    'route' => 'stockallbarcode',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('stockallbarcode'),
                ],
                [
                    'label' => 'List All Item Barcode',
                    'route' => 'updated.barcode.item.position',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('updated.barcode.item.position'),
                ],
                [
                    'label' => 'Delivery Notes',
                    'route' => 'delivery-notes.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('delivery-notes.*'),
                ],
                [
                    'label' => 'Destination',
                    'route' => 'destination.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('destination.*'),
                ],
                [
                    'label' => 'Vehicles',
                    'route' => 'vehicles.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('vehicles.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Monthly Budget',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Reports',
                    'route' => 'monthly.budget.report.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('monthly.budget.report.*'),
                ],
                [
                    'label' => 'Summary Reports',
                    'route' => 'monthly-budget-summary-report.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('monthly-budget-summary-report.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Employee Evaluation',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'All',
                    'route' => 'discipline.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('discipline.*'),
                ],
                [
                    'label' => 'Yayasan',
                    'route' => 'yayasan.table',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('yayasan.table'),
                ],
                [
                    'label' => 'Magang',
                    'route' => 'magang.table',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('magang.table'),
                ],
                [
                    'label' => 'Evaluasi Individu All IN',
                    'route' => 'format.evaluation.year.allin',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('format.evaluation.year.allin'),
                ],
                [
                    'label' => 'Evaluasi Individu Yayasan',
                    'route' => 'format.evaluation.year.yayasan',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('format.evaluation.year.yayasan'),
                ],
                [
                    'label' => 'Evaluasi Individu Magang',
                    'route' => 'format.evaluation.year.magang',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('format.evaluation.year.magang'),
                ],
                [
                    'label' => 'Export Yayasan Jpayroll',
                    'route' => 'exportyayasan.dateinput',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('exportyayasan.dateinput'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Other',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Purchase Request',
                    'route' => auth()->user()->hasRole('top-management') ? 'director.pr.index' : 'purchaserequest',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('director.pr.index') || request()->routeIs('purchaserequest'),
                ],
                [
                    'label' => 'Job Report',
                    'route' => 'daily-reports.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('daily-reports.*'),
                ],
                [
                    'label' => 'Form Overtime',
                    'route' => 'overtime.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('overtime.index'),
                ],
                [
                    'label' => 'Import Actual Overtime',
                    'route' => 'actual.import.form',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('actual.import.form'),
                ],
                [
                    'label' => 'Summary Form Overtime',
                    'route' => 'overtime.summary',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('overtime.summary'),
                ],
                [
                    'label' => 'Form Cuti',
                    'route' => 'formcuti',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('formcuti.*'),
                ],
                [
                    'label' => 'Form Keluar',
                    'route' => 'formkeluar',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('formkeluar.*'),
                ],
                [
                    'label' => 'Monthly PR',
                    'route' => 'purchaserequest.monthlyprlist',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('purchaserequest.monthlyprlist'),
                ],
                [
                    'label' => 'SPK',
                    'route' => 'spk.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('spk.*'),
                ],
                [
                    'label' => 'Form Kerusakan/Perbaikan',
                    'route' => 'formkerusakan.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('formkerusakan.*'),
                ],
                [
                    'label' => 'Purchase Orders',
                    'route' => 'po.dashboard',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('po.dashboard'),
                ],
                [
                    'label' => 'Waiting PO',
                    'route' => 'waiting_purchase_orders.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('waiting_purchase_orders.*'),
                ],
                [
                    'label' => 'Employee Training',
                    'route' => 'employee_trainings.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('employee_trainings.*'),
                ],
                [
                    'label' => 'Upload Daily Report',
                    'route' => 'daily-report.form',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('daily-report.form'),
                ],
                [
                    'label' => 'Files',
                    'route' => 'files.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('files.*'),
                ],
                [
                    'label' => 'Department Expenses',
                    'route' => 'department-expenses.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('department-expenses.*'),
                ],
                [
                    'label' => 'Vehicles',
                    'route' => 'vehicles.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('vehicles.*'),
                ],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'File Compliance',
            'icon' => 'document-text',
            'children' => [
                [
                    'label' => 'Requirements',
                    'route' => 'requirements.index',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('requirements.index'),
                ],
                [
                    'label' => 'Requirements Assign',
                    'route' => 'requirements.assign',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('requirements.assign'),
                ],
                [
                    'label' => 'Review Upload',
                    'route' => 'admin.requirement-uploads',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('admin.requirement-uploads'),
                ],
                [
                    'label' => 'Departments Overview',
                    'route' => 'departments.overview',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('departments.overview'),
                ],
                [
                    'label' => 'Compliance Dashboard',
                    'route' => 'compliance.dashboard',
                    'icon' => 'document-text',
                    'active' => request()->routeIs('compliance.dashboard'),
                ],
            ],
        ],
    ];
@endphp

<nav class="flex-1 overflow-y-auto px-2 py-3 text-sm">
    <ul class="space-y-2">
        @foreach ($nav as $item)
            @if ($item['type'] === 'single')
                @php
                    $label = $item['label'];
                    $isActive = $item['active'] ?? false;
                @endphp
                <li class="relative text-slate-600" x-data="{ hover: false, flyoutTop: 0, label: '{{ strtolower($label) }}' }"
                    @mouseenter="hover = true; flyoutTop = $el.getBoundingClientRect().top" @mouseleave="hover = false"
                    x-show="q === '' || label.includes(q.toLowerCase())">
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 transition hover:bg-slate-100  {{ $isActive ? 'bg-slate-900 text-white shadow-sm hover:text-slate-500 border-2' : '' }}"
                        :class="{
                            'justify-center': sidebarCollapsed,
                            'justify-start': !sidebarCollapsed,
                        }">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-md {{ $isActive ? 'bg-slate-800/80 text-white' : 'bg-slate-100 text-slate-500' }}">
                            @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                        </span>
                        <span class="truncate text-sm" x-show="!sidebarCollapsed">
                            {{ $item['label'] }}
                        </span>
                    </a>

                    {{-- Teleported flyout when collapsed --}}
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && hover" x-transition x-cloak
                            class="fixed z-50 ml-1 rounded-lg bg-slate-900 px-3 py-2 text-xs text-white shadow-lg"
                            :style="{
                                top: (flyoutTop + 8) + 'px', // 8px offset for nicer alignment
                                left: '5rem', // collapsed sidebar width (md:w-20 = 5rem)
                            }">
                            {{ $item['label'] }}
                        </div>
                    </template>
                </li>
            @elseif ($item['type'] === 'group')
                @php
                    $groupLabel = $item['label'];
                    $children = $item['children'] ?? [];
                    $anyActive = collect($children)->contains(fn($c) => $c['active'] ?? false);
                @endphp
                <li class="relative text-slate-600"
                    x-data="{
                        hover: false, 
                        open: {{ $anyActive ? 'true' : 'false' }},
                        flyoutOpen: false,
                        flyoutTop: 0,
                        flyoutTimer: null,
                        label: '{{ strtolower($groupLabel) }}'
                    }" 
                    @mouseenter="
                        clearTimeout(flyoutTimer);
                        hover = true;
                        flyoutOpen = true;
                        flyoutTop = $el.getBoundingClientRect().top;
                    "
                    @mouseleave="
                        hover = false;
                        flyoutTimer = setTimeout(() => { flyoutOpen = false }, 120);
                    " 
                    x-show="q === '' || label.includes(q.toLowerCase())"
                >
                    {{-- Group header --}}
                    <button type="button" @click="open = !open" 
                        class="flex w-full items-center rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide {{ $anyActive ? 'text-slate-900' : 'text-slate-500 hover:text-slate-700' }}"
                        :class="{
                            'justify-between': !sidebarCollapsed,
                            'justify-center': sidebarCollapsed,
                        }">
                        <span class="flex items-center gap-2">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                                @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                            </span>
                            <span x-show="!sidebarCollapsed">{{ $groupLabel }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            :class="open ? 'rotate-90 text-slate-700' : 'text-slate-400'"
                            class="h-3 w-3 transition-transform" viewBox="0 0 20 20" fill="currentColor"
                            x-show="!sidebarCollapsed">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 01.02-1.06L11 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    {{-- Children (visible only when not collapsed) --}}
                    <ul x-show="open && !sidebarCollapsed" class="mt-1 space-y-1 pl-3 border-l border-slate-100">
                        @foreach ($children as $child)
                            @php
                                $childLabel = $child['label'];
                                $childActive = $child['active'] ?? false;
                            @endphp
                            <li x-data="{ label: '{{ strtolower($childLabel) }}' }" x-show="q === '' || label.includes(q.toLowerCase())">
                                <a href="{{ route($child['route']) }}"
                                    class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs transition hover:bg-slate-100
                                          {{ $childActive ? 'bg-slate-900 text-white shadow-sm hover:text-slate-500 border-2' : 'text-slate-600' }}">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-md {{ $childActive ? 'bg-slate-800/80 text-white' : 'bg-slate-100 text-slate-500' }}">
                                        @include('new.layouts.partials.nav-icon', ['name' => $child['icon']])
                                    </span>
                                    <span class="truncate">{{ $child['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Teleported flyout with children when collapsed --}}
                    <template x-teleport="body">
                        <div x-show="sidebarCollapsed && flyoutOpen" 
                            x-transition 
                            x-cloak
                            class="fixed z-50 w-56 rounded-lg bg-slate-900 px-3 py-2 text-xs text-white shadow-lg"
                            :style="{
                                top: (flyoutTop + 4) + 'px',
                                left: '5rem',
                            }"
                            @mouseenter="
                                clearTimeout(flyoutTimer);
                                flyoutOpen = true;
                            "
                            @mouseleave="
                                flyoutTimer = setTimeout(() => { flyoutOpen = false }, 120);
                            ">
                            <div class="mb-1 font-semibold text-[11px] uppercase tracking-wide text-slate-300">
                                {{ $groupLabel }}
                            </div>
                            <ul class="space-y-0.5">
                                @foreach ($children as $child)
                                    <li>
                                        <a href="{{ route($child['route']) }}"
                                            class="flex items-center gap-2 rounded-md px-2 py-1 hover:bg-slate-800/70">
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded bg-slate-800 text-slate-200">
                                                @include('new.layouts.partials.nav-icon', [
                                                    'name' => $child['icon'],
                                                ])
                                            </span>
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </template>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
