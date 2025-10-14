<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Assign Requirement to Departments</h1>
        <div class="small text-muted" wire:loading.delay>Updating…</div>
    </div>

    <div class="row g-4">
        {{-- Left: selection --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    {{-- Requirement selector --}}
                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Requirement <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.live="requirement_id">
                                <option value="">— choose —</option>
                                @foreach ($requirements as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('requirement_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label d-block">Options</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="mandatory"
                                    wire:model.live="is_mandatory">
                                <label class="form-check-label" for="mandatory">Mandatory (counts toward
                                    compliance)</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Department checklist --}}
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        <div class="position-relative" style="min-width: 260px;">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input class="form-control ps-5" placeholder="Search departments by name or code…"
                                wire:model.live.debounce.250ms="deptSearch">
                        </div>

                        <div class="vr d-none d-md-block"></div>

                        <div class="btn-group btn-group-sm" role="group" aria-label="bulk select">
                            <button class="btn btn-outline-secondary" wire:click="selectAll"
                                @disabled(!$requirement_id)>All</button>
                            <button class="btn btn-outline-secondary" wire:click="selectNone">None</button>
                            <button class="btn btn-outline-secondary" wire:click="selectAssigned"
                                @disabled(!$requirement_id)>Assigned</button>
                            <button class="btn btn-outline-secondary" wire:click="selectUnassigned"
                                @disabled(!$requirement_id)>Unassigned</button>
                        </div>

                        <span class="badge text-bg-light ms-auto">
                            Selected: {{ count($department_ids) }}
                        </span>
                    </div>

                    <div class="border rounded p-2" style="max-height: 380px; overflow: auto;">
                        @forelse($departments as $d)
                            @php $checked = in_array($d->id, $department_ids); @endphp
                            <label class="d-flex align-items-center gap-2 py-1 px-2 rounded hover-bg"
                                style="cursor:pointer">
                                <input class="form-check-input mt-0" type="checkbox" value="{{ $d->id }}"
                                    wire:model.live="department_ids">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $d->name }}</div>
                                    <div class="small text-muted">{{ $d->code ?? '—' }}</div>
                                </div>
                                @if (in_array($d->id, $assignedDeptIds))
                                    <span class="badge text-bg-secondary">assigned</span>
                                @endif
                            </label>
                        @empty
                            <div class="text-center text-muted py-4">No departments found.</div>
                        @endforelse
                    </div>

                    @error('department_ids')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-outline-secondary" wire:click="selectNone">Clear</button>
                        @if ($requirement_id && count($department_ids) > 0)
                            <button class="btn btn-outline-danger" wire:click="unassign"
                                wire:confirm="Are you sure you want to unassign selected departments from this requirement?"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Unassign</span>
                                <span wire:loading>Removing…</span>
                            </button>
                        @endif
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled"
                            @disabled(!$requirement_id || count($department_ids) === 0)>
                            <span wire:loading.remove>Save assignments</span>
                            <span wire:loading>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: insights --}}
        <div class="col-lg-4">
            {{-- Requirement facts --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-muted">Requirement</div>
                            <div class="fw-semibold">{{ $req?->name ?? '—' }}</div>
                            <div class="small text-muted">{{ $req?->code ?? '' }}</div>
                        </div>
                        @if ($req)
                            <span class="badge text-bg-light">#{{ $req->id }}</span>
                        @endif
                    </div>

                    @if ($req)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Minimum files</span><span
                                    class="badge text-bg-primary">{{ $req->min_count }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Validity</span>
                                <span
                                    class="badge text-bg-secondary">{{ $req->validity_days ? $req->validity_days . ' days' : 'No expiry' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Frequency</span><span
                                    class="badge text-bg-info">{{ ucfirst($req->frequency) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Approval</span>
                                @if ($req->requires_approval)
                                    <span class="badge text-bg-warning">Required</span>
                                @else
                                    <span class="badge text-bg-success">Not required</span>
                                @endif
                            </li>
                        </ul>

                        <div class="mt-3">
                            <div class="small text-muted mb-1">Allowed file types</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse(($req->allowed_mimetypes ?? []) as $m)
                                    <span class="badge rounded-pill text-bg-light border">{{ $m }}</span>
                                @empty
                                    <span class="text-muted small">Any type</span>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="text-muted small">Choose a requirement to see details.</div>
                    @endif
                </div>
            </div>

            {{-- Preview impact --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="h6 mb-3">Preview</div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Will create new assignments</span>
                        <span class="badge text-bg-success">{{ $willCreate }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Will update existing</span>
                        <span class="badge text-bg-secondary">{{ $willUpdate }}</span>
                    </div>
                    @if ($is_mandatory)
                        <div class="small text-muted mt-2">New/updated assignments will be <strong>mandatory</strong>.
                        </div>
                    @else
                        <div class="small text-muted mt-2">New/updated assignments will be <strong>optional</strong>.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="h6 mb-3">Recent assignments</div>
                    @if ($req && $recent->count())
                        <div class="list-group list-group-flush">
                            @foreach ($recent as $ra)
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <div class="me-2">
                                        <div class="small fw-semibold">
                                            {{ optional($ra->scope)->name ?? 'Department #' . $ra->scope_id }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $ra->is_mandatory ? 'Mandatory' : 'Optional' }}
                                        </div>
                                    </div>
                                    <div class="small text-muted">{{ $ra->created_at?->diffForHumans() }}</div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($req)
                        <div class="text-muted small">No recent activity.</div>
                    @else
                        <div class="text-muted small">Choose a requirement to see recent activity.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        .hover-bg:hover {
            background: rgba(13, 110, 253, .04)
        }
    </style>
@endPushOnce
