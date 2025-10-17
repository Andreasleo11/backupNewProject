<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h5 mb-0">
                {{ $report?->id ? 'Edit Verification Report' : 'New Verification Report' }}
            </h1>
            @if ($report?->document_number)
                <div class="text-muted small">Doc#: {{ $report->document_number }}</div>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button class="btn btn-primary" wire:click="save">
                <i class="bi bi-save"></i> Save
            </button>
        </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        {{-- Left column: header fields --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Receive Date</label>
                            <input type="date" class="form-control @error('form.rec_date') is-invalid @enderror"
                                wire:model.live.defer="form.rec_date">
                            @error('form.rec_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verify Date</label>
                            <input type="date" class="form-control @error('form.verify_date') is-invalid @enderror"
                                wire:model.live.defer="form.verify_date">
                            @error('form.verify_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control @error('form.customer') is-invalid @enderror"
                            placeholder="Customer name" wire:model.live.defer="form.customer">
                        @error('form.customer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control @error('form.invoice_number') is-invalid @enderror"
                            placeholder="Invoice #" wire:model.live.defer="form.invoice_number">
                        @error('form.invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-3">

                    <div class="mb-0">
                        <label class="form-label">Department (meta)</label>
                        <input type="text" class="form-control" placeholder="e.g. FIN, OPS"
                            wire:model.live.defer="form.meta.department">
                        <div class="form-text">Used by approval resolver.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: items repeater --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">Items</div>
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addItem">
                            <i class="bi bi-plus-lg"></i> Add item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Part Name</th>
                                    <th class="text-end">Rec Qty</th>
                                    <th class="text-end">Verify Qty</th>
                                    <th class="text-end">Can Use</th>
                                    <th class="text-end">Can't Use</th>
                                    <th class="text-end">Price</th>
                                    <th>Cur</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $i => $row)
                                    <tr>
                                        <td style="min-width: 180px;">
                                            <input type="text"
                                                class="form-control @error('items.' . $i . '.part_name') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.part_name"
                                                placeholder="Part name">
                                            @error('items.' . $i . '.part_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control @error('items.' . $i . '.rec_quantity') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.rec_quantity">
                                            @error('items.' . $i . '.rec_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control @error('items.' . $i . '.verify_quantity') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.verify_quantity">
                                            @error('items.' . $i . '.verify_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control @error('items.' . $i . '.can_use') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.can_use">
                                            @error('items.' . $i . '.can_use')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control @error('items.' . $i . '.cant_use') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.cant_use">
                                            @error('items.' . $i . '.cant_use')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end" style="max-width:120px;">
                                            <input type="number" step="0.01"
                                                class="form-control @error('items.' . $i . '.price') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.price">
                                            @error('items.' . $i . '.price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td style="max-width:90px;">
                                            <input type="text"
                                                class="form-control @error('items.' . $i . '.currency') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.currency">
                                            @error('items.' . $i . '.currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" type="button"
                                                wire:click="removeItem({{ $i }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No items yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($items))
                                @php
                                    $total = collect($items)->sum(
                                        fn($r) => (float) ($r['verify_quantity'] ?? 0) * (float) ($r['price'] ?? 0),
                                    );
                                @endphp
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Monetary Total (Σ verify_qty × price)</th>
                                        <th class="text-end">{{ number_format($total, 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
