<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h5 mb-0">
                {{ $template?->id ? 'Edit Rule Template' : 'Create Rule Template' }}
            </h1>
            @if ($template?->id)
                <div class="small text-muted">#{{ $template->id }}</div>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.approvals.rules.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button class="btn btn-primary" wire:click="save">
                <i class="bi bi-save"></i> Save
            </button>
        </div>
    </div>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    {{-- Template fields --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Model Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('model_type') is-invalid @enderror"
                        wire:model.defer="model_type">
                        @foreach (config('approvals.approvables') as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('model_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Must match the Eloquent class name used by the approvable.</div>
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
                        <input class="form-check-input" type="checkbox" wire:model.defer="active">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Match Expr (JSON)</label>
                    <textarea rows="4" class="form-control @error('match_expr') is-invalid @enderror"
                        placeholder='{"department":"FIN","amount_gt":100000000}' wire:model.defer="match_expr"></textarea>
                    @error('match_expr')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="small text-muted mt-1">
                        Supported keys (example): <code>department</code>, <code>amount_gt</code>,
                        <code>amount_gte</code>,
                        <code>amount_lte</code>, <code>any_tags</code>. Leave blank for a generic rule.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Steps --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Steps</div>
                <button class="btn btn-sm btn-outline-primary" wire:click="addStep" type="button">
                    <i class="bi bi-plus-lg"></i> Add step
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 8%">Seq</th>
                            <th style="width: 18%">Approver Type</th>
                            <th style="width: 24%">Approver ID</th>
                            <th style="width: 12%">Final?</th>
                            <th style="width: 22%">Reorder</th>
                            <th style="width: 16%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($steps as $i => $s)
                            <tr>
                                <td>
                                    <input type="number" min="1"
                                        class="form-control @error('steps.' . $i . '.sequence') is-invalid @enderror"
                                        wire:model.defer="steps.{{ $i }}.sequence">
                                    @error('steps.' . $i . '.sequence')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td>
                                    <select
                                        class="form-select @error('steps.' . $i . '.approver_type') is-invalid @enderror"
                                        wire:model.live="steps.{{ $i }}.approver_type">
                                        <option value="user">User</option>
                                        <option value="role">Role</option>
                                    </select>
                                    @error('steps.' . $i . '.approver_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td>
                                    <input type="number" min="1"
                                        class="form-control @error('steps.' . $i . '.approver_id') is-invalid @enderror"
                                        placeholder="User/Role ID"
                                        wire:model.defer="steps.{{ $i }}.approver_id">
                                    @error('steps.' . $i . '.approver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>

                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model.defer="steps.{{ $i }}.final">
                                </td>

                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-secondary"
                                            wire:click="moveUp({{ $i }})" @disabled($i === 0)>
                                            ↑
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            wire:click="moveDown({{ $i }})"
                                            @disabled($i === count($steps) - 1)>↓</button>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger"
                                        wire:click="removeStep({{ $i }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No steps yet. Add one to get
                                    started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary" wire:click="save"><i class="bi bi-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
