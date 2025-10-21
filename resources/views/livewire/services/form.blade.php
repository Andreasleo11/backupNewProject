<div class="container py-3">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
            <li class="breadcrumb-item active">{{ $record ? 'Edit Service' : 'New Service' }}</li>
        </ol>
    </nav>

    {{-- Page header --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                    style="width:44px;height:44px">
                    <i class="bi bi-wrench-adjustable fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0">{{ $record ? 'Edit Service Record' : 'New Service Record' }}</h5>
                    <div class="text-muted small">
                        @if ($vehicle ?? false)
                            Vehicle: <span class="fw-semibold">{{ $vehicle->display_name }}</span>
                            <span class="text-muted">•</span> Current Odometer: {{ number_format($vehicle->odometer) }}
                            km
                        @else
                            Fill the form below
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2">
                <a href="{{ $vehicle ? route('vehicles.show', $vehicle) : route('vehicles.index') }}"
                    class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="save"></span>
                    <i class="bi bi-save me-1" wire:loading.remove wire:target="save"></i> Save
                </button>
            </div>
        </div>
    </div>

    {{-- Service meta --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Service Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" class="form-control @error('service_date') is-invalid @enderror"
                            wire:model="service_date">
                        @error('service_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Odometer</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                        <input type="number" class="form-control" wire:model.live="odometer" min="0"
                            step="1" placeholder="0">
                        <span class="input-group-text">km</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Workshop</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shop"></i></span>
                        <input type="text" class="form-control" wire:model.live="workshop"
                            placeholder="Internal / Vendor name">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Global VAT (%)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-percent"></i></span>
                        <input type="number" min="0" max="100" step="0.01"
                            class="form-control @error('global_tax_rate') is-invalid @enderror"
                            wire:model.live="global_tax_rate" placeholder="e.g. 11">
                        @error('global_tax_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Default VAT for all items (can be overridden per item).</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" rows="2" wire:model.live="notes" placeholder="Optional notes"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card border-0 shadow-sm position-relative">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">Service Items / Checks</span>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" wire:click="addItem">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="position-sticky top-0 bg-body text-muted small">
                        <tr>
                            <th style="width:22%">Part / Check</th>
                            <th style="width:14%">Action</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:10%">UoM</th>
                            <th style="width:14%">Unit Cost</th>
                            <th style="width:12%">Discount</th>
                            <th style="width:12%">Tax %</th>
                            <th style="width:14%">Line Total</th>
                            <th>Remarks</th>
                            <th style="width:1%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $row)
                            @php
                                $qty = (float) ($items[$i]['qty'] ?? 0);
                                $uc = (float) ($items[$i]['unit_cost'] ?? 0);
                                $disc = max(0, min(100, (float) ($items[$i]['discount'] ?? 0)));
                                $rowTr = $items[$i]['tax_rate'] ?? null;
                                $rowTr = $rowTr === '' || $rowTr === null ? null : max(0, min(100, (float) $rowTr));
                                $rate = $rowTr ?? ($global_tax_rate ?? 0);

                                $base = $qty * $uc * (1 - $disc / 100);
                                $tax = $base * ($rate / 100);
                                $lt = $base + $tax;
                            @endphp
                            <tr wire:key="svc-row-{{ $row['id'] ?? 'n' }}-{{ $i }}">
                                <td>
                                    <input type="text"
                                        class="form-control form-control-sm @error('items.' . $i . '.part_name') is-invalid @enderror"
                                        placeholder="e.g. Engine Oil"
                                        wire:model.live="items.{{ $i }}.part_name">
                                    @error('items.' . $i . '.part_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <select class="form-select form-select-sm"
                                        wire:model.live="items.{{ $i }}.action">
                                        <option value="checked">checked</option>
                                        <option value="replaced">replaced</option>
                                        <option value="repaired">repaired</option>
                                        <option value="topped_up">topped_up</option>
                                        <option value="cleaned">cleaned</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model.live="items.{{ $i }}.qty" step="0.01"
                                        min="0" placeholder="0">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model.live="items.{{ $i }}.uom" placeholder="L, pcs">
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control"
                                            wire:model.live="items.{{ $i }}.unit_cost" step="0.01"
                                            min="0" placeholder="0">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        wire:model.live="items.{{ $i }}.discount" step="0.01"
                                        min="0" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        wire:model.live="items.{{ $i }}.tax_rate" step="0.01"
                                        min="0" max="100"
                                        placeholder="{{ (string) ($global_tax_rate ?? 0) }}">
                                </td>
                                <td class="text-nowrap">
                                    Rp {{ number_format($lt, 0, ',', '.') }}
                                    <div class="small text-muted">
                                        <span>Base: Rp {{ number_format($base, 0, ',', '.') }}</span>
                                        <span class="ms-2">VAT: Rp {{ number_format($tax, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model.live="items.{{ $i }}.remarks" placeholder="Optional">
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" title="Remove"
                                        wire:click="removeItem({{ $i }})">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <div class="mb-2"><i class="bi bi-clipboard2-x fs-2"></i></div>
                                    <div class="fw-semibold">No items yet</div>
                                    <div class="small mb-2">Click “Add Item” to start listing parts or checks.</div>
                                    <button class="btn btn-sm btn-primary" wire:click="addItem">
                                        <i class="bi bi-plus-lg me-1"></i> Add Item
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer totals --}}
        @php
            $totBase = 0.0;
            $totTax = 0.0;

            foreach ($items as $r) {
                $qty = (float) ($r['qty'] ?? 0);
                $uc = (float) ($r['unit_cost'] ?? 0);
                $disc = max(0, min(100, (float) ($r['discount'] ?? 0)));

                $rowTr = $r['tax_rate'] ?? null;
                $rowTr = $rowTr === '' || $rowTr === null ? null : max(0, min(100, (float) $rowTr));
                $rate = $rowTr ?? ($global_tax_rate ?? 0);

                $base = $qty * $uc * (1 - $disc / 100);
                $tax = $base * ($rate / 100);

                $totBase += $base;
                $totTax += $tax;
            }

            $grand = $totBase + $totTax;
        @endphp
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="small text-muted">Items: <span class="fw-semibold">{{ collect($items)->count() }}</span>
                </div>
                <div class="small text-muted">Workshop: <span class="fw-semibold">{{ $workshop ?: '—' }}</span></div>
                <div class="small text-muted">Date: <span
                        class="fw-semibold">{{ $service_date ?: now()->toDateString() }}</span></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="small text-muted">
                    Subtotal: <strong>Rp {{ number_format($totBase, 0, ',', '.') }}</strong>
                    <span class="mx-2">•</span>
                    VAT: <strong>Rp {{ number_format($totTax, 0, ',', '.') }}</strong>
                </div>
                <div class="fs-6">Grand Total:
                    <span class="fw-bold">Rp {{ number_format($grand, 0, ',', '.') }}</span>
                </div>
                <a href="{{ $vehicle ? route('vehicles.show', $vehicle) : route('vehicles.index') }}"
                    class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="save"></span>
                    <i class="bi bi-save me-1" wire:loading.remove wire:target="save"></i> Save
                </button>
            </div>
        </div>
    </div>

    <style>
        .table thead th {
            z-index: 2;
        }

        /* keep sticky header above content */
        .table td,
        .table th {
            vertical-align: middle;
        }

        .badge.text-bg-light {
            color: var(--bs-body-color);
        }
    </style>
</div>
