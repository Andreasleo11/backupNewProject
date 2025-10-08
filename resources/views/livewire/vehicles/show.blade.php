<div class="container-fluid px-0">
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center my-2" role="alert">
            <i class="bi bi-check2-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center my-2" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Back link --}}
    <a href="{{ route('vehicles.index') }}" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    @php
        $last = $vehicle->latestService;
        $lastKm = (int) ($last->odometer ?? 0);
        $nextKm = $lastKm ? $lastKm + 10000 : null;
    @endphp

    {{-- Header card --}}
    <div class="card border-0 shadow-sm mt-2">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">{{ $vehicle->display_name }}</h4>
                        <div class="text-muted">
                            VIN: <span class="font-monospace">{{ $vehicle->vin ?? '—' }}</span>
                            • Status:
                            <span class="badge text-bg-{{ $vehicle->status->variant() }}">
                                <i class="bi bi-{{ $vehicle->status->icon() }} me-1"></i>
                                {{ $vehicle->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('vehicles.edit', $vehicle) }}">
                        <i class="bi bi-pencil me-1"></i> Edit Vehicle
                    </a>
                    @if (!$vehicle->is_sold)
                        <a class="btn btn-primary" href="{{ route('services.create', $vehicle) }}">
                            <i class="bi bi-wrench-adjustable me-1"></i> Add Service
                        </a>
                    @endif
                </div>
            </div>
            @if ($vehicle->is_sold)
                <div class="alert alert-secondary d-flex align-items-center my-2" role="alert">
                    <i class="bi bi-cash-coin me-2"></i>
                    <div>Sold on <strong>{{ $vehicle->sold_at?->isoFormat('DD MMM YYYY') ?? '—' }}</strong></div>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Odometer</div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i>
                        <div class="fs-5 fw-semibold">{{ number_format($vehicle->odometer) }} <span
                                class="text-muted fs-6">km</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Last Service</div>
                    @if ($last)
                        <div class="fw-semibold">{{ $last->service_date->isoFormat('DD MMM YYYY') }}</div>
                        <div class="text-muted small">at {{ $last->workshop ?? 'Internal' }} •
                            {{ number_format($last->odometer ?? 0) }} km</div>
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Costs</div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-muted">YTD</div>
                            <div class="fw-semibold">Rp {{ number_format($ytdCost, 0, ',', '.') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Lifetime</div>
                            <div class="fw-semibold">Rp {{ number_format($lifetimeCost, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Service history --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Service History</span>
            <div class="ms-auto d-flex gap-2">
                <select class="form-select form-select-sm" wire:model.live="year" style="width:auto">
                    <option value="all">All years</option>
                    @for ($y = now()->year; $y >= now()->year - 10; $y--)
                        <option>{{ $y }}</option>
                    @endfor
                </select>
                <input class="form-control form-control-sm" style="width:200px" placeholder="Filter workshop…"
                    wire:model.live.debounce.300ms="workshop">
                <select class="form-select form-select-sm" wire:model.live="perPage" style="width:auto">
                    <option>10</option>
                    <option selected>20</option>
                    <option>50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive" style="max-height:70vh; overflow:auto">
            <table class="table align-middle table-hover mb-0">
                <thead class="position-sticky top-0 bg-body text-muted small">
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-nowrap">Odometer</th>
                        <th scope="col">Workshop</th>
                        <th scope="col">Items</th>
                        <th scope="col" class="text-nowrap">Total Cost</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        @php
                            $items = $r->items ?? collect();
                            $preview = $items->take(3);
                            $rest = $items->skip(3);
                            $collapseId = 'svc' . $r->id . '-items';
                        @endphp
                        <tr wire:key="svc-row-{{ $r->id }}">
                            <td class="text-nowrap">{{ optional($r->service_date)->isoFormat('DD MMM YYYY') }}</td>
                            <td>{{ number_format($r->odometer ?? 0) }} km</td>
                            <td>{{ $r->workshop ?? 'Internal' }}</td>
                            <td class="small">
                                @if ($items->isNotEmpty())
                                    <div class="d-flex flex-wrap align-items-center gap-1">
                                        {{-- first 3 as badges --}}
                                        @foreach ($preview as $it)
                                            <span class="badge rounded-pill text-bg-light border me-1 mb-1">
                                                {{ $it->part_name }}
                                                <span class="opacity-75">({{ $it->action }})</span>
                                                @if ($it->qty)
                                                    <span class="opacity-75">—
                                                        {{ rtrim(rtrim(number_format($it->qty, 2, '.', ''), '0'), '.') }}
                                                        {{ $it->uom }}</span>
                                                @endif
                                            </span>
                                        @endforeach

                                        {{-- reveal more, same badge style --}}
                                        @if ($rest->isNotEmpty())
                                            <a class="small text-decoration-none ms-1" data-bs-toggle="collapse"
                                                href="#{{ $collapseId }}" role="button" aria-expanded="false"
                                                aria-controls="{{ $collapseId }}">
                                                +{{ $rest->count() }} more
                                            </a>

                                            <div class="collapse w-100 mt-1" id="{{ $collapseId }}">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($rest as $it)
                                                        <span
                                                            class="badge rounded-pill text-bg-light border me-1 mb-1">
                                                            {{ $it->part_name }}
                                                            <span class="opacity-75">({{ $it->action }})</span>
                                                            @if ($it->qty)
                                                                <span class="opacity-75">—
                                                                    {{ rtrim(rtrim(number_format($it->qty, 2, '.', ''), '0'), '.') }}
                                                                    {{ $it->uom }}</span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                                <a class="small text-decoration-none d-inline-block mt-1"
                                                    data-bs-toggle="collapse" href="#{{ $collapseId }}"
                                                    aria-controls="{{ $collapseId }}">
                                                    Show less
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">No items</span>
                                @endif
                            </td>

                            <td class="text-nowrap">Rp {{ number_format($r->total_cost, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('services.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if ($canManage)
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="deleteService({{ $r->id }})"
                                        wire:confirm="Delete service on {{ optional($r->service_date)->isoFormat('DD MMM YYYY') }} ({{ number_format($r->odometer ?? 0) }} km)? This cannot be undone."
                                        wire:loading.attr="disabled" wire:target="deleteService">
                                        <span class="spinner-border spinner-border-sm me-1" wire:loading
                                            wire:target="deleteService"></span>
                                        <i class="bi bi-trash" wire:loading.remove wire:target="deleteService"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <div class="mb-2"><i class="bi bi-clipboard2-x fs-2"></i></div>
                                <div class="fw-semibold">No records yet.</div>
                                <div class="small">Add the first service record to get started.</div>
                                @if (!$vehicle->is_sold)
                                    <a class="btn btn-sm btn-primary mt-2"
                                        href="{{ route('services.create', $vehicle) }}">
                                        <i class="bi bi-plus-lg me-1"></i> Add Service
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .table thead th {
            z-index: 1;
        }

        /* helps sticky header sit above shadow */
        .table-hover tbody tr:hover {
            background: var(--bs-light-bg-subtle, #f8f9fa);
        }

        /* keeps pill text readable on light themes */
        .badge.text-bg-light {
            color: var(--bs-body-color);
        }

        /* truncate super long part names inside pills */
        .badge {
            max-width: 100%;
        }

        .badge>span {
            white-space: nowrap;
        }
    </style>
</div>
