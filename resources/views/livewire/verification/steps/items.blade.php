<div>
    {{-- Toolbar --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
        <div class="fs-5">
            @if ($customer)
                Items for <span class="fw-semibold">{{ $customer }}</span>
            @else
                <span class="text-muted">Items</span>
            @endif
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            {{-- Default Currency --}}
            <div class="input-group input-group-sm" style="width: 260px;" x-data="{ cur: @entangle('defaultCurrency') }">
                <span class="input-group-text">Default currency</span>
                <input list="dl-currencies" type="text" class="form-control" placeholder="IDR"
                    wire:model.live.defer="defaultCurrency">
                <button type="button" class="btn btn-outline-secondary"
                    @click.prevent="confirm(`Apply '${cur || ''}' to all rows?`) && $wire.applyDefaultCurrency()">
                    Apply to all
                </button>
            </div>
            <datalist id="dl-currencies">
                <option value="IDR" />
                <option value="USD" />
                <option value="EUR" />
                <option value="JPY" />
                <option value="CNY" />
            </datalist>

            {{-- Paste from Excel --}}
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('pasteDialog', true)"
                data-bs-toggle="tooltip" title="Paste from Excel/CSV">
                <i class="bi bi-clipboard"></i>
            </button>

            {{-- Bulk helper --}}
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="fillAllCantUseFromDefects"
                data-bs-toggle="tooltip" title="Copy Defect → Can't Use (all)">
                <i class="bi bi-arrow-down-square"></i>
            </button>

            <button type="button" class="btn btn-sm btn-primary" wire:click="addItem">
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

                    <tr wire:key="row-{{ $i }}"
                        class="align-middle item-row {{ $errBag->isNotEmpty() ? 'table-warning' : '' }}">
                        <td class="text-muted small">{{ $i + 1 }}</td>

                        <td style="min-width: 240px;">
                            <div class="d-flex gap-2 align-items-start">
                                <input type="text" autocomplete="off"
                                    class="form-control form-control-sm @error('items.' . $i . '.part_name') is-invalid @enderror"
                                    placeholder="Part name" wire:model.live.defer="items.{{ $i }}.part_name"
                                    id="fld-items-{{ $i }}-part-name">
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
                                    <span class="badge bg-dark-subtle text-dark-emphasis border">
                                        <i class="bi bi-bug"></i> {{ $hi + $md + $lo }}
                                    </span>
                                @endif
                                @if ($hi > 0)
                                    <span class="badge bg-danger-subtle text-danger">HIGH {{ $hi }}</span>
                                @endif
                                @if ($md > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis">MED
                                        {{ $md }}</span>
                                @endif
                                @if ($lo > 0)
                                    <span class="badge bg-success-subtle text-success">LOW {{ $lo }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Numbers --}}
                        <td class="text-end" style="min-width:110px;">
                            <input type="number" step="0.0001" id="fld-items-{{ $i }}-rec-quantity"
                                class="form-control form-control-sm text-end @error('items.' . $i . '.rec_quantity') is-invalid @enderror"
                                wire:model.live.defer="items.{{ $i }}.rec_quantity" inputmode="decimal">
                            @error('items.' . $i . '.rec_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end" style="min-width:110px;">
                            <input type="number" step="0.0001" id="fld-items-{{ $i }}-verify-quantity"
                                class="form-control form-control-sm text-end @error('items.' . $i . '.verify_quantity') is-invalid @enderror"
                                wire:model.live.defer="items.{{ $i }}.verify_quantity" inputmode="decimal">
                            @error('items.' . $i . '.verify_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end" style="min-width:110px;">
                            <input type="number" step="0.0001" id="fld-items-{{ $i }}-can-use"
                                class="form-control form-control-sm text-end @error('items.' . $i . '.can_use') is-invalid @enderror"
                                wire:model.live.defer="items.{{ $i }}.can_use" inputmode="decimal">
                            @error('items.' . $i . '.can_use')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end" style="min-width:110px;">
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.0001" id="fld-items-{{ $i }}-cant-use"
                                    class="form-control text-end @error('items.' . $i . '.cant_use') is-invalid @enderror"
                                    wire:model.live.defer="items.{{ $i }}.cant_use" inputmode="decimal">
                                <button class="btn btn-outline-secondary" type="button"
                                    wire:click="fillCantUseFromDefects({{ $i }})" data-bs-toggle="tooltip"
                                    title="Copy Defect → Can't Use">
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

                        <td class="text-end" style="min-width:120px;">
                            <input type="number" step="0.01" id="fld-items-{{ $i }}-price"
                                class="form-control form-control-sm text-end @error('items.' . $i . '.price') is-invalid @enderror"
                                wire:model.live.defer="items.{{ $i }}.price" inputmode="decimal">
                            @error('items.' . $i . '.price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>

                        <td style="min-width:90px;">
                            <input type="text" id="fld-items-{{ $i }}-currency"
                                class="form-control form-control-sm @error('items.' . $i . '.currency') is-invalid @enderror"
                                wire:model.live.defer="items.{{ $i }}.currency"
                                placeholder="{{ $defaultCurrency }}">
                            @error('items.' . $i . '.currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end fw-semibold"> {{ number_format($line, 2) }} </td>

                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary"
                                    wire:click="insertItemBelow({{ $i }})" data-bs-toggle="tooltip"
                                    title="Insert below">＋</button>
                                <button class="btn btn-outline-secondary"
                                    wire:click="duplicateItem({{ $i }})" data-bs-toggle="tooltip"
                                    title="Duplicate">⎘</button>
                                <button class="btn btn-outline-secondary"
                                    wire:click="moveItemUp({{ $i }})" data-bs-toggle="tooltip"
                                    title="Up">↑</button>
                                <button class="btn btn-outline-secondary"
                                    wire:click="moveItemDown({{ $i }})" data-bs-toggle="tooltip"
                                    title="Down">↓</button>
                                <button class="btn btn-outline-danger" wire:click="removeItem({{ $i }})"
                                    data-bs-toggle="tooltip" title="Remove"><i class="bi bi-trash"></i></button>
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
                // group totals by currency (handles mixed currencies gracefully)
                $byCurr = collect($items)->groupBy(
                    fn($r) => trim($r['currency'] ?? $defaultCurrency) ?: $defaultCurrency,
                );
                $grandRows = $byCurr
                    ->map(
                        fn($rows, $cur) => [
                            'currency' => $cur,
                            'sum' => $rows->sum(
                                fn($r) => (float) ($r['verify_quantity'] ?? 0) * (float) ($r['price'] ?? 0),
                            ),
                        ],
                    )
                    ->values();
            @endphp

            @if (count($items))
                <tfoot class="sticky-foot">
                    @foreach ($grandRows as $gr)
                        <tr>
                            <th colspan="10" class="text-end">Monetary Total ({{ $gr['currency'] }})</th>
                            <th class="text-end">{{ number_format($gr['sum'], 2) }}</th>
                            <th></th>
                        </tr>
                    @endforeach
                </tfoot>
            @endif
        </table>
    </div>

    @pushOnce('extraCss')
        <style>
            .editor-table th,
            .editor-table td {
                vertical-align: middle;
            }

            .sticky-head {
                position: sticky;
                top: 0;
                z-index: 2;
            }

            .sticky-foot {
                position: sticky;
                bottom: 0;
                background: var(--bs-body-bg);
                z-index: 1;
            }

            .pick-table tr:hover {
                background: var(--bs-secondary-bg);
            }
        </style>
    @endPushOnce

    {{-- Paste dialog (kept) --}}
    @if ($pasteDialog ?? false)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.35);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Paste items from Excel/CSV</h5>
                        <button type="button" class="btn-close" wire:click="$set('pasteDialog', false)"></button>
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
</div>
