<div class="row g-3">
    <div class="col-md-6">
        <div class="input-group">
            <div class="form-floating">
                <input id="fld-form-rec_date" type="date"
                    class="form-control @error('form.rec_date') is-invalid @enderror" max="{{ now()->toDateString() }}"
                    wire:model.live.debounce.300ms="form.rec_date">
                <label for="fld-form-rec_date">Receive Date <span class="text-danger">*</span></label>

            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Use today"
                wire:click="$set('form.rec_date','{{ now()->toDateString() }}')">
                Today
            </button>
        </div>
        @error('form.rec_date')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">The date items arrived at our facility.</div>
    </div>

    <div class="col-md-6">
        <div class="input-group">
            <div class="form-floating">
                <input id="fld-form-verify_date" type="date"
                    class="form-control @error('form.verify_date') is-invalid @enderror"
                    min="{{ $form['rec_date'] ?? '' }}" wire:model.live.debounce.300ms="form.verify_date">
                <label for="fld-form-verify_date">Verify Date <span class="text-danger">*</span></label>

            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip"
                title="Copy from Receive" wire:click="$set('form.verify_date', @js($form['rec_date'] ?? ''))">
                Copy
            </button>
        </div>
        @error('form.verify_date')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">Must be on or after Receive Date.</div>
    </div>

    <div class="col-md-6">
        <div class="form-floating">
            <input id="fld-form-customer" type="text"
                class="form-control @error('form.customer') is-invalid @enderror" placeholder="Customer name"
                wire:model.live.debounce.300ms="form.customer" autocomplete="organization">
            <label for="fld-form-customer">Customer <span class="text-danger">*</span></label>
            @error('form.customer')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-floating">
            <input id="fld-form-invoice" type="text"
                class="form-control @error('form.invoice_number') is-invalid @enderror" placeholder="Invoice #"
                wire:model.live.debounce.300ms="form.invoice_number" autocomplete="off">
            <label for="fld-form-invoice">Invoice Number <span class="text-danger">*</span></label>
            @error('form.invoice_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-floating">
            <input id="fld-form-dept" type="text" class="form-control" placeholder="FIN / OPS"
                wire:model.live.debounce.300ms="form.meta.department">
            <label for="fld-form-dept">Department (meta)</label>
        </div>
        <div class="form-text">Used by approval resolver (optional).</div>
    </div>
</div>
