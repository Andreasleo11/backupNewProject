<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">{{ $row?->id ? 'Edit Defect' : 'Create Defect' }}</h1>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.verification.defects.index') }}">Back</a>
            <button class="btn btn-primary" wire:click="save">Save</button>
        </div>
    </div>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Code</label>
                    <input class="form-control @error('code') is-invalid @enderror" wire:model.defer="code">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select class="form-select @error('default_severity') is-invalid @enderror"
                        wire:model.live="default_severity">
                        @foreach (\App\Domain\Verification\Enums\Severity::cases() as $sev)
                            <option value="{{ $sev->value }}">{{ $sev->value }}</option>
                        @endforeach
                    </select>
                    @error('default_severity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Source</label>
                    <select class="form-select @error('default_source') is-invalid @enderror"
                        wire:model.live="default_source">
                        @foreach (\App\Domain\Verification\Enums\DefectSource::cases() as $src)
                            <option value="{{ $src->value }}">{{ $src->value }}</option>
                        @endforeach
                    </select>
                    @error('default_source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Default Quantity</label>
                    <input type="number" step="0.0001"
                        class="form-control @error('default_quantity') is-invalid @enderror"
                        wire:model.defer="default_quantity">
                    @error('default_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-7">
                    <label class="form-label">Notes</label>
                    <input class="form-control @error('notes') is-invalid @enderror" wire:model.defer="notes">
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">Active</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model.defer="active">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
