@section('title', 'Form Overtime List - ' . env('APP_NAME'))

<div x-data x-init="$nextTick(() => {
    [...document.querySelectorAll('[data-bs-toggle=tooltip]')].forEach(el => new bootstrap.Tooltip(el));
})">

    @include('partials.alert-success-error')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0">Form Overtime List</h2>
        @if (Auth::user()->department->name !== 'MANAGEMENT')
            <a href="{{ route('overtime.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Form Overtime
            </a>
        @endif
    </div>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb bg-light rounded py-2">
            <li class="breadcrumb-item"><a href="{{ route('overtime.index') }}">Form Overtime</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </nav>

    <div class="mb-4">
        <div class="pb-2">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-sm fw-bold text-secondary">Info Summary</span>

                {{-- Scope switch (All results vs This page) --}}
                <div class="btn-group btn-group-sm" role="group" aria-label="Stats scope">
                    <input type="radio" class="btn-check" id="scopeAll" name="statsScope" value="all"
                        wire:model.live="statsScope">
                    <label class="btn btn-outline-secondary" for="scopeAll">All results</label>

                    <input type="radio" class="btn-check" id="scopePage" name="statsScope" value="page"
                        wire:model.live="statsScope">
                    <label class="btn btn-outline-secondary" for="scopePage">This page</label>
                </div>
            </div>

            {{-- Skeleton while stats recompute --}}
            <div class="row g-3 d-none" wire:loading.class.remove="d-none"
                wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage,statsScope">
                @for ($i = 0; $i < 3; $i++)
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="placeholder-glow">
                                    <span class="placeholder rounded-circle d-inline-block"
                                        style="width:36px;height:36px;"></span>
                                    <div class="mt-2">
                                        <span class="placeholder col-6"></span>
                                        <div class="mt-1"><span class="placeholder col-6 col-md-3"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Cards (visible when NOT loading) --}}
            <div class="row g-3" wire:loading.remove
                wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage,statsScope">
                {{-- Approved --}}
                <div class="col-12 col-md-4">
                    <button type="button"
                        class="card border-0 shadow-sm h-100 w-100 text-start stat-card {{ $infoStatus === 'approved' ? 'stat-card--active' : '' }}"
                        wire:click="setInfoFilter('approved')"
                        aria-pressed="{{ $infoStatus === 'approved' ? 'true' : 'false' }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="badge rounded-circle p-3 bg-success-subtle text-success"><i
                                    class="bi bi-check2"></i></span>
                            <div>
                                <div class="text-muted small">Approved</div>
                                <div class="fs-5 fw-semibold">{{ number_format($stats['approved']) }}</div>
                                <div class="progress mt-1" style="height:4px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $stats['pct_approved'] }}%"
                                        aria-valuenow="{{ $stats['pct_approved'] }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>

                {{-- Rejected --}}
                <div class="col-12 col-md-4">
                    <button type="button"
                        class="card border-0 shadow-sm h-100 w-100 text-start stat-card {{ $infoStatus === 'rejected' ? 'stat-card--active' : '' }}"
                        wire:click="setInfoFilter('rejected')"
                        aria-pressed="{{ $infoStatus === 'rejected' ? 'true' : 'false' }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="badge rounded-circle p-3 bg-danger-subtle text-danger"><i
                                    class="bi bi-x-lg"></i></span>
                            <div>
                                <div class="text-muted small">Rejected</div>
                                <div class="fs-5 fw-semibold">{{ number_format($stats['rejected']) }}</div>
                                <div class="progress mt-1" style="height:4px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: {{ $stats['pct_rejected'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>

                {{-- Pending --}}
                <div class="col-12 col-md-4">
                    <button type="button"
                        class="card border-0 shadow-sm h-100 w-100 text-start stat-card {{ $infoStatus === 'pending' ? 'stat-card--active' : '' }}"
                        wire:click="setInfoFilter('pending')"
                        aria-pressed="{{ $infoStatus === 'pending' ? 'true' : 'false' }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="badge rounded-circle p-3 bg-secondary-subtle text-secondary"><i
                                    class="bi bi-hourglass-split"></i></span>
                            <div>
                                <div class="text-muted small">Pending</div>
                                <div class="fs-5 fw-semibold">{{ number_format($stats['pending']) }}</div>
                                <div class="progress mt-1" style="height:4px;">
                                    <div class="progress-bar bg-secondary" role="progressbar"
                                        style="width: {{ $stats['pct_pending'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tiny CSS touch for hover/active --}}
    <style>
        .stat-card {
            transition: transform .08s ease, box-shadow .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .stat-card--active {
            outline: 2px solid var(--bs-primary);
        }
    </style>

    {{-- FILTERS / TOOLBAR CARD --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            {{-- Top row: Search + quick ranges + density + right tools --}}
            <div class="d-flex flex-wrap gap-2 align-items-center">

                {{-- Search --}}
                <div class="input-group" style="max-width: 360px">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search ID / Admin / Branch"
                        wire:model.live.debounce.400ms="search">
                </div>

                {{-- Quick date ranges (desktop) --}}
                <div class="btn-group d-none d-md-inline-flex" role="group" aria-label="Quick ranges">
                    <button type="button" class="btn btn-outline-secondary {{ $range === 'today' ? 'active' : '' }}"
                        aria-pressed="{{ $range === 'today' ? 'true' : 'false' }}"
                        wire:click="setRange('today')">Today</button>

                    <button type="button" class="btn btn-outline-secondary {{ $range === '7d' ? 'active' : '' }}"
                        aria-pressed="{{ $range === '7d' ? 'true' : 'false' }}" wire:click="setRange('7d')">Last 7
                        days</button>

                    <button type="button" class="btn btn-outline-secondary {{ $range === '30d' ? 'active' : '' }}"
                        aria-pressed="{{ $range === '30d' ? 'true' : 'false' }}" wire:click="setRange('30d')">Last 30
                        days</button>

                    <button type="button" class="btn btn-outline-secondary {{ $range === 'mtd' ? 'active' : '' }}"
                        aria-pressed="{{ $range === 'mtd' ? 'true' : 'false' }}" title="Month-to-Date"
                        wire:click="setRange('mtd')">MTD</button>
                </div>

                {{-- Right tools --}}
                <div class="ms-auto d-flex align-items-center gap-2">
                    {{-- Per-page --}}
                    <select class="form-select form-select-sm" style="max-width: 120px" wire:model.live="perPage">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>

                    {{-- Density toggle --}}
                    <div class="btn-group ms-1" role="group" aria-label="Density">
                        <input type="radio" class="btn-check" id="denseOn" name="density" value="1"
                            wire:model.live="dense">
                        <label class="btn btn-outline-secondary btn-sm" for="denseOn" data-bs-toggle="tooltip"
                            title="Compact rows">
                            <i class="bi bi-list"></i>
                        </label>

                        <input type="radio" class="btn-check" id="denseOff" name="density" value="0"
                            wire:model.live="dense">
                        <label class="btn btn-outline-secondary btn-sm" for="denseOff" data-bs-toggle="tooltip"
                            title="Comfortable rows">
                            <i class="bi bi-ui-checks-grid"></i>
                        </label>
                    </div>

                    {{-- Mobile: open offcanvas --}}
                    <button class="btn btn-outline-secondary btn-sm d-md-none" data-bs-toggle="offcanvas"
                        data-bs-target="#filterOffcanvas">
                        <i class="bi bi-sliders"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Desktop filter fields (inline) --}}
            <div class="row g-3 align-items-end mt-3 d-none d-md-flex">
                <div class="col-auto">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input id="start_date" type="date" class="form-control shadow-sm" wire:model.live="startDate"
                        placeholder=" ">
                    @error('startDate')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-auto">
                    <label for="end_date" class="form-label">End Date</label>
                    <input id="end_date" type="date" class="form-control shadow-sm" wire:model.live="endDate"
                        placeholder=" ">
                    @error('endDate')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-auto">
                    <label for="dept" class="form-label">Department</label>
                    <select id="dept" class="form-select shadow-sm" wire:model.live="dept">
                        <option value="">-- All --</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($user->specification->name === 'VERIFICATOR')
                    <div class="col-auto">
                        <label for="info_status" class="form-label">Info</label>
                        <select id="info_status" class="form-select shadow-sm" wire:model.live="infoStatus">
                            <option value="">-- Semua --</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <label for="is_push" class="form-label">Push by Verificator</label>
                        <select id="is_push" class="form-select shadow-sm" wire:model.live="isPush">
                            <option value="">-- All --</option>
                            <option value="1">Already Pushed</option>
                            <option value="0">Not Yet Pushed</option>
                        </select>
                    </div>
                @endif

                <div class="col-auto">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary shadow-sm" wire:click="resetFilters"
                        wire:loading.attr="disabled">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear all
                    </button>
                </div>

                {{-- Export CSV --}}
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-outline-primary" wire:click="exportCsv"
                        wire:loading.attr="disabled"><i class="bi bi-download"></i> Export CSV
                    </button>
                </div>
            </div>

            {{-- Applied filters chips --}}
            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($range)
                    <span class="badge rounded-pill text-bg-light border">
                        Range: {{ strtoupper($range) }}
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('range')"></button>
                    </span>
                @endif

                @if ($startDate && $endDate)
                    <span class="badge rounded-pill text-bg-light border">
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} –
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('dates')"></button>
                    </span>
                @endif

                @if ($dept)
                    <span class="badge rounded-pill text-bg-light border">
                        Dept: {{ collect($departments)->firstWhere('id', $dept)['name'] ?? $dept }}
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('dept')"></button>
                    </span>
                @endif

                @if ($infoStatus)
                    <span class="badge rounded-pill text-bg-light border">
                        Info: {{ ucfirst($infoStatus) }}
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('infoStatus')"></button>
                    </span>
                @endif

                @if ($isPush !== null && $isPush !== '')
                    <span class="badge rounded-pill text-bg-light border">
                        {{ $isPush === '1' ? 'Pushed' : 'Not pushed' }}
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('isPush')"></button>
                    </span>
                @endif

                @if ($search)
                    <span class="badge rounded-pill text-bg-light border">
                        Search: “{{ $search }}”
                        <button class="btn-close btn-close-white ms-2" aria-label="Clear"
                            wire:click="clearFilter('search')"></button>
                    </span>
                @endif
            </div>

        </div>
    </div>

    {{-- MOBILE OFFCANVAS with the same fields --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="vstack gap-3">
                <div>
                    <label class="form-label">Date range</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" wire:model.live="startDate">
                        <input type="date" class="form-control" wire:model.live="endDate">
                    </div>
                </div>

                <div>
                    <label class="form-label">Quick ranges</label>
                    <div class="btn-group w-100" role="group" aria-label="Quick ranges">
                        <button class="btn btn-outline-secondary {{ $range === 'today' ? 'active' : '' }}"
                            wire:click="setRange('today')">Today</button>
                        <button class="btn btn-outline-secondary {{ $range === '7d' ? 'active' : '' }}"
                            wire:click="setRange('7d')">7d</button>
                        <button class="btn btn-outline-secondary {{ $range === '30d' ? 'active' : '' }}"
                            wire:click="setRange('30d')">30d</button>
                        <button class="btn btn-outline-secondary {{ $range === 'mtd' ? 'active' : '' }}"
                            wire:click="setRange('mtd')">MTD</button>
                    </div>
                </div>

                <div>
                    <label class="form-label">Department</label>
                    <select class="form-select" wire:model.live="dept">
                        <option value="">-- All --</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($user->specification->name === 'VERIFICATOR')
                    <div>
                        <label class="form-label">Info</label>
                        <select class="form-select" wire:model.live="infoStatus">
                            <option value="">-- Semua --</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Push by Verificator</label>
                        <select class="form-select" wire:model.live="isPush">
                            <option value="">-- All --</option>
                            <option value="1">Already Pushed</option>
                            <option value="0">Not Yet Pushed</option>
                        </select>
                    </div>
                @endif

                <div class="d-grid">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="offcanvas" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear all
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        function sortIcon($field, $current, $dir)
        {
            if ($current !== $field) {
                return 'bi bi-arrow-down-up text-muted';
            }
            return $dir === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down';
        }
        function ariaSort($field, $current, $dir)
        {
            if ($current !== $field) {
                return 'none';
            }
            return $dir === 'asc' ? 'ascending' : 'descending';
        }

        $compact = $dense ? 'table-sm table-compact' : '';
    @endphp

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" wire:loading.class="opacity-50">
                {{-- ===== DATA TABLE (shown when NOT loading) ===== --}}
                <table wire:loading.remove wire:key="table-data"
                    class="table table-hover table-striped table-bordered align-middle text-center mb-0 {{ $compact }}">

                    <thead class="table-light sticky-top" style="top:0; z-index:2;">
                        <tr>
                            <th role="button" wire:click="sortBy('id')"
                                aria-sort="{{ ariaSort('id', $sortField, $sortDirection) }}">
                                ID <i class="{{ sortIcon('id', $sortField, $sortDirection) }}"></i>
                            </th>
                            <th>Admin</th>
                            <th>Dept</th>
                            <th>Branch</th>
                            <th role="button" wire:click="sortBy('first_overtime_date')"
                                aria-sort="{{ ariaSort('first_overtime_date', $sortField, $sortDirection) }}">
                                Overtime Date <i
                                    class="{{ sortIcon('first_overtime_date', $sortField, $sortDirection) }}"></i>
                            </th>
                            <th role="button" wire:click="sortBy('status')"
                                aria-sort="{{ ariaSort('status', $sortField, $sortDirection) }}">
                                Status <i class="{{ sortIcon('status', $sortField, $sortDirection) }}"></i>
                            </th>
                            <th>Type</th>
                            <th>Is After Hour?</th>
                            <th>Info</th>
                            <th>Action</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($dataheader as $fot)
                            <tr wire:key="row-{{ $fot->id }}"
                                class="{{ $fot->is_planned ? '' : 'bg-danger-subtle' }}">
                                <td>{{ $fot->id }}</td>
                                <td>{{ $fot->user->name }}</td>
                                <td>
                                    <span
                                        class="badge bg-light text-secondary border">{{ $fot->department->name }}</span>
                                </td>
                                <td>{{ $fot->branch }}</td>
                                <td>{{ $fot->first_overtime_date ? \Carbon\Carbon::parse($fot->first_overtime_date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    @include('partials.formovertime-status', ['fot' => $fot])
                                    @if ($fot->is_push == 1)
                                        <div class="text-success small mt-1" data-bs-toggle="tooltip"
                                            title="Pushed by Verificator">
                                            <i class="bi bi-check-circle me-1"></i> Finish by Bu Bernadett
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge rounded-pill px-3 py-2 fs-6 {{ $fot->is_planned ? 'bg-light text-secondary border border-secondary' : 'bg-danger text-white' }}"
                                        data-bs-toggle="tooltip"
                                        title="Planned if created before today; otherwise Urgent">
                                        {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                    </span>
                                </td>
                                <td>{{ $fot->is_after_hour ? 'Yes' : 'No' }}</td>
                                <td class="text-start">
                                    <div class="d-flex flex-column gap-1">
                                        @if ($fot->approved_count)
                                            <span class="badge bg-success">Approved: {{ $fot->approved_count }}</span>
                                        @endif
                                        @if ($fot->rejected_count)
                                            <span class="badge bg-danger">Rejected: {{ $fot->rejected_count }}</span>
                                        @endif
                                        @if ($fot->pending_count)
                                            <span class="badge bg-secondary">Pending: {{ $fot->pending_count }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <a href="{{ route('formovertime.detail', ['id' => $fot->id]) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-info-circle"></i> Detail
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm"
                                            wire:click="$dispatch('confirm-delete', { id: {{ $fot->id }} })"
                                            wire:loading.attr="disabled" title="Delete this record">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($fot->created_at)->format('d-m-Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    No data matches your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- ===== SKELETON TABLE (shown ONLY while loading) ===== --}}
                @php
                    $cols = 11;
                    $rows = min($perPage ?? 10, 10);
                @endphp
                <table wire:loading wire:key="table-skeleton"
                    wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,isPush"
                    class="table table-hover table-striped table-bordered align-middle text-center mb-0 {{ $compact }}"
                    style="table-layout: fixed;" aria-busy="true">
                    <colgroup>
                        <col style="width:6%">
                        <col style="width:14%">
                        <col style="width:10%">
                        <col style="width:12%">
                        <col style="width:12%">
                        <col style="width:10%">
                        <col style="width:7%">
                        <col style="width:7%">
                        <col style="width:9%">
                        <col style="width:7%">
                        <col style="width:6%">
                    </colgroup>

                    <thead class="table-light sticky-top" style="top:0; z-index:2;">
                        <tr>
                            <th>ID</th>
                            <th>Admin</th>
                            <th>Dept</th>
                            <th>Branch</th>
                            <th>Overtime Date</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Is After Hour?</th>
                            <th>Info</th>
                            <th>Action</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @for ($r = 0; $r < $rows; $r++)
                            <tr>
                                @for ($c = 0; $c < $cols; $c++)
                                    <td class="py-2">
                                        <span class="placeholder-glow d-block">
                                            <span class="placeholder d-block w-100" style="height:1rem;"></span>
                                        </span>
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing {{ $dataheader->firstItem() }} to {{ $dataheader->lastItem() }}
            of {{ $dataheader->total() }} entries
        </div>
        <div>
            {{ $dataheader->links() }}
        </div>
    </div>

    {{-- Delete Confirmation Modal (no <script> tag) --}}
    <div x-data="{
        m: null,
        init() {
            this.m = new bootstrap.Modal(this.$refs.modal, { backdrop: 'static', keyboard: false });
            // Listen for Livewire browser events
            window.addEventListener('show-delete-modal', () => this.m.show());
            window.addEventListener('hide-delete-modal', () => this.m.hide());
        },
        close() { this.m?.hide(); }
    }" x-init="init()">
        <div class="modal fade" id="deleteModal" x-ref="modal" tabindex="-1" aria-labelledby="deleteModalLabel"
            aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Form Overtime</h5>
                        <button type="button" class="btn-close" @click="close()" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete
                        <strong>#{{ $pendingDeleteId }}</strong>?
                        <div class="text-muted small mt-1">This action cannot be undone.</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" @click="close()">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteConfirmed"
                            wire:target="deleteConfirmed" wire:loading.attr="disabled">
                            <span class="spinner-border spinner-border-sm me-1" wire:loading
                                wire:target="deleteConfirmed"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>



</div>
