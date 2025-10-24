<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">📅 Receive Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('form.rec_date') is-invalid @enderror"
            wire:model.defer="form.rec_date">
        @error('form.rec_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">🔍 Verify Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('form.verify_date') is-invalid @enderror"
            wire:model.defer="form.verify_date">
        @error('form.verify_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">👤 Customer <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('form.customer') is-invalid @enderror"
            placeholder="Customer name" wire:model.defer="form.customer">
        @error('form.customer')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">🧾 Invoice Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('form.invoice_number') is-invalid @enderror"
            placeholder="Invoice #" wire:model.defer="form.invoice_number">
        @error('form.invoice_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Department (meta)</label>
        <input type="text" class="form-control" placeholder="e.g. FIN, OPS" wire:model.defer="form.meta.department">
        <div class="form-text">Used by approval resolver.</div>
    </div>
</div>
