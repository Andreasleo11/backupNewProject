<div>
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="text-primary border-bottom pb-1 fw-bold mb-3">Output</h6>
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">Quantity Total</label>
                    <input type="number" min="0" class="form-control" wire:model.blur="output_quantity_total">
                    @error('output_quantity_total')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col mb-3">
                    <label class="form-label">Quantity Pass</label>
                    <input type="number" min="0" class="form-control" wire:model.blur="output_quantity_pass">
                    @error('output_quantity_pass')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col mb-3">
                    <label class="form-label">Quantity Reject</label>
                    <input type="number" min="0" class="form-control" wire:model.blur="output_quantity_reject">
                    @error('output_quantity_reject')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sampling Quantity</label>
                <input type="number" min="0" class="form-control" wire:model.blur="sampling_quantity">
                @error('sampling_quantity')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Reject Percentage (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control"
                    wire:model.blur="reject_percentage" readonly>
                @error('reject_percentage')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-primary mt-3" wire:click="saveStep">Save & Continue</button>
</div>
