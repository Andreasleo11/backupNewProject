<div class="container py-4">

    {{-- Header + toolbar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-3">
            <h1 class="h5 mb-0">Requirement Uploads — Review</h1>
            <span class="badge text-bg-light">Showing {{ $rows->total() }} uploads</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-5" placeholder="Search by file, requirement, dept…"
                    wire:model.live.debounce.300ms="q" style="width:260px">
            </div>
        </div>
    </div>

    {{-- Inline date range --}}
    <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap mt-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <label class="small text-muted me-1">Date</label>
            <input type="date" class="form-control" wire:model.live="date_from" style="width: 150px">
            <span class="mx-1 small text-muted">→</span>
            <input type="date" class="form-control" wire:model.live="date_to" style="width: 150px">

            {{-- Quick ranges --}}
            <div class="btn-group btn-group-sm ms-1" role="group" aria-label="Quick ranges">
                <button class="btn btn-outline-secondary" wire:click="setRange('7d')">Last 7d</button>
                <button class="btn btn-outline-secondary" wire:click="setRange('30d')">Last 30d</button>
                <button class="btn btn-outline-secondary" wire:click="setRange('month')">This month</button>
            </div>

            <button class="btn btn-sm btn-link text-decoration-none ms-1" wire:click="clearDateRange">
                Clear
            </button>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <select class="form-select" wire:model.live="status" style="width:160px" title="Status">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>

            <select class="form-select" wire:model.live="perPage" style="width:110px" title="Per page">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
            {{-- MIME + Expiring --}}
            <input type="text" class="form-control" wire:model.live="mime_like" placeholder="MIME contains…"
                style="width: 180px">
            <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" id="onlyExpiring" wire:model.live="only_expiring">
                <label class="form-check-label small" for="onlyExpiring">Expiring ≤ 30d</label>
            </div>
        </div>
    </div>

    {{-- Bulk actions --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <div class="small text-muted">
            <span class="me-2">Selected: <strong>{{ count($selected) }}</strong></span>
            <button class="btn btn-sm btn-link text-decoration-none" wire:click="clearSelection">Clear</button>
        </div>
        <div class="d-flex gap-2">
            @can('approve-requirements')
                <button class="btn btn-sm btn-success" wire:click="bulkApprove" @disabled(count($selected) === 0)">
                    Approve selected
                </button>
                <button class="btn btn-sm btn-outline-danger" wire:click="bulkReject" @disabled(count($selected) === 0)">
                    Reject selected
                </button>
            @endcan
            <button class="btn btn-sm btn-outline-secondary" wire:click="exportCsv">
                <i class="bi bi-download me-1"></i>Export CSV
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:36px">
                        <input type="checkbox" class="form-check-input"
                            wire:click="togglePageSelection($event.target.checked)">
                    </th>
                    <th role="button" wire:click="sortBy('requirements.name')">
                        Requirement {!! $this->sortIcon('requirements.name') !!}
                    </th>

                    <th role="button" wire:click="sortBy('departments.name')">
                        Department {!! $this->sortIcon('departments.name') !!}
                    </th>

                    <th role="button" wire:click="sortBy('status')">
                        Status {!! $this->sortIcon('status') !!}
                    </th>
                    <th role="button" wire:click="sortBy('valid_until')">
                        Validity {!! $this->sortIcon('valid_until') !!}
                    </th>

                    <th role="button" wire:click="sortBy('created_at')">
                        Uploaded {!! $this->sortIcon('created_at') !!}
                    </th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $u)
                    @php
                        $badge =
                            ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$u->status] ??
                            'secondary';
                        $daysLeft = $u->valid_until ? now()->diffInDays($u->valid_until, false) : null;
                    @endphp
                    <tr wire:key="upload-{{ $u->id }}">
                        <td>
                            <input type="checkbox" class="form-check-input" wire:model.live="selected"
                                value="{{ $u->id }}">
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $u->req_name }}</div>
                            <div class="small text-muted">{{ $u->req_code }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $u->dept_name }}</div>
                            <div class="small text-muted">{{ $u->dept_code ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $u->original_name }}</span>
                                <small class="text-muted">{{ $u->mime_type }} ·
                                    {{ number_format($u->size / 1024, 1) }}
                                    KB</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($u->status) }}</span>
                            @if ($u->review_notes)
                                <i class="bi bi-chat-left-text ms-1 text-muted" title="Has notes"></i>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ $u->valid_from?->format('Y-m-d') ?? '—' }} →
                                    {{ $u->valid_until?->format('Y-m-d') ?? '—' }}</span>
                                @if (!is_null($daysLeft))
                                    <small class="text-muted">
                                        <span
                                            class="badge {{ $daysLeft < 0 ? 'text-bg-secondary' : ($daysLeft <= 7 ? 'text-bg-danger' : ($daysLeft <= 14 ? 'text-bg-warning' : 'text-bg-light')) }}">
                                            {{ $daysLeft < 0 ? 'expired' : "in {$daysLeft}d" }}
                                        </span>
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td><small class="text-muted">{{ $u->created_at->format('Y-m-d H:i') }}</small></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                                href="{{ URL::signedRoute('uploads.download', ['upload' => $u->id]) }}">
                                Download
                            </a>
                            @can('approve-requirements')
                                <button class="btn btn-sm btn-outline-primary"
                                    wire:click="openDecision({{ $u->id }})">
                                    Decide
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No uploads match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="small text-muted">
            Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}
        </div>
        {{ $rows->links() }}
    </div>

    {{-- Decision Modal (Bootstrap) --}}
    <div wire:ignore.self class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approval Decision</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($active)
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <div class="small text-muted">File</div>
                                    <div class="fw-semibold">{{ $active['original_name'] }}</div>
                                    <div class="small text-muted">{{ $active['mime_type'] }} ·
                                        {{ number_format($active['size'] / 1024, 1) }} KB</div>
                                </div>
                                <div class="mb-2">
                                    <div class="small text-muted">Requirement</div>
                                    <div class="fw-semibold">{{ $active['req_name'] }} <span
                                            class="text-muted">({{ $active['req_code'] }})</span></div>
                                </div>
                                <div class="mb-2">
                                    <div class="small text-muted">Department</div>
                                    <div class="fw-semibold">{{ $active['dept_name'] }} <span
                                            class="text-muted">({{ $active['dept_code'] ?? '—' }})</span></div>
                                </div>
                                <div class="mb-2">
                                    <div class="small text-muted">Validity</div>
                                    <div>{{ $active['valid_from'] ?? '—' }} → {{ $active['valid_until'] ?? '—' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a class="btn btn-outline-secondary w-100 mb-2" href="{{ $active['download_url'] }}"
                                    target="_blank" rel="noopener">
                                    <i class="bi bi-box-arrow-down me-1"></i> Download
                                </a>
                                @if (Str::startsWith($active['mime_type'], 'image/'))
                                    <img src="{{ $active['preview_url'] }}" class="img-fluid rounded border"
                                        alt="preview">
                                @endif
                            </div>
                        </div>
                        <hr>
                    @endif

                    <label class="form-label">Notes (optional)</label>
                    <textarea class="form-control" rows="3" wire:model.defer="review_notes"
                        placeholder="Reason or remarks for the decision"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    @if ($uploadId)
                        <button class="btn btn-danger" wire:click="reject({{ $uploadId }})"
                            data-bs-dismiss="modal">Reject</button>
                        <button class="btn btn-success" wire:click="approve({{ $uploadId }})"
                            data-bs-dismiss="modal">Approve</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.delay>
        <div class="position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(255,255,255,.5); z-index: 1050;">
            <div class="position-absolute top-50 start-50 translate-middle text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div> Loading…
            </div>
        </div>
    </div>
</div>
@pushOnce('extraCss')
    <style>
        th[role="button"] {
            cursor: pointer;
            user-select: none;
        }

        th[role="button"]:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
    </style>
@endPushOnce

@pushOnce('extraJs')
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('open-decision-modal', () => {
                const el = document.getElementById('decisionModal');
                new bootstrap.Modal(el).show();
            });
        });
    </script>
@endPushOnce
