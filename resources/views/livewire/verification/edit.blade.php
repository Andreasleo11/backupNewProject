<div class="container py-4">

    {{-- Top toolbar (save/back stays available) --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-bold">
                {{ $report?->id ? "Edit Verification Report #{$report->id}" : 'New Verification Report' }}</h3>
            @if (!$report?->id)
                <p class="text-muted">
                    This report will be auto-saved as a draft. You can close the form and continue editing it later.
                </p>
            @else
                @if ($report?->document_number)
                    <div class="small text-muted">Doc#: <span class="fw-semibold">{{ $report->document_number }}</span>
                    </div>
                @endif
            @endif
            <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                <span>
                    @if ($lastAutosaveAt)
                        Autosaved at {{ $lastAutosaveAt }}
                    @else
                        Autosave enabled (every {{ (int) ($autosaveMs / 1000) }}s)
                    @endif
                </span>
            </div>
        </div>

        <button class="btn btn-outline-danger" wire:click="$dispatch('ask-discard-draft')">
            <i class="bi bi-trash"></i> Discard draft
        </button>

        <div wire:ignore.self class="modal fade" id="discardDraftModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Discard draft?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        This will permanently remove the auto-saved draft for this report.
                        <br><span class="text-muted small">You can’t undo this action.</span>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" wire:click="clearDraft" data-bs-dismiss="modal">
                            <i class="bi bi-trash"></i> Discard draft
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @pushOnce('extraJs')
            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('ask-discard-draft', () => {
                        const el = document.getElementById('discardDraftModal');
                        if (!el) return;
                        const modal = bootstrap.Modal.getOrCreateInstance(el);
                        modal.show();
                    });

                    Livewire.on('draft-cleared', () => {
                        window.location.href = "{{ route('verification.index') }}";
                    })
                });
            </script>
        @endPushOnce
    </div>

    <div x-data="{ step: @entangle('step') }" class="mb-4 d-flex align-items-center column-gap-3">
        @php
            $steps = [1 => 'Header', 2 => 'Items', 3 => 'Defects', 4 => 'Preview'];
            $totalSteps = count($steps);
        @endphp

        @foreach ($steps as $s => $label)
            {{-- Step Circle --}}
            <div class="text-center">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; font-weight: bold; transition: all 0.7s ease;"
                    :class="{
                        'bg-primary text-white border-primary': step >= {{ $s }},
                        'bg-transparent text-primary border border-primary': step <
                            {{ $s }}
                    }">
                    {{ $s }}
                </div>
                <small class="d-block mt-1">{{ $label }}</small>
            </div>

            {{-- Progress Bar Between Circles --}}
            @if ($s < $totalSteps)
                <div class="flex-grow-1 mx-2">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                            :class="step > {{ $s }} ? 'bg-primary' : 'bg-light'"
                            :style="'width: ' + (step > {{ $s }} ? '100%' : '0%') +
                            '; transition: width 0.7s ease;'">
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
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

    {{-- STEP 1: HEADER --}}
    @if ($step === 1)
        <div class="card border-0">
            <div class="card-body">
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
                        <input type="text" class="form-control" placeholder="e.g. FIN, OPS"
                            wire:model.defer="form.meta.department">
                        <div class="form-text">Used by approval resolver.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between gap-2 mt-3">
            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left"></i>
                Back to List</a>
            <button class="btn btn-primary" wire:click="nextStep"><i class="bi bi-arrow-right"></i> Next</button>
        </div>
    @endif

    {{-- STEP 2: ITEMS (reuse your improved table) --}}
    @if ($step === 2)
        <div class="card border-0">
            <div class="card-body pb-0">
                {{-- Items toolbar --}}
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                    <div class="fs-5">
                        @if ($report?->customer)
                            Items for
                            <span class="fw-semibold">
                                {{ $report->customer }}
                            </span>
                        @endif
                    </div>
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
                            wire:click="$set('pasteDialog', true)" data-bs-toggle="tooltip"
                            title="Paste from Excel/CSV">
                            <i class="bi bi-clipboard"></i>
                        </button>

                        {{-- bulk helper --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            wire:click="fillAllCantUseFromDefects" data-bs-toggle="tooltip"
                            title="Copy Defect -> Can't Use (all)">
                            <i class="bi bi-arrow-down-square"></i>
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
                                <th>Currency</th>
                                <th class="text-end">Line Total</th>
                                <th class="text-center"></th>
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

                                <tr class="align-middle item-row {{ $errBag->isNotEmpty() ? 'table-warning' : '' }}">
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
                                                data-bs-toggle="tooltip" title="Copy Defect -> Can't Use">
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
                                                wire:click="moveItemUp({{ $i }})" data-bs-toggle="tooltip"
                                                title="Up">↑</button>
                                            <button class="btn btn-outline-secondary"
                                                wire:click="moveItemDown({{ $i }})"
                                                data-bs-toggle="tooltip" title="Down">↓</button>
                                            <button class="btn btn-outline-danger"
                                                wire:click="removeItem({{ $i }})" data-bs-toggle="tooltip"
                                                title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No items yet.</td>
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
        <div class="d-flex justify-content-between">
            <button class="btn btn-outline-secondary" wire:click="prevStep"><i class="bi bi-arrow-left"></i> Back to
                Header</button>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" wire:click="nextStep"><i class="bi bi-arrow-right"></i> Next</button>
            </div>
        </div>

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

        {{-- keep your Paste dialog modal below if you split tables --}}
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
    @endif

    {{-- STEP 3: DEFECTS (per-item) --}}
    @if ($step === 3)
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border-0 h-100">
                    <div class="card-header bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold">Items</div>
                            <button class="btn btn-sm btn-outline-primary" wire:click="goToStep(2)"
                                data-bs-toggle="tooltip" title="Edit items"><i
                                    class="bi bi-pencil-square"></i></button>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($items as $i => $row)
                            @php
                                $defects = $row['defects'] ?? [];
                                $count = count($defects);
                                $hi = collect($defects)->where('severity', 'HIGH')->count();
                                $md = collect($defects)->where('severity', 'MEDIUM')->count();
                                $lo = collect($defects)->where('severity', 'LOW')->count();
                            @endphp
                            <button
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $activeItem === $i ? 'active' : '' }}"
                                wire:click="goToItem({{ $i }})">
                                <span class="text-truncate">{{ $row['part_name'] ?: 'Untitled' }}</span>
                                <span>
                                    <span
                                        class="badge bg-dark-subtle text-dark-emphasis border">{{ $count }}</span>
                                    @if ($hi)
                                        <span class="badge text-bg-danger">{{ $hi }}</span>
                                    @endif
                                    @if ($md)
                                        <span class="badge text-bg-warning text-dark">{{ $md }}</span>
                                    @endif
                                    @if ($lo)
                                        <span class="badge text-bg-success">{{ $lo }}</span>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Defects for: <span
                                class="text-muted">{{ $items[$activeItem]['part_name'] ?? '—' }}</span></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary"
                                wire:click="addDefect({{ $activeItem ?? 0 }})" data-bs-toggle="tooltip"
                                title="Add Defect" aria-label="Add Defect">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary"
                                wire:click="openDefectPicker({{ $activeItem ?? 0 }})" data-bs-toggle="tooltip"
                                title="Catalog" aria-label="Open Catalog">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        @php $defects = $activeItem!==null ? ($items[$activeItem]['defects'] ?? []) : []; @endphp

                        @if (empty($defects))
                            <div class="text-muted">No defects yet.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:12%">Code</th>
                                            <th style="width:26%">Name</th>
                                            <th style="width:16%">Severity</th>
                                            <th style="width:16%">Source</th>
                                            <th class="text-end" style="width:16%">Qty</th>
                                            <th style="width:18%">Notes</th>
                                            <th style="width:6%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($defects as $d => $def)
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                        class="form-control form-control-sm @error('items.' . $activeItem . '.defects.' . $d . '.code') is-invalid @enderror"
                                                        wire:model.defer="items.{{ $activeItem }}.defects.{{ $d }}.code">
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        class="form-control form-control-sm @error('items.' . $activeItem . '.defects.' . $d . '.name') is-invalid @enderror"
                                                        wire:model.defer="items.{{ $activeItem }}.defects.{{ $d }}.name">
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <select
                                                        class="form-select form-select-sm @error('items.' . $activeItem . '.defects.' . $d . '.severity') is-invalid @enderror"
                                                        wire:model="items.{{ $activeItem }}.defects.{{ $d }}.severity">
                                                        @foreach (\App\Domain\Verification\Enums\Severity::cases() as $sev)
                                                            <option value="{{ $sev->value }}">{{ $sev->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.severity')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <select
                                                        class="form-select form-select-sm @error('items.' . $activeItem . '.defects.' . $d . '.source') is-invalid @enderror"
                                                        wire:model="items.{{ $activeItem }}.defects.{{ $d }}.source">
                                                        @foreach (\App\Domain\Verification\Enums\DefectSource::cases() as $src)
                                                            <option value="{{ $src->value }}">{{ $src->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.source')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number" step="0.0001"
                                                        class="form-control form-control-sm text-end @error('items.' . $activeItem . '.defects.' . $d . '.quantity') is-invalid @enderror"
                                                        wire:model.defer="items.{{ $activeItem }}.defects.{{ $d }}.quantity">
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.quantity')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        class="form-control form-control-sm @error('items.' . $activeItem . '.defects.' . $d . '.notes') is-invalid @enderror"
                                                        wire:model.defer="items.{{ $activeItem }}.defects.{{ $d }}.notes">
                                                    @error('items.' . $activeItem . '.defects.' . $d . '.notes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="removeDefect({{ $activeItem }}, {{ $d }})"><i
                                                            class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button class="btn btn-outline-secondary" wire:click="prevStep"><i class="bi bi-arrow-left"></i>
                    Back to Items</button>
                <button class="btn btn-primary" wire:click="save"><i class="bi bi-save"></i> Save</button>
            </div>
        </div>
    @endif

    {{-- Catalog Picker Modal (reuse your existing) --}}
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
                            <div class="col"><input class="form-control" placeholder="Search code or name..."
                                    wire:model.debounce.300ms="defectSearch"></div>
                            <div class="col-auto"><span class="text-muted small">Double-click a row to select</span>
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
                                            <td>@include('partials.source-chip', ['source' => $c['source']])</td>
                                            <td class="text-end">
                                                {{ rtrim(rtrim(number_format($c['quantity'], 4, '.', ''), '0'), '.') }}
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-primary"
                                                    wire:click.stop="pickCatalogDefect({{ $c['id'] }})">Use</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No results.</td>
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
</div>
