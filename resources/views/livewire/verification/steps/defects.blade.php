<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Items</div>
                    <button class="btn btn-sm btn-outline-primary" wire:click="goToStep(2)" data-bs-toggle="tooltip"
                        title="Edit items"><i class="bi bi-pencil-square"></i></button>
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
                            <span class="badge bg-dark-subtle text-dark-emphasis border">{{ $count }}</span>
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
        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Defects for: <span
                        class="text-muted">{{ $items[$activeItem]['part_name'] ?? '—' }}</span></div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="addDefect({{ $activeItem ?? 0 }})"
                        data-bs-toggle="tooltip" title="Add Defect" aria-label="Add Defect">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" wire:click="openDefectPicker({{ $activeItem ?? 0 }})"
                        data-bs-toggle="tooltip" title="Catalog" aria-label="Open Catalog">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
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
