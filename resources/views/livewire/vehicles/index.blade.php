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

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
            {{-- Search --}}
            <div class="input-group" style="max-width:320px">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search plate / brand / model / driver"
                    wire:model.live.debounce.300ms="q">
                @if ($q !== '')
                    <button class="btn btn-outline-secondary" wire:click="$set('q','')"><i class="bi bi-x"></i></button>
                @endif
            </div>

            {{-- Status filter --}}
            @php use App\Enums\VehicleStatus; @endphp
            @if ($fullFeature)
                <div class="btn-group" role="group" aria-label="Status filter">
                    {{-- All --}}
                    <input type="radio" class="btn-check" id="st-all" value="all" wire:model.live="status"
                        autocomplete="off">
                    <label class="btn btn-outline-secondary" for="st-all">
                        <i class="bi bi-ui-checks-grid me-1"></i>All
                    </label>

                    {{-- Enum-driven options --}}
                    @foreach (VehicleStatus::cases() as $st)
                        <input type="radio" class="btn-check" id="st-{{ $st->value }}" value="{{ $st->value }}"
                            wire:model.live="status" autocomplete="off">
                        <label class="btn btn-outline-{{ $st->variant() }}" for="st-{{ $st->value }}">
                            <i class="bi bi-{{ $st->icon() }} me-1"></i>{{ $st->label() }}
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- Per page --}}
            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Per page</span>
                    <select class="form-select form-select-sm" style="width:auto" wire:model.live="perPage">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> New Vehicle
                </a>
            </div>


        </div>
    </div>

    {{-- Desktop table (md+) --}}
    <div class="card border-0 shadow-sm d-none d-md-block position-relative">
        <div class="table-responsive" wire:loading.class="opacity-50" wire:target="q,status,perPage,page,sort,dir">
            <table class="table align-middle table-hover mb-0">
                @php
                    $aria = fn($f) => $sort === $f ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
                    $chev = fn($f) => $sort === $f
                        ? ($dir === 'asc'
                            ? 'bi-chevron-up'
                            : 'bi-chevron-down')
                        : 'bi-chevron-expand';
                @endphp
                @if ($fullFeature)
                    <thead class="position-sticky top-0 bg-body">
                        <tr class="text-muted small">
                            <th scope="col" role="button" wire:click="sortBy('plate_number')"
                                aria-sort="{{ $aria('plate_number') }}">
                                Vehicle <i class="bi {{ $chev('plate_number') }} ms-1"></i>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('driver_name')"
                                aria-sort="{{ $aria('driver_name') }}" class="text-nowrap">Driver
                                <i class="bi {{ $chev('driver_name') }} ms-1"></i>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('odometer')"
                                aria-sort="{{ $aria('odometer') }}" class="text-nowrap">
                                Odometer <i class="bi {{ $chev('odometer') }} ms-1"></i>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('status')"
                                aria-sort="{{ $aria('status') }}">
                                Status <i class="bi {{ $chev('status') }} ms-1"></i>
                            </th>
                            <th scope="col" wire:click="sortBy('last_service_date')"
                                aria-sort="{{ $aria('last_service_date') }}">Last Service <i
                                    class="bi {{ $chev('last_service_date') }} ms-1"></i></th>
                            <th scope="col">Checked / Parts</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($vehicles as $v)
                            @php $last = $v->latestService; @endphp
                            <tr wire:key="veh-{{ $v->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $v->display_name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 22rem">
                                        VIN: <span class="font-monospace">{{ $v->vin ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <i class="bi bi-person me-1 text-muted"></i>{{ $v->driver_name ?? '—' }}
                                </td>
                                <td>{{ number_format($v->odometer) }} <span class="text-muted">km</span></td>
                                <td>
                                    <span class="badge text-bg-{{ $v->status->variant() }}">
                                        <i class="bi bi-{{ $v->status->icon() }} me-1"></i>
                                        {{ $v->status->label() }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    @if ($last)
                                        <div class="fw-semibold">
                                            <time
                                                datetime="{{ $last->service_date->toDateString() }}">{{ $last->service_date->isoFormat('DD MMM YYYY') }}</time>
                                        </div>
                                        <div class="text-muted small">
                                            at {{ $last->workshop ?? 'Internal' }} •
                                            {{ number_format($last->odometer ?? 0) }} km
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @php
                                    /** @var \App\Models\ServiceRecord|null $last */
                                    $last = $v->latestService;
                                    $shown = $last?->items?->count() ?? 0; // how many we actually loaded (<=5)
                                    $total = $last?->items_count ?? $shown;
                                @endphp

                                <td class="small">
                                    @if ($total > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($last?->items ?? collect() as $it)
                                                <span class="badge rounded-pill text-bg-light border me-1 mb-1">
                                                    {{ $it->part_name }} <span
                                                        class="opacity-75">({{ $it->action }})</span>
                                                </span>
                                            @endforeach

                                            @if ($total > $shown)
                                                <span class="text-muted">+{{ $total - $shown }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No items</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="btn-group">
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('vehicles.show', $v) }}" title="View Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('vehicles.edit', $v) }}" title="Edit Vehicle"><i
                                                class="bi bi-pencil"></i>
                                        </a>
                                        <a class="btn btn-sm btn-primary" href="{{ route('services.create', $v) }}"
                                            title="Add Service"> <i class="bi bi-wrench-adjustable"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteVehicle({{ $v->id }})"
                                            wire:confirm="Delete {{ $v->display_name }}? This cannot be undone."
                                            wire:loading.attr="disabled" wire:target="deleteVehicle">
                                            <span class="spinner-border spinner-border-sm" role="status" wire:loading
                                                wire:target="deleteVehicle"></span>
                                            <i class="bi bi-trash" wire:loading.remove
                                                wire:target="deleteVehicle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5">
                                    <div class="text-center text-muted">
                                        <div class="mb-2"><i class="bi bi-truck fs-2"></i></div>
                                        <div class="fw-semibold">No vehicles found</div>
                                        <div class="small mb-3">Try adjusting your search or add a new vehicle.</div>
                                        <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> New Vehicle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @else
                    <thead class="position-sticky top-0 bg-body">
                        <tr class="text-muted small">
                            <th scope="col" role="button" wire:click="sortBy('plate_number')"
                                aria-sort="{{ $aria('plate_number') }}">
                                Vehicle <i class="bi {{ $chev('plate_number') }} ms-1"></i>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('driver_name')"
                                aria-sort="{{ $aria('driver_name') }}" class="text-nowrap">Driver
                                <i class="bi {{ $chev('driver_name') }} ms-1"></i>
                            </th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($vehicles as $v)
                            @php
                                /** @var \App\Models\ServiceRecord|null $last */
                                $last = $v->latestService;
                                $shown = $last?->items?->count() ?? 0; // how many we actually loaded (<=5)
                                $total = $last?->items_count ?? $shown;
                            @endphp
                            <tr wire:key="veh-{{ $v->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $v->display_name }}</div>
                                </td>
                                <td class="text-nowrap">
                                    <i class="bi bi-person me-1 text-muted"></i>{{ $v->driver_name ?? '—' }}
                                </td>

                                <td class="text-end">
                                    <div class="btn-group">
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('vehicles.edit', $v) }}" title="Edit Vehicle"><i
                                                class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5">
                                    <div class="text-center text-muted">
                                        <div class="mb-2"><i class="bi bi-truck fs-2"></i></div>
                                        <div class="fw-semibold">No vehicles found</div>
                                        <div class="small mb-3">Try adjusting your search or add a new vehicle.</div>
                                        <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> New Vehicle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center" wire:loading.class="opacity-50"
            wire:target="q,status,perPage,page,sort,dir">
            <div class="small text-muted">Showing {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }} of
                {{ $vehicles->total() }}</div>
            <div>{{ $vehicles->links() }}</div>
        </div>

        {{-- Replace your overlay div --}}
        <div wire:loading.flex wire:target="q,status,perPage,page,sort,dir"
            class="position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center bg-white bg-opacity-75">
            <div class="spinner-border" role="status" aria-label="Loading"></div>
        </div>

    </div>

    {{-- Mobile cards (< md) --}}
    <div class="d-md-none">
        @forelse($vehicles as $v)
            @php
                $last = $v->latestService;
                $shown = $last?->items?->count() ?? 0;
                $total = $last?->items_total ?? ($last?->items_count ?? $shown);
            @endphp
            @if ($fullFeature)
                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $v->display_name }}</div>
                                <div class="text-muted small">VIN: <span
                                        class="font-monospace">{{ $v->vin ?? '—' }}</span></div>
                            </div>
                            <span class="badge text-bg-{{ $v->status->variant() }}">
                                <i class="bi bi-{{ $v->status->icon() }} me-1"></i>
                                {{ $v->status->label() }}
                            </span>

                        </div>

                        <div class="mt-2 small">
                            <div><i class="bi bi-person me-1"></i>{{ $v->driver_name ?? '—' }}</div>
                            <div><i class="bi bi-speedometer me-1"></i>{{ number_format($v->odometer) }} km</div>
                            <div>
                                <i class="bi bi-wrench-adjustable me-1"></i>
                                @if ($last)
                                    {{ $last->service_date->isoFormat('DD MMM YYYY') }} •
                                    {{ number_format($last->odometer ?? 0) }} km
                                @else
                                    <span class="text-muted">No service yet</span>
                                @endif
                            </div>
                        </div>

                        @if ($total > 0)
                            <div class="mt-2">
                                @foreach ($last?->items ?? collect() as $it)
                                    <span class="badge rounded-pill text-bg-light border me-1 mb-1">
                                        {{ $it->part_name }} <span class="opacity-75">({{ $it->action }})</span>
                                    </span>
                                @endforeach
                                @if ($total > $shown)
                                    <span class="text-muted small">+{{ $total - $shown }} more</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2" wire:loading.class="opacity-50"
                        wire:target="q,status,perPage,page">
                        <a class="btn btn-sm btn-outline-secondary flex-fill"
                            href="{{ route('vehicles.show', $v) }}"><i class="bi bi-eye me-1"></i>Detail</a>
                        <a class="btn btn-sm btn-outline-primary flex-fill"
                            href="{{ route('vehicles.edit', $v) }}"><i class="bi bi-pencil me-1"></i>Edit</a>
                        @if (!$v->is_sold)
                            <a class="btn btn-sm btn-primary flex-fill" href="{{ route('services.create', $v) }}"><i
                                    class="bi bi-wrench-adjustable me-1"></i>Service</a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger flex-fill"
                            wire:click="deleteVehicle({{ $v->id }})"
                            wire:confirm="Delete {{ $v->display_name }}? This cannot be undone."
                            wire:loading.attr="disabled" wire:target="deleteVehicle">
                            <span class="spinner-border spinner-border-sm me-1" role="status" wire:loading
                                wire:target="deleteVehicle"></span>
                            <i class="bi bi-trash" wire:loading.remove wire:target="deleteVehicle"></i>Delete
                        </button>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $v->display_name }}</div>
                            </div>
                        </div>

                        <div class="mt-2 small">
                            <div><i class="bi bi-person me-1"></i>{{ $v->driver_name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2" wire:loading.class="opacity-50"
                        wire:target="q,status,perPage,page">
                        <a class="btn btn-sm btn-outline-primary flex-fill"
                            href="{{ route('vehicles.edit', $v) }}"><i class="bi bi-pencil me-1"></i>Edit</a>
                    </div>
                </div>
            @endif
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted">
                    <div class="mb-2"><i class="bi bi-truck fs-2"></i></div>
                    <div class="fw-semibold">No vehicles found</div>
                    <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-primary mt-2">
                        <i class="bi bi-plus-lg me-1"></i> New Vehicle
                    </a>
                </div>
            </div>
        @endforelse

        <div class="my-3">{{ $vehicles->links() }}</div>
    </div>

    <style>
        .table thead th {
            z-index: 1;
        }

        /* helps sticky header over shadows */
        .table-hover tbody tr:hover {
            background: var(--bs-light-bg-subtle, #f8f9fa);
        }
    </style>
</div>
