<div class="container py-3">
    <h1 class="h5 mb-3">Assign Requirement to Departments</h1>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Requirement</label>
            <select class="form-select" wire:model="requirement_id">
                <option value="">-- choose --</option>
                @foreach ($requirements as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Departments</label>
            <select class="form-select" wire:model="department_ids" multiple size="8">
                @foreach ($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
        </div>
        <div class="col-12 d-flex align-items-center gap-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="mandatory" wire:model="is_mandatory">
                <label class="form-check-label" for="mandatory">Mandatory</label>
            </div>
            <button class="btn btn-primary" wire:click="save">Save</button>
        </div>
    </div>
</div>
