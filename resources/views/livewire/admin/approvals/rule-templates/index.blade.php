<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Approval Rule Templates</h1>
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="{{ route('admin.approvals.rules.create') }}">
                <i class="bi bi-plus-lg"></i> New (full form)
            </a>
            <button class="btn btn-outline-primary" wire:click="$set('createOpen', true)">
                Quick create
            </button>
        </div>
    </div>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input class="form-control" placeholder="Search by name / code / model"
                wire:model.live.debounce.300ms="search">
        </div>
    </div>

    {{-- Quick create collapsible --}}
    @if ($createOpen)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Model Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('model_type') is-invalid @enderror"
                            wire:model.defer="model_type">
                            @foreach (config('approvals.approvables') as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Code</label>
                        <input class="form-control" wire:model.defer="code">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Priority</label>
                        <input type="number" class="form-control @error('priority') is-invalid @enderror"
                            wire:model.defer="priority" min="1" max="9999">
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" wire:model.defer="active">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Match Expr (JSON)</label>
                        <textarea rows="3" class="form-control @error('match_expr') is-invalid @enderror"
                            placeholder='{"department":"FIN","amount_gt":100000000}' wire:model.defer="match_expr"></textarea>
                        @error('match_expr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional filters used by the resolver.</div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button class="btn btn-outline-secondary" wire:click="$set('createOpen', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="create">Create</button>
                </div>
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Priority</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Model Type</th>
                    <th>Active</th>
                    <th>Steps</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($templates as $t)
                    <tr>
                        <td>{{ $t->priority }}</td>
                        <td class="fw-semibold">{{ $t->name }}</td>
                        <td class="text-muted">{{ $t->code ?: '—' }}</td>
                        <td><code class="small">{{ $t->model_type }}</code></td>
                        <td>
                            <span class="badge text-bg-{{ $t->active ? 'success' : 'secondary' }}">
                                {{ $t->active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>{{ $t->steps()->count() }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.approvals.rules.edit', $t->id) }}"
                                class="btn btn-sm btn-outline-primary">Edit</a>
                            <button class="btn btn-sm btn-outline-danger"
                                wire:click="delete({{ $t->id }})">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $templates->links() }}
</div>
