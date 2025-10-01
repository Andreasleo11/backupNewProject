<div class="container py-3">

    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('monthly-budget-summary-report.index') }}">Monthly Budget Summary Reports</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">List</li>
        </ol>
    </nav>

    {{-- Header + Controls --}}
    <div class="d-flex flex-column gap-3">

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
            <div>
                <h2 class="fw-bold mb-1">Monthly Budget Summary Reports</h2>
                <div class="text-secondary small">
                    Showing <strong>{{ $reports->count() }}</strong> of <strong>{{ $reports->total() }}</strong>
                </div>
            </div>

            {{-- Generate form (server POST) --}}
            @if ($showGenerateButton)
                <form action="{{ route('monthly.budget.summary.report.store') }}" method="post"
                    class="d-flex align-items-center gap-2" x-data
                    x-on:submit="$el.querySelector('button[type=submit]').disabled = true">
                    @csrf
                    <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">
                    <div class="input-group">
                        <span class="input-group-text" id="monthPickerLabel">
                            <i class="bx bx-calendar"></i>
                        </span>
                        <input type="text" id="monthPicker" name="month" class="form-control"
                            placeholder="Select Month (mm-yyyy)" aria-label="Select Month"
                            aria-describedby="monthPickerLabel" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-refresh me-1"></i> Generate
                    </button>
                </form>
            @endif
        </div>

        {{-- Filter toolbar --}}
        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="search" class="form-label small text-secondary mb-1">Search</label>
                        <input wire:model.live.debounce.400ms="search" type="text" id="search"
                            class="form-control" placeholder="Doc # or Creator name">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="from" class="form-label small text-secondary mb-1">Month from</label>
                        <input wire:model.live="monthFrom" type="text" id="from" class="form-control"
                            placeholder="mm-yyyy">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="to" class="form-label small text-secondary mb-1">Month to</label>
                        <input wire:model.live="monthTo" type="text" id="to" class="form-control"
                            placeholder="mm-yyyy">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="status" class="form-label small text-secondary mb-1">Status</label>
                        <select wire:model.live="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="1">Draft</option>
                            <option value="2">Submitted</option>
                            <option value="3">Design Head Approved</option>
                            <option value="4">GM Approved</option>
                            <option value="5">Director Review</option>
                            <option value="6">Director Approved</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-1">
                        <label for="perPage" class="form-label small text-secondary mb-1">Per page</label>
                        <select wire:model.live="perPage" id="perPage" class="form-select">
                            <option selected>10</option>
                            <option>15</option>
                            <option>25</option>
                            <option>50</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <button wire:click="clearFilters" type="button" class="btn btn-outline-secondary w-100">
                            <i class="bx bx-filter-alt-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="card mt-4 position-relative">

        {{-- Loading overlay --}}
        <div wire:loading.flex
            class="position-absolute top-0 start-0 w-100 h-100 bg-body bg-opacity-75
                                 align-items-center justify-content-center"
            aria-live="polite">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
            <span class="ms-2">Loading…</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-body">
                        <tr>
                            <th
                                class="text-secondary text-uppercase small fw-semibold text-nowrap position-sticky top-0 bg-body">
                                #</th>

                            {{-- Sortable headers --}}
                            <th class="position-sticky top-0 bg-body">
                                <button type="button" class="btn btn-link p-0 text-decoration-none"
                                    wire:click="sortBy('doc_num')">
                                    <span class="text-secondary text-uppercase small fw-semibold">Doc. Number</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'doc_num',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            <th class="position-sticky top-0 bg-body">
                                <button type="button" class="btn btn-link p-0 text-decoration-none"
                                    wire:click="sortBy('report_date')">
                                    <span class="text-secondary text-uppercase small fw-semibold">Report Month</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'report_date',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            <th class="position-sticky top-0 bg-body">
                                <button type="button" class="btn btn-link p-0 text-decoration-none"
                                    wire:click="sortBy('created_at')">
                                    <span class="text-secondary text-uppercase small fw-semibold">Created At</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'created_at',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            <th class="text-secondary text-uppercase small fw-semibold position-sticky top-0 bg-body">
                                Status</th>

                            <th class="text-end position-sticky top-0 bg-body">
                                <button type="button" class="btn btn-link p-0 text-decoration-none"
                                    wire:click="sortBy('total')">
                                    <span class="text-secondary text-uppercase small fw-semibold">Total</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'total',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            <th
                                class="text-secondary text-uppercase small fw-semibold text-end position-sticky top-0 bg-body">
                                MoM</th>
                            <th class="text-secondary text-uppercase small fw-semibold position-sticky top-0 bg-body">
                                Action</th>
                        </tr>
                    </thead>

                    <tbody class="table-group-divider">
                        @forelse ($reports as $report)
                            @php
                                $reportDate = \Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');
                                $createdAt = \Carbon\Carbon::parse($report->created_at);
                                $formattedCreatedAt = $createdAt->format('d/m/Y (H:i:s)');
                                $total = (float) ($report->total_amount ?? 0);
                                $m = $report->mom ?? [
                                    'has_prev' => false,
                                    'direction' => 'none',
                                    'diff' => null,
                                    'pct' => null,
                                    'prev' => 0,
                                ];
                            @endphp

                            <tr>
                                <td class="text-nowrap">{{ $reports->firstItem() + $loop->index }}</td>

                                <td class="text-nowrap">
                                    <span class="fw-semibold">{{ $report->doc_num }}</span>
                                </td>

                                <td class="text-nowrap">
                                    <time datetime="{{ $reportDate->format('Y-m-01') }}"
                                        title="{{ $reportDate->toFormattedDayDateString() }}">
                                        {{ $monthYear }}
                                    </time>
                                </td>

                                <td class="text-nowrap">
                                    <time datetime="{{ $createdAt->toIso8601String() }}"
                                        title="{{ $createdAt->toDayDateTimeString() }}">
                                        {{ $formattedCreatedAt }}
                                    </time>
                                </td>

                                <td>
                                    @include('partials.monthly-budget-summary-report-status', [
                                        'status' => $report->status,
                                    ])
                                </td>

                                {{-- Total --}}
                                <td class="text-end text-nowrap">
                                    <span class="fw-semibold" data-bs-toggle="tooltip"
                                        data-bs-title="Total quantity × cost per unit">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- MoM --}}
                                <td class="text-end text-nowrap">
                                    @if (!$m['has_prev'])
                                        <span class="text-muted" data-bs-toggle="tooltip"
                                            data-bs-title="No prior month">—</span>
                                    @elseif ($m['direction'] === 'up')
                                        <span class="badge bg-success-subtle border border-success-subtle text-success"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-trending-up align-middle me-1"></i>
                                            Rp {{ number_format($m['diff'], 0, ',', '.') }}
                                            <span class="ms-1">({{ number_format($m['pct'], 2, ',', '.') }}%)</span>
                                        </span>
                                    @elseif ($m['direction'] === 'down')
                                        <span class="badge bg-danger-subtle border border-danger-subtle text-danger"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-trending-down align-middle me-1"></i>
                                            Rp {{ number_format($m['diff'], 0, ',', '.') }}
                                            <span class="ms-1">({{ number_format($m['pct'], 2, ',', '.') }}%)</span>
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle border border-secondary-subtle text-secondary"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-minus align-middle me-1"></i>
                                            0 (0%)
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-nowrap">
                                    <a wire:navigate
                                        href="{{ route('monthly.budget.summary.report.show', $report->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bx bx-info-circle me-1"></i> Detail
                                    </a>

                                    @include('partials.delete-confirmation-modal', [
                                        'id' => $report->id,
                                        'route' => 'monthly.budget.summary.report.delete',
                                        'title' => 'Delete report confirmation',
                                        'body' => "Are you sure want to delete report <strong>$report->doc_num</strong>?",
                                    ])

                                    @if ($authUser->id == $report->creator_id)
                                        @if ($report->status === 1)
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#delete-confirmation-modal-{{ $report->id }}">
                                                <i class="bx bx-trash-alt me-1"></i> Delete
                                            </button>
                                        @elseif (in_array($report->status, [2, 3, 4], true))
                                            @include('partials.cancel-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => route(
                                                    'monthly.budget.summary.report.cancel',
                                                    $report->id),
                                            ])
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#cancel-confirmation-modal-{{ $report->id }}">
                                                <i class="bx bx-x-circle me-1"></i> Cancel
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <i class="bx bx-file-find fs-1 text-secondary mb-2"></i>
                                        <div class="fw-semibold">No reports found</div>
                                        <div class="text-secondary small mb-3">Adjust filters or generate a new report.
                                        </div>
                                        @if ($showGenerateButton)
                                            <a href="#monthPicker" class="btn btn-primary">
                                                <i class="bx bx-refresh me-1"></i> Generate Monthly Report
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-transparent">
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-center">
                <div class="small text-secondary">
                    Page {{ $reports->currentPage() }} of {{ $reports->lastPage() }}
                </div>
                <div>
                </div>
            </div>
            {{ $reports->links() }}
        </div>
    </div>

    {{-- Scripts --}}
    @push('extraJs')
        <script type="module">
            // Month pickers (bootstrap-datepicker or similar should be globally loaded)
            const monthOpts = {
                format: "mm-yyyy",
                startView: "months",
                minViewMode: "months",
                autoclose: true
            };
            $('#monthPicker').datepicker(monthOpts);
            $('#from').datepicker(monthOpts).on('changeDate', e => @this.set('monthFrom', e.format()));
            $('#to').datepicker(monthOpts).on('changeDate', e => @this.set('monthTo', e.format()));

            // Tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el, {
                container: 'body'
            }));
        </script>
    @endpush

    {{-- Sticky header helper --}}
    @push('extraCss')
        <style>
            thead tr>* {
                z-index: 2;
            }

            thead .btn.btn-link {
                color: inherit;
            }

            thead .btn.btn-link:hover {
                text-decoration: none;
                opacity: .8;
            }
        </style>
    @endpush
</div>
