<div class="container py-4">
    <div class="d-flex justify-content-between">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('requirements.index') }}">Requirements</a></li>
                <li class="breadcrumb-item active">{{ $requirement?->exists ? 'Edit' : 'Create' }}</li>
            </ol>
        </nav>
        @if ($requirement?->exists)
            <div class="ms-auto">
                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteReq">
                    Delete
                </button>
            </div>
            <div x-data x-init="const el = $refs.delmodal;
            let m;
            const hide = () => {
                if (!m) return;
                m.hide();
            };
            window.addEventListener('hide-delete-modal', hide);">
                <div class="modal fade" id="deleteReq" tabindex="-1" aria-hidden="true" x-ref="delmodal"
                    wire:ignore.self>
                    <div class="modal-dialog">
                        <div class="modal-content border-danger">
                            <div class="modal-header">
                                <h5 class="modal-title text-danger">Delete Requirement</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <p class="mb-2">
                                    You’re about to delete <strong>{{ $requirement->name }}</strong>
                                    (<code>{{ $requirement->code }}</code>).
                                </p>

                                <div class="alert alert-warning">
                                    <div class="d-flex justify-content-between">
                                        <span>Assignments</span>
                                        <strong>{{ $usage['assignments'] }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Uploads</span>
                                        <strong>{{ $usage['uploads'] }}</strong>
                                    </div>
                                    <small class="d-block mt-2">
                                        @if ($usage['assignments'] || $usage['uploads'])
                                            You must detach assignments and remove uploads before deletion.
                                        @else
                                            No assignments or uploads detected. Safe to delete.
                                        @endif
                                    </small>
                                </div>

                                <label class="form-label">Type the code to confirm</label>
                                <input type="text" class="form-control" placeholder="{{ $requirement->code }}"
                                    wire:model.defer="delete_confirm_input" name="delete_confirm_input">
                                @error('delete_confirm_input')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                                <button class="btn btn-danger" wire:click="deleteRequirement"
                                    @disabled($usage['assignments'] || $usage['uploads']) wire:loading.attr="disabled">
                                    @if ($usage['assignments'] || $usage['uploads'])
                                        Resolve usage first
                                    @else
                                        Delete
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center"><i
                class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix:</strong>
            <ul class="mb-0">
                @foreach ($errors->keys() as $field)
                    <li>{{ Str::headline(Str::afterLast($field, '.')) }} has an issue.</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Left: Form --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="h5 mb-0">Requirement {{ $requirement?->exists ? 'Editor' : 'Creator' }}</h1>
                        @if ($requirement?->exists)
                            <span class="badge text-bg-secondary">#{{ $requirement->id }}</span>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" placeholder="ORG_STRUCTURE"
                                    wire:model.live.debounce.400ms="code" wire:keydown.debounce.400ms="checkCodeUnique">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="navigator.clipboard.writeText('{{ $code }}')" title="Copy"><i
                                        class="bi bi-clipboard"></i></button>
                            </div>
                            <div class="form-text">
                                Uppercase, digits, /, _ or -.
                            </div>
                            @if (!is_null($code_is_unique))
                                <div class="small mt-1">
                                    @if ($code_is_unique)
                                        <span class="text-success"><i class="bi bi-check-circle"></i> Available</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle"></i> Already used</span>
                                    @endif
                                </div>
                            @endif
                            @error('code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-md-8">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Organization Structure"
                                wire:model.live.debounce.300ms="name">
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea rows="3" class="form-control" wire:model.live.debounce.300ms="description"
                                placeholder="Explain what files satisfy this requirement, who maintains them, renewal cadence, etc."></textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Friendly file categories --}}
                        <div class="col-12">
                            <label class="form-label">Allowed file types</label>
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="selectAllPresets">Select all</button>

                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="clearPresets">Clear</button>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach ($this->selected_preset_meta as $p)
                                    <span class="badge rounded-pill text-bg-light border">
                                        {{ $p['label'] }}
                                        <button class="btn btn-sm btn-link text-danger p-0 ms-1"
                                            wire:click="removePreset('{{ $p['key'] }}')">&times;</button>
                                    </span>
                                @endforeach
                            </div>

                            <div class="row g-3">
                                @php
                                    $presets = $this->mimePresets();
                                    $icons = [
                                        'pdf' => 'bi-file-earmark-pdf',
                                        'images' => 'bi-image',
                                        'word' => 'bi-file-earmark-word',
                                        'excel' => 'bi-file-earmark-excel',
                                        'ppt' => 'bi-file-earmark-ppt',
                                        'text' => 'bi-file-earmark-text',
                                        'zip' => 'bi-file-earmark-zip',
                                        'visio' => 'bi-diagram-3',
                                        'cad' => 'bi-box', // pick any; Bootstrap Icons doesn’t have CAD
                                    ];
                                @endphp

                                @foreach ($presets as $key => $p)
                                    <div class="col-sm-6 col-md-4">
                                        <label class="card h-100 border-0 shadow-sm selectable-card">
                                            <div class="card-body d-flex align-items-start gap-3">
                                                <input type="checkbox" class="form-check-input mt-1"
                                                    wire:model="selected_presets"
                                                    wire:click.prevent="togglePreset('{{ $key }}')"
                                                    value="{{ $key }}">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <i class="bi {{ $icons[$key] ?? 'bi-file-earmark' }}"></i>
                                                        <span class="fw-semibold">{{ $p['label'] }}</span>
                                                    </div>
                                                    <div class="small text-muted">Opens with: {{ $p['apps'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Advanced: custom types (collapsed by default) --}}
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#customTypes">
                                    Advanced: add a specific type
                                </button>
                                <div class="collapse mt-2" id="customTypes">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @forelse($custom_mimes as $i => $m)
                                            <span class="badge rounded-pill text-bg-light border">
                                                {{ $m }}
                                                <button type="button"
                                                    class="btn btn-sm btn-link text-danger text-decoration-none ms-1 p-0"
                                                    wire:click="removeCustom({{ $i }})">&times;</button>
                                            </span>
                                        @empty
                                            <span class="text-muted">No custom types.</span>
                                        @endforelse
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-plus-circle"></i></span>
                                        <input type="text" class="form-control"
                                            placeholder="e.g. application/json or pdf/jpg/xlsx"
                                            wire:model.defer="custom_input" wire:keydown.enter.prevent="addCustom">
                                        <button class="btn btn-outline-primary" type="button"
                                            wire:click="addCustom">Add</button>
                                    </div>
                                    <div class="form-text">Tip: you can type short forms like <code>pdf</code>,
                                        <code>jpg</code>, <code>xlsx</code> or full MIME.
                                    </div>
                                </div>
                            </div>

                            @error('allowed_mimetypes')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Numbers & cadence --}}
                        <div class="col-md-4">
                            <label class="form-label">Minimum files <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="20" class="form-control"
                                wire:model.live="min_count">
                            @error('min_count')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Validity (days)</label>
                            <input type="number" min="1" max="3650" class="form-control"
                                wire:model.live="validity_days" placeholder="e.g. 365">
                            <div class="form-text">Leave empty if no expiry.</div>
                            @error('validity_days')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">Frequency</label>
                            <div class="btn-group w-100" role="group" aria-label="frequency">
                                @foreach (['once' => 'Once', 'yearly' => 'Yearly', 'quarterly' => 'Quarterly', 'monthly' => 'Monthly'] as $val => $label)
                                    <input type="radio" class="btn-check" name="freq"
                                        id="freq-{{ $val }}" autocomplete="off"
                                        value="{{ $val }}" wire:model.live.debounce.300ms="frequency">
                                    <label class="btn btn-outline-primary"
                                        for="freq-{{ $val }}">{{ $label }}</label>
                                @endforeach
                            </div>
                            @error('frequency')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">Approval</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="requiresApproval"
                                    wire:model="requires_approval">
                                <label class="form-check-label" for="requiresApproval">Requires admin approval to
                                    count</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('requirements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">Save</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Live summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-muted mb-3">Summary</h2>

                    <div class="mb-3">
                        <div class="small text-muted">Code</div>
                        <div class="fw-semibold">{{ $code ?: '—' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Name</div>
                        <div class="fw-semibold">{{ $name ?: '—' }}</div>
                    </div>

                    {{-- Friendly categories --}}
                    <div class="mb-3">
                        <div class="small text-muted">Allowed file types</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($this->selected_preset_meta as $p)
                                <span class="badge rounded-pill text-bg-light border">{{ $p['label'] }}</span>
                            @empty
                                <span class="text-muted">none</span>
                            @endforelse
                        </div>
                        @if (count($this->selected_preset_meta))
                            <div class="small text-muted mt-1">
                                Opens with:
                                {{ collect($this->selected_preset_meta)->pluck('apps')->unique()->implode(', ') }}
                            </div>
                        @endif
                    </div>

                    {{-- What counts line --}}
                    <div class="mb-3">
                        <div class="small text-muted">What counts</div>
                        <div class="fw-semibold">{{ $this->policy_line }}</div>
                    </div>

                    {{-- Details list --}}
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Minimum files
                            <span class="badge text-bg-primary">{{ $min_count }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Validity
                            <span
                                class="badge text-bg-secondary">{{ $validity_days ? $validity_days . ' days' : 'No expiry' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Frequency
                            <span class="badge text-bg-info">{{ $this->frequencyLabel() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Approval
                            @if ($requires_approval)
                                <span class="badge text-bg-warning">Required</span>
                            @else
                                <span class="badge text-bg-success">Not required</span>
                            @endif
                        </li>
                    </ul>

                    {{-- Technical peek (collapsible) --}}
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#mimePeek">
                            Show technical details ({{ count($allowed_mimetypes) }}
                            type{{ count($allowed_mimetypes) == 1 ? '' : 's' }})
                        </button>
                        <div class="collapse mt-2" id="mimePeek">
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($allowed_mimetypes as $m)
                                    <span class="badge rounded-pill text-bg-light border">{{ $m }}</span>
                                @empty
                                    <span class="text-muted">No MIME types.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        .selectable-card input[type="checkbox"]:checked~* {
            filter: none;
        }

        .selectable-card {
            transition: .15s;
        }

        .selectable-card:hover {
            transform: translateY(-2px);
        }

        .list-group-item {
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, .05);
        }
    </style>
@endPushOnce
