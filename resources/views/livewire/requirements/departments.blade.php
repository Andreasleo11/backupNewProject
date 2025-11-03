<div class="container py-4">
    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('requirements.index') }}">Requirements</a></li>
            <li class="breadcrumb-item"><a href="{{ route('requirements.edit', $req) }}">{{ $req->code }}</a></li>
            <li class="breadcrumb-item active">Departments</li>
        </ol>
    </nav>

    {{-- Header + requirement facts --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
        <h1 class="h5 mb-0">{{ $req->name }} — Departments</h1>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge text-bg-primary-subtle">Min {{ $req->min_count }}</span>
            <span class="badge text-bg-{{ $req->validity_days ? 'secondary' : 'light' }}">
                {{ $req->validity_days ? "Valid ≤ {$req->validity_days} days" : 'No expiry' }}
            </span>
            <span class="badge text-bg-info">{{ ucfirst($req->frequency) }}</span>
            @if ($req->requires_approval)
                <span class="badge text-bg-warning"><i class="bi bi-shield-check me-1"></i>Approval required</span>
            @else
                <span class="badge text-bg-success">No approval</span>
            @endif
        </div>
    </div>

    {{-- Summary stats --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Assigned</div>
                    <div class="fs-5 fw-semibold">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">OK</div>
                    <div class="fs-5 fw-semibold text-success">{{ $summary['ok'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Pending</div>
                    <div class="fs-5 fw-semibold text-warning">{{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Missing</div>
                    <div class="fs-5 fw-semibold text-danger">{{ $summary['missing'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control ps-5" placeholder="Search department…"
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="status">
                        <option value="all">All statuses</option>
                        <option value="ok">OK</option>
                        <option value="pending">Pending</option>
                        <option value="missing">Missing</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="perPage">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Department</th>
                    <th style="width:320px">Progress</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $row)
                    <tr wire:key="dept-row-{{ $row['dept']->id }}">
                        <td>
                            <div class="fw-semibold">{{ $row['dept']->name }}</div>
                            <div class="small text-muted">{{ $row['dept']->code ?? '—' }}</div>
                        </td>
                        <td>
                            {{-- progress bar shows valid/min visually --}}
                            @php
                                $p = min(100, (int) round(($row['valid'] / max(1, $row['min'])) * 100));
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" role="progressbar"
                                    aria-valuenow="{{ $p }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar {{ $p == 100 ? 'bg-success' : '' }}"
                                        style="width: {{ $p }}%"></div>
                                </div>
                                <span class="small text-muted">{{ $row['valid'] }} / {{ $row['min'] }}</span>
                                @if (($row['pending'] ?? 0) > 0)
                                    <span class="badge text-bg-warning">Pending {{ $row['pending'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php $clr = ['OK'=>'success','Pending'=>'warning','Missing'=>'danger'][$row['status']] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $clr }}">{{ $row['status'] }}</span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                                href="{{ route('departments.compliance', $row['dept']) }}">
                                Manage
                            </a>
                            <button class="btn btn-sm btn-primary"
                                x-on:click="$dispatch('open-upload', { requirementId: {{ $req->id }}, departmentId: {{ $row['dept']->id }} })">
                                Upload
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No departments assigned to this
                            requirement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer / Pagination --}}
    <div class="mt-2">
        {{ $items->links() }}
    </div>

    {{-- Single uploader instance (outside table for stability) --}}
    <livewire:requirements.upload />

</div>

@pushOnce('extraCss')
    <style>
        .form-control:focus,
        .form-select:focus,
        .btn:focus {
            box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
        }

        .table> :not(caption)>*>* {
            padding-top: .65rem;
            padding-bottom: .65rem;
        }
    </style>
@endPushOnce

@pushOnce('extraJs')
    <script>
        Livewire.on('upload:done', () => {
            location.reload(); // Reloads the entire page
        });
    </script>
@endPushOnce
