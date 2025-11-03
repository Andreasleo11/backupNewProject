<div class="container py-4">
    {{-- Header / Toolbar --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h1 class="h4 mb-0">Requirements</h1>
            <span class="badge text-bg-light">{{ $items->total() }} total</span>
        </div>
        <a href="{{ route('requirements.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Requirement
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control ps-5" placeholder="Search by code or name…"
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                {{-- Frequency filter --}}
                <div class="col-6 col-md-2">
                    <select class="form-select" wire:model.live="filterFreq">
                        <option value="">All frequencies</option>
                        <option value="once">Once</option>
                        <option value="yearly">Yearly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                {{-- Approval filter --}}
                <div class="col-6 col-md-2">
                    <select class="form-select" wire:model.live="filterApproval">
                        <option value="">Approval: All</option>
                        <option value="1">Required</option>
                        <option value="0">Not required</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="col-6 col-md-2">
                    <div class="input-group">
                        <select class="form-select" wire:model.live="sort">
                            <option value="name">Sort: Name</option>
                            <option value="code">Sort: Code</option>
                            <option value="min_count">Sort: Min</option>
                            <option value="frequency">Sort: Frequency</option>
                            <option value="requires_approval">Sort: Approval</option>
                        </select>
                        <button class="btn btn-outline-secondary" wire:click="toggleDir" title="Toggle direction">
                            <i class="bi {{ $dir === 'asc' ? 'bi-arrow-down-up' : 'bi-arrow-up-down' }}"></i>
                        </button>
                    </div>
                </div>

                {{-- Per page --}}
                <div class="col-6 col-md-1">
                    <select class="form-select" wire:model.live="perPage" title="Per page">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop TABLE --}}
    <div class="table-responsive d-none d-lg-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th role="button" wire:click="sortBy('code')">Code @include('partials.sortchev', ['field' => 'code'])</th>
                    <th role="button" wire:click="sortBy('name')">Name @include('partials.sortchev', ['field' => 'name'])</th>
                    <th role="button" wire:click="sortBy('min_count')">Min @include('partials.sortchev', ['field' => 'min_count'])</th>
                    <th role="button" wire:click="sortBy('frequency')">Frequency @include('partials.sortchev', ['field' => 'frequency'])</th>
                    <th role="button" wire:click="sortBy('requires_approval')">Approval @include('partials.sortchev', ['field' => 'requires_approval'])
                    </th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $r)
                    <tr>
                        <td class="text-muted fw-semibold">{{ $r->code }}</td>
                        <td>{{ $r->name }}</td>
                        <td><span class="badge text-bg-primary-subtle">{{ $r->min_count }}</span></td>
                        <td>
                            @php $fc=['once'=>'secondary','yearly'=>'info','quarterly'=>'warning','monthly'=>'success']; @endphp
                            <span
                                class="badge text-bg-{{ $fc[$r->frequency] ?? 'secondary' }}">{{ ucfirst($r->frequency) }}</span>
                        </td>
                        <td>
                            @if ($r->requires_approval)
                                <span class="badge text-bg-info"><i class="bi bi-shield-check me-1"></i>Required</span>
                            @else
                                <span class="badge text-bg-light"><i class="bi bi-check2-circle me-1"></i>Not
                                    required</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('requirements.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </a>
                                <a href="{{ route('requirements.departments', $r) }}" class="btn btn-sm btn-outline-primary">Departments</a>
                                {{-- room for more actions later --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <div class="fs-2 mb-2">🤷‍♂️</div>
                                No requirements found{{ $search ? " for “$search”" : '' }}.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile CARDS --}}
    <div class="d-lg-none">
        @forelse ($items as $r)
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">{{ $r->code }}</div>
                            <div class="fw-semibold">{{ $r->name }}</div>
                        </div>
                        <a href="{{ route('requirements.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge text-bg-primary-subtle">Min {{ $r->min_count }}</span>
                        @php $fc=['once'=>'secondary','yearly'=>'info','quarterly'=>'warning','monthly'=>'success']; @endphp
                        <span
                            class="badge text-bg-{{ $fc[$r->frequency] ?? 'secondary' }}">{{ ucfirst($r->frequency) }}</span>
                        @if ($r->requires_approval)
                            <span class="badge text-bg-info">Approval</span>
                        @else
                            <span class="badge text-bg-light">No approval</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <div class="fs-2 mb-2">🔎</div>No requirements yet.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Footer / Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
        </div>
        {{ $items->onEachSide(1)->links() }}
    </div>
</div>

@pushOnce('styles')
    <style>
        .table> :not(caption)>*>* {
            padding-top: .65rem;
            padding-bottom: .65rem;
        }

        /* if you have a fixed navbar */
        .form-control:focus,
        .form-select:focus,
        .btn:focus {
            box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
        }
    </style>
@endPushOnce
