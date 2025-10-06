{{-- resources/views/livewire/requirements/form.blade.php --}}
<div class="container py-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('requirements.index') }}">Requirements</a></li>
            <li class="breadcrumb-item active">{{ $requirement?->exists ? 'Edit' : 'Create' }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" placeholder="ORG_STRUCTURE" wire:model.blur="code">
                    <div class="form-text">Use UPPERCASE, digits, underscore or dash.</div>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" placeholder="Organization Structure"
                        wire:model.blur="name">
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea rows="3" class="form-control" wire:model.blur="description"></textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Allowed MIME types</label>
                    <textarea rows="4" class="form-control" placeholder="application/pdf&#10;image/png&#10;image/jpeg"
                        wire:model.blur="allowed_mimetypes_input"></textarea>
                    <div class="form-text">One per line or comma-separated.</div>
                    @error('allowed_mimetypes_input')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Minimum files <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="20" class="form-control"
                                wire:model.blur="min_count">
                            @error('min_count')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Validity (days)</label>
                            <input type="number" min="1" max="3650" class="form-control"
                                wire:model.blur="validity_days">
                            <div class="form-text">Leave empty if no expiry.</div>
                            @error('validity_days')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Frequency</label>
                            <select class="form-select" wire:model="frequency">
                                <option value="once">Once</option>
                                <option value="yearly">Yearly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            @error('frequency')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiresApproval"
                                    wire:model="requires_approval">
                                <label class="form-check-label" for="requiresApproval">Requires approval</label>
                            </div>
                        </div>
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
