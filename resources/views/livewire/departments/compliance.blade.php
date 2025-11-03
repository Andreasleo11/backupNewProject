<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">{{ $department->name }} — Compliance</h1>
        <div class="w-50">
            <div class="progress" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0"
                aria-valuemax="100">
                <div class="progress-bar {{ $percent == 100 ? 'bg-success' : '' }}" style="width: {{ $percent }}%">
                    {{ $percent }}%
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control ps-5"
                            placeholder="Search requirement by code or name…" wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select form-select-sm" wire:model.live="status" title="Status">
                        <option value="all">All</option>
                        <option value="ok">OK</option>
                        <option value="pending">Pending</option>
                        <option value="missing">Missing</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select form-select-sm" wire:model.live="sort" title="Sort">
                        <option value="code">Sort: Code</option>
                        <option value="name">Sort: Name</option>
                        <option value="percent">Sort: % Complete</option>
                        <option value="expires">Sort: Expires</option>
                    </select>
                </div>
                <div class="col-12 col-lg-3 d-flex align-items-center justify-content-lg-end gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="onlyUnmet" wire:model.live="onlyUnmet">
                        <label for="onlyUnmet" class="form-check-label">Show unmet only</label>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="sortBy('{{ $sort }}')">
                        <i class="bi {{ $dir === 'asc' ? 'bi-sort-down' : 'bi-sort-up' }}"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light sticky-top">
                <tr>
                    <th role="button" wire:click="sortBy('code')">
                        Code
                        @if ($sort === 'code')
                            <i class="bi {{ $dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                        @endif
                    </th>
                    <th role="button" wire:click="sortBy('name')">
                        Requirement
                        @if ($sort === 'name')
                            <i class="bi {{ $dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                        @endif
                    </th>
                    <th style="width:340px" role="button" wire:click="sortBy('percent')">
                        Progress
                        @if ($sort === 'percent')
                            <i class="bi {{ $dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                        @endif
                    </th>
                    <th role="button" wire:click="sortBy('expires')" title="Latest valid-until">
                        Expires / Next due
                        @if ($sort === 'expires')
                            <i class="bi {{ $dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                        @endif
                    </th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $rows = $this->filteredSortedRows; @endphp
                @forelse($rows as $r)
                    @php
                        $p = (int) $r['percent'];
                        $expires = $r['last_valid_until']?->format('Y-m-d');
                        $due = $r['next_due']?->format('Y-m-d');
                    @endphp
                    <tr @class([
                        'table-warning' => $p < 100,
                    ])>
                        <td class="text-muted small">{{ $r['code'] }}</td>
                        <td>
                            <div class="fw-semibold" title="{{ $r['allowed_summary'] }}">{{ $r['name'] }}</div>
                            <div class="small text-muted">
                                Min {{ $r['min'] }}
                                @if ($r['requires_approval'])
                                    · needs approval
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" role="progressbar"
                                    aria-valuenow="{{ $p }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar {{ $p == 100 ? 'bg-success' : '' }}"
                                        style="width: {{ $p }}%"></div>
                                </div>
                                <span class="small text-muted">{{ $r['valid_count'] }} / {{ $r['min'] }}</span>
                                @if (($r['pending'] ?? 0) > 0)
                                    <span class="badge text-bg-warning">Pending {{ $r['pending'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($expires)
                                <span class="badge text-bg-light border">exp {{ $expires }}</span>
                            @elseif($due)
                                <span class="badge text-bg-danger-subtle text-danger">due {{ $due }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @php $clr = ['OK'=>'success','Pending'=>'warning','Missing'=>'danger'][$r['status']] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $clr }}">{{ $r['status'] }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" wire:click="openUpload({{ $r['id'] }})">
                                    Upload
                                </button>
                                <button class="btn btn-sm btn-outline-primary" title="Recent Uploads"
                                    wire:click="$dispatch('open-recent-uploads', { requirementId: {{ $r['id'] }}, departmentId: {{ $department->id }} })">
                                    <i class="bi bi-clock-history"></i>
                                    @if ($r['pending'] ?? (0 ?? 0))
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning">
                                            {{ $r['pending'] }}
                                        </span>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No assigned requirements match your
                            filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="d-md-none">
        @php $rows = $this->filteredSortedRows; @endphp
        @forelse($rows as $r)
            @php
                $p = (int) $r['percent'];
                $expires = $r['last_valid_until']?->format('Y-m-d');
                $due = $r['next_due']?->format('Y-m-d');
                $clr = ['OK' => 'success', 'Pending' => 'warning', 'Missing' => 'danger'][$r['status']] ?? 'secondary';
            @endphp
            <div class="card shadow-sm mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-muted">{{ $r['code'] }}</div>
                            <div class="fw-semibold">{{ $r['name'] }}</div>
                            <div class="small text-muted">Min {{ $r['min'] }} @if ($r['requires_approval'])
                                    · needs approval
                                @endif
                            </div>
                        </div>
                        <span class="badge text-bg-{{ $clr }}">{{ $r['status'] }}</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress" role="progressbar" aria-valuenow="{{ $p }}"
                            aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar {{ $p == 100 ? 'bg-success' : '' }}"
                                style="width: {{ $p }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 small">
                            <span class="text-muted">{{ $r['valid_count'] }} / {{ $r['min'] }}</span>
                            <div class="d-flex align-items-center gap-2">
                                @if (($r['pending'] ?? 0) > 0)
                                    <span class="badge text-bg-warning">Pending {{ $r['pending'] }}</span>
                                @endif
                                @if ($expires)
                                    <span class="badge text-bg-light border">exp {{ $expires }}</span>
                                @elseif($due)
                                    <span class="badge text-bg-danger-subtle text-danger">due
                                        {{ $due }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <a class="btn btn-sm btn-outline-secondary w-50"
                                href="{{ route('departments.compliance', ['department' => $department, 'requirement' => $r['id']]) }}">Manage</a>
                            <button class="btn btn-sm btn-primary w-50"
                                wire:click="openUpload({{ $r['id'] }})">Upload</button>
                            <button class="btn btn-sm btn-outline-primary w-50"
                                wire:click="$dispatch('open-recent-uploads', { requirementId: {{ $r['id'] }}, departmentId: {{ $department->id }} })">Recent
                                Uploads</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <div class="fs-2 mb-2">🔎</div>No assigned requirements match your filters.
                </div>
            </div>
        @endforelse
    </div>

    <livewire:requirements.upload :key="'uploader-' . $department->id" />
    <livewire:requirements.recent-uploads />
</div>

@pushOnce('extraCss')
    <style>
        .table> :not(caption)>*>* {
            padding-top: .65rem;
            padding-bottom: .65rem;
        }

        .form-control:focus,
        .form-select:focus,
        .btn:focus {
            box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
        }

        .table-warning-subtle {
            background-color: rgba(255, 193, 7, .05);
        }

    </style>
@endPushOnce
