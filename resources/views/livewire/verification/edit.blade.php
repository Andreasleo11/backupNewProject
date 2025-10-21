<div class="container py-4">

    {{-- ========== Sticky Header / Toolbar ========== --}}
    <div class="position-sticky top-0 pb-2" style="z-index: 1020; background: var(--bs-body-bg);">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h1 class="h5 mb-0">
                        {{ $report?->id ? 'Edit Verification Report' : 'New Verification Report' }}
                    </h1>
                    <div class="small text-muted">
                        @if ($report?->document_number)
                            Doc#: <span class="fw-semibold">{{ $report->document_number }}</span>
                            <span class="mx-2">•</span>
                        @endif
                        <span>Receive → Verify dates, customer, invoice & items</span>
                    </div>
                </div>
                {{-- Quick summary chips --}}
                @php
                    $currency = collect($items)->first()['currency'] ?? 'IDR';
                    $grand = collect($items)->sum(
                        fn($r) => (float) ($r['verify_quantity'] ?? 0) * (float) ($r['price'] ?? 0),
                    );
                @endphp
                <div class="d-none d-md-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">Items:
                        {{ count($items) }}</span>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">Currency:
                        {{ $currency }}</span>
                    <span class="badge bg-primary-subtle text-primary-emphasis border">
                        Total: {{ number_format($grand, 2) }}
                    </span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip"
                    title="Back to list (Esc)">
                    <i class="bi bi-arrow-left"></i> Back
                </a>

                {{-- Save actions --}}
                <div class="btn-group">
                    <button class="btn btn-primary" wire:click="save" data-bs-toggle="tooltip" title="Ctrl/Cmd + S">
                        <i class="bi bi-save"></i> Save
                    </button>
                    {{-- Optional: Save & Submit (only show on edit + DRAFT) --}}
                    @if ($report?->status === 'DRAFT')
                        <button class="btn btn-outline-primary" wire:click="$dispatch('confirm-submit')"
                            data-bs-toggle="tooltip" title="Save then submit for approval">
                            <i class="bi bi-send"></i> Save & Submit
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Inline keyboard hints --}}
        <div class="small text-muted d-none d-md-block">
            <i class="bi bi-lightning-charge"></i> Tips: <kbd>Ctrl/Cmd + S</kbd> to save • Click column headers for help
            • Double-click a catalog row to pick.
        </div>
        <hr class="mt-2 mb-3">
    </div>

    {{-- ========== Errors ========== --}}
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
        {{-- ========== Header fields ========== --}}
        <div class="">
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
                            placeholder="Customer name" autocomplete="off" wire:model.live.defer="form.customer">
                        @error('form.customer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control @error('form.invoice_number') is-invalid @enderror"
                            placeholder="Invoice #" autocomplete="off" wire:model.live.defer="form.invoice_number">
                        @error('form.invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-3">

                    <div class="mb-0">
                        <label class="form-label">Department (meta)</label>
                        <input type="text" class="form-control" placeholder="e.g. FIN, OPS"
                            wire:model.live.defer="form.meta.department">
                        <div class="form-text">Used by approval resolver for routing rules.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== Right: Items table (improved) ========== --}}
        <div class="">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- Items toolbar --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                        <div class="fw-semibold">Items</div>
                        <div class="d-flex flex-wrap gap-2">
                            {{-- default currency for new rows --}}
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text">Default currency</span>
                                <input type="text" class="form-control" placeholder="IDR"
                                    wire:model.live.defer="defaultCurrency">
                                <button class="btn btn-outline-secondary" type="button"
                                    wire:click="applyDefaultCurrency">
                                    Apply to all
                                </button>
                            </div>

                            {{-- paste from Excel --}}
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="$set('pasteDialog', true)">
                                <i class="bi bi-clipboard"></i> Paste from Excel/CSV
                            </button>

                            {{-- bulk helper --}}
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="fillAllCantUseFromDefects" data-bs-toggle="tooltip"
                                title="Set Can't Use = sum(defects.quantity) for all rows">
                                <i class="bi bi-arrow-down-square"></i> Copy defects → Can't Use (all)
                            </button>

                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addItem">
                                <i class="bi bi-plus-lg"></i> Add item
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-hover editor-table">
                            <thead class="table-light sticky-head">
                                <tr>
                                    <th class="text-muted small">#</th>
                                    <th>Part Name</th>
                                    <th class="text-end">Rec Qty</th>
                                    <th class="text-end">Verify Qty</th>
                                    <th class="text-end">Can Use</th>
                                    <th class="text-end">Can't Use</th>
                                    <th class="text-end">OK %</th>
                                    <th class="text-end">Scrap %</th>
                                    <th class="text-end">Price</th>
                                    <th>Cur</th>
                                    <th class="text-end">Line Total</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($items as $i => $row)
                                    @php
                                        $verify = (float) ($row['verify_quantity'] ?? 0);
                                        $can = (float) ($row['can_use'] ?? 0);
                                        $cant = (float) ($row['cant_use'] ?? 0);
                                        $price = (float) ($row['price'] ?? 0);
                                        $okPct = $verify > 0 ? ($can / $verify) * 100 : 0;
                                        $ngPct = $verify > 0 ? ($cant / $verify) * 100 : 0;
                                        $line = $verify * $price;
                                        $defects = $items[$i]['defects'] ?? [];
                                        $errBag = collect($errors->getBag('default')->get("items.$i.*"))->collapse();
                                    @endphp

                                    <tr
                                        class="align-middle item-row {{ $errBag->isNotEmpty() ? 'table-warning' : '' }}">
                                        <td class="text-muted small">{{ $i + 1 }}</td>

                                        <td style="min-width: 220px;">
                                            <div class="d-flex gap-2 align-items-start">
                                                <input type="text" autocomplete="off"
                                                    class="form-control form-control-sm @error('items.' . $i . '.part_name') is-invalid @enderror"
                                                    placeholder="Part name"
                                                    wire:model.live.defer="items.{{ $i }}.part_name">
                                                {{-- row error chip --}}
                                                @if ($errBag->isNotEmpty())
                                                    <span class="badge text-bg-warning" data-bs-toggle="tooltip"
                                                        title="{{ implode(' • ', $errBag->toArray()) }}">!</span>
                                                @endif
                                            </div>
                                            @error('items.' . $i . '.part_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                            {{-- inline defect chips --}}
                                            @php
                                                $hi = collect($defects)->where('severity', 'HIGH')->count();
                                                $md = collect($defects)->where('severity', 'MEDIUM')->count();
                                                $lo = collect($defects)->where('severity', 'LOW')->count();
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1 mt-1 small">
                                                @if ($hi + $md + $lo > 0)
                                                    <span class="badge bg-dark-subtle text-dark-emphasis border"><i
                                                            class="bi bi-bug"></i> {{ $hi + $md + $lo }}</span>
                                                @endif
                                                @if ($hi > 0)
                                                    <span class="badge text-bg-danger">HIGH {{ $hi }}</span>
                                                @endif
                                                @if ($md > 0)
                                                    <span class="badge text-bg-warning text-dark">MED
                                                        {{ $md }}</span>
                                                @endif
                                                @if ($lo > 0)
                                                    <span class="badge text-bg-success">LOW {{ $lo }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control form-control-sm @error('items.' . $i . '.rec_quantity') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.rec_quantity">
                                            @error('items.' . $i . '.rec_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control form-control-sm @error('items.' . $i . '.verify_quantity') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.verify_quantity">
                                            @error('items.' . $i . '.verify_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td class="text-end" style="max-width:110px;">
                                            <input type="number" step="0.0001"
                                                class="form-control form-control-sm @error('items.' . $i . '.can_use') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.can_use">
                                            @error('items.' . $i . '.can_use')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td class="text-end" style="max-width:140px;">
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.0001"
                                                    class="form-control @error('items.' . $i . '.cant_use') is-invalid @enderror"
                                                    wire:model.live.defer="items.{{ $i }}.cant_use">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="fillCantUseFromDefects({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Set Can't Use = sum(defects)">
                                                    <i class="bi bi-arrow-down-square"></i>
                                                </button>
                                            </div>
                                            @error('items.' . $i . '.cant_use')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td
                                            class="text-end small {{ $okPct >= 98 ? 'text-success' : ($okPct < 90 ? 'text-danger' : '') }}">
                                            {{ number_format($okPct, 1) }}%
                                        </td>

                                        <td class="text-end small {{ $ngPct >= 10 ? 'text-danger' : '' }}">
                                            {{ number_format($ngPct, 1) }}%
                                        </td>

                                        <td class="text-end" style="max-width:120px;">
                                            <input type="number" step="0.01"
                                                class="form-control form-control-sm @error('items.' . $i . '.price') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.price">
                                            @error('items.' . $i . '.price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td style="max-width:90px;">
                                            <input type="text"
                                                class="form-control form-control-sm @error('items.' . $i . '.currency') is-invalid @enderror"
                                                wire:model.live.defer="items.{{ $i }}.currency">
                                            @error('items.' . $i . '.currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td class="text-end fw-semibold">{{ number_format($line, 2) }}</td>

                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-secondary"
                                                    wire:click="insertItemBelow({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Insert below">＋</button>
                                                <button class="btn btn-outline-secondary"
                                                    wire:click="duplicateItem({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Duplicate">⎘</button>
                                                <button class="btn btn-outline-secondary"
                                                    wire:click="moveItemUp({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Up">↑</button>
                                                <button class="btn btn-outline-secondary"
                                                    wire:click="moveItemDown({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Down">↓</button>
                                                <button class="btn btn-outline-danger"
                                                    wire:click="removeItem({{ $i }})"
                                                    data-bs-toggle="tooltip" title="Remove">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                        </td>
                                    </tr>

                                    {{-- Defects table (unchanged from your latest, keep below or inline as you like) --}}
                                    <tr class="fade-in">
                                        <td colspan="12" class="bg-body-tertiary border-start border-end">
                                            {{-- defects actions under row --}}
                                            <div class="mt-2 d-flex flex-wrap gap-2 justify-content-end">
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                    wire:click="openDefectPicker({{ $i }})">
                                                    <i class="bi bi-search"></i> Catalog
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                                    wire:click="addDefect({{ $i }})">
                                                    <i class="bi bi-plus-lg"></i> Add defect
                                                </button>
                                            </div>
                                            {{-- keep your existing defects table block here --}}
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="table-secondary sticky-subhead">
                                                        <tr>
                                                            <th style="width:12%">Code</th>
                                                            <th style="width:26%">Name</th>
                                                            <th style="width:14%">Severity</th>
                                                            <th style="width:14%">Source</th>
                                                            <th class="text-end" style="width:14%">Qty</th>
                                                            <th style="width:18%">Notes</th>
                                                            <th style="width:8%"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($defects as $d => $def)
                                                            <tr>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm @error('items.' . $i . '.defects.' . $d . '.code') is-invalid @enderror"
                                                                        wire:model.live.defer="items.{{ $i }}.defects.{{ $d }}.code">
                                                                    @error('items.' . $i . '.defects.' . $d . '.code')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm @error('items.' . $i . '.defects.' . $d . '.name') is-invalid @enderror"
                                                                        wire:model.live.defer="items.{{ $i }}.defects.{{ $d }}.name">
                                                                    @error('items.' . $i . '.defects.' . $d . '.name')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        class="form-select form-select-sm @error('items.' . $i . '.defects.' . $d . '.severity') is-invalid @enderror"
                                                                        wire:model.live="items.{{ $i }}.defects.{{ $d }}.severity">
                                                                        @foreach (\App\Domain\Verification\Enums\Severity::cases() as $sev)
                                                                            <option value="{{ $sev->value }}">
                                                                                {{ $sev->value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('items.' . $i . '.defects.' . $d .
                                                                        '.severity')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        class="form-select form-select-sm @error('items.' . $i . '.defects.' . $d . '.source') is-invalid @enderror"
                                                                        wire:model.live="items.{{ $i }}.defects.{{ $d }}.source">
                                                                        @foreach (\App\Domain\Verification\Enums\DefectSource::cases() as $src)
                                                                            <option value="{{ $src->value }}">
                                                                                {{ $src->value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('items.' . $i . '.defects.' . $d . '.source')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.0001"
                                                                        class="form-control form-control-sm text-end @error('items.' . $i . '.defects.' . $d . '.quantity') is-invalid @enderror"
                                                                        wire:model.live.defer="items.{{ $i }}.defects.{{ $d }}.quantity">
                                                                    @error('items.' . $i . '.defects.' . $d .
                                                                        '.quantity')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm @error('items.' . $i . '.defects.' . $d . '.notes') is-invalid @enderror"
                                                                        wire:model.live.defer="items.{{ $i }}.defects.{{ $d }}.notes">
                                                                    @error('items.' . $i . '.defects.' . $d . '.notes')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td class="text-end">
                                                                    <button class="btn btn-sm btn-outline-danger"
                                                                        wire:click="removeDefect({{ $i }}, {{ $d }})"
                                                                        type="button">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">No items yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @php
                                $grand = collect($items)->sum(
                                    fn($r) => (float) ($r['verify_quantity'] ?? 0) * (float) ($r['price'] ?? 0),
                                );
                            @endphp
                            @if (count($items))
                                <tfoot class="sticky-foot">
                                    <tr>
                                        <th colspan="10" class="text-end">Monetary Total (Σ verify_qty × price)</th>
                                        <th class="text-end">{{ number_format($grand, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paste dialog (Excel/CSV) --}}
        @if ($pasteDialog ?? false)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.35);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Paste items from Excel/CSV</h5>
                            <button type="button" class="btn-close"
                                wire:click="$set('pasteDialog', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="small text-muted mb-2">
                                Columns order: <code>part_name, rec_quantity, verify_quantity, can_use, cant_use, price,
                                    currency</code>
                            </div>
                            <textarea class="form-control" rows="8" placeholder="Paste tab- or comma-separated rows here"
                                wire:model.defer="pasteBuffer"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary"
                                wire:click="$set('pasteDialog', false)">Cancel</button>
                            <button class="btn btn-primary" wire:click="applyPastedItems">Insert</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- tiny styles --}}
        <style>
            .item-row input[type="number"]::-webkit-outer-spin-button,
            .item-row input[type="number"]::-webkit-inner-spin-button {
                opacity: .4;
            }

            @media (max-width: 768px) {
                .editor-table thead {
                    display: none;
                }

                .editor-table tbody tr {
                    display: block;
                    margin-bottom: .75rem;
                    border: 1px solid var(--bs-border-color);
                    border-radius: .5rem;
                }

                .editor-table tbody tr td {
                    display: flex;
                    justify-content: space-between;
                    padding: .5rem .75rem;
                }

                .editor-table tbody tr td:first-child {
                    display: none;
                }

                .editor-table tbody tr td:last-child {
                    justify-content: flex-end;
                }
            }
        </style>


        {{-- ========== Catalog Picker Modal (Livewire overlay) ========== --}}
        @if (!is_null($pickerForItem))
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.35);">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pick a defect from catalog</h5>
                            <button type="button" class="btn-close" wire:click="closeDefectPicker"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col">
                                    <input class="form-control" placeholder="Search code or name..."
                                        wire:model.live.debounce.300ms="defectSearch">
                                </div>
                                <div class="col-auto">
                                    <span class="text-muted small">Double-click a row to select</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle pick-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Severity</th>
                                            <th>Source</th>
                                            <th class="text-end">Default Qty</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($catalogResults as $c)
                                            <tr wire:click="pickCatalogDefect({{ $c['id'] }})"
                                                ondblclick="this.click()" style="cursor: pointer;">
                                                <td class="fw-semibold">{{ $c['code'] }}</td>
                                                <td>{{ $c['name'] }}</td>
                                                <td>@include('partials.severity-badge', [
                                                    'severity' => $c['severity'],
                                                ])</td>
                                                <td>@include('partials.source-chip', [
                                                    'source' => $c['source'],
                                                ])</td>
                                                <td class="text-end">
                                                    {{ rtrim(rtrim(number_format($c['quantity'], 4, '.', ''), '0'), '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click.stop="pickCatalogDefect({{ $c['id'] }})">
                                                        Use
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No results.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" wire:click="closeDefectPicker">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{-- ========== Minimal styles for better UX (no new deps) ========== --}}
        <style>
            .editor-table th,
            .editor-table td {
                vertical-align: middle;
            }

            .sticky-head {
                position: sticky;
                top: 0;
                z-index: 1;
            }

            .sticky-subhead {
                position: sticky;
                top: 0;
                z-index: 1;
            }

            .sticky-foot {
                position: sticky;
                bottom: 0;
                background: var(--bs-body-bg);
            }

            .fade-in {
                animation: fade .15s ease-in;
            }

            @keyframes fade {
                from {
                    opacity: .6
                }

                to {
                    opacity: 1
                }
            }

            .pick-table tr:hover {
                background: var(--bs-secondary-bg);
            }
        </style>

        {{-- ========== Tiny helpers: save shortcut, tooltips, submit confirm ========== --}}
        <script>
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    window.Livewire?.getByName?.('verification.edit')?.call('save');
                }
                if (e.key === 'Escape') {
                    const closeBtn = document.querySelector('[wire\\:click="closeDefectPicker"]');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                }
            });
            document.addEventListener('livewire:init', () => {
                const tts = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tts.forEach(el => new bootstrap.Tooltip(el));
                Livewire.on('confirm-submit', () => {
                    if (confirm('Save and submit for approval?')) {
                        // you can implement a Livewire method that chains save() then submit()
                        // or dispatch an event listened by the component to call submit use case
                        window.Livewire?.getByName?.('verification.edit')?.call(
                            'save'); // then redirect->show to submit from there if you like
                    }
                });
            });
        </script>
    </div>
