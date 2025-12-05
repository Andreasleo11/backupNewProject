<div class="max-w-6xl mx-auto px-4 py-4 space-y-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="text-sm">
        <ol class="flex flex-wrap items-center gap-1 text-slate-500">
            <li>
                <a href="{{ route('vehicles.index') }}" class="hover:text-slate-700 hover:underline">
                    Vehicles
                </a>
            </li>
            <li class="text-slate-400">/</li>
            <li class="font-medium text-slate-700">
                {{ $record ? 'Edit Service' : 'New Service' }}
            </li>
        </ol>
    </nav>

    {{-- Page header --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="px-4 py-3 flex flex-wrap justify-between items-start gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 shadow-inner">
                    <i class="bi bi-wrench-adjustable text-lg"></i>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-slate-900">
                        {{ $record ? 'Edit Service Record' : 'New Service Record' }}
                    </h5>
                    <div class="mt-0.5 text-xs text-slate-500">
                        @if ($vehicle ?? false)
                            Vehicle:
                            <span class="font-semibold text-slate-800">{{ $vehicle->display_name }}</span>
                            <span class="mx-1 text-slate-400">•</span>
                            Current Odometer:
                            <span class="font-medium">{{ number_format($vehicle->odometer) }} km</span>
                        @else
                            Fill the form below
                        @endif
                    </div>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-2">
                <a href="{{ $vehicle ? route('vehicles.show', $vehicle) : route('vehicles.index') }}"
                   class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    Cancel
                </a>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                    <span class="mr-1 inline-flex" wire:loading wire:target="save">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="save">
                        <i class="bi bi-save mr-1 text-[0.85rem]"></i>
                    </span>
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Service meta --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="px-4 py-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                {{-- Service Date --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Service Date
                    </label>
                    <div class="flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-slate-500">
                            <i class="bi bi-calendar3 text-[0.9rem]"></i>
                        </span>
                        <input
                            type="date"
                            wire:model="service_date"
                            class="block w-full py-2 px-3 rounded-r-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                   @error('service_date') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    </div>
                    @error('service_date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Odometer --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Odometer
                    </label>
                    <div class="flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-slate-500">
                            <i class="bi bi-speedometer2 text-[0.9rem]"></i>
                        </span>
                        <input
                            type="number"
                            min="0"
                            step="1"
                            placeholder="0"
                            wire:model.live="odometer"
                            class="block w-full py-2 px-3 rounded-r-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span
                            class="ml-2 inline-flex items-center rounded-md border border-slate-300 bg-slate-50 px-2 text-xs text-slate-600">
                            km
                        </span>
                    </div>
                </div>

                {{-- Workshop --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Workshop
                    </label>
                    <div class="flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-slate-500">
                            <i class="bi bi-shop text-[0.9rem]"></i>
                        </span>
                        <input
                            type="text"
                            placeholder="Internal / Vendor name"
                            wire:model.live="workshop"
                            class="block w-full py-2 px-3 rounded-r-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Global VAT --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Global VAT (%)
                    </label>
                    <div class="flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-slate-500">
                            <i class="bi bi-percent text-[0.9rem]"></i>
                        </span>
                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            placeholder="e.g. 11"
                            wire:model.live="global_tax_rate"
                            class="block w-full py-2 px-3 rounded-r-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                   @error('global_tax_rate') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    </div>
                    @error('global_tax_rate')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] text-slate-500">
                        Default VAT for all items (can be overridden per item).
                    </p>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mt-4">
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Notes
                </label>
                <textarea
                    rows="2"
                    wire:model.live="notes"
                    placeholder="Optional notes"
                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm relative">
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <span class="text-sm font-semibold text-slate-800">
                Service Items / Checks
            </span>
            <button
                type="button"
                wire:click="addItem"
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                Add Item
            </button>
        </div>

        {{-- Table --}}
        <div class="px-3 py-3 overflow-x-auto">
            <table class="min-w-full text-xs text-slate-700">
                <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-2 py-2 text-left w-[22%]">
                            Part / Check <span class="text-rose-500">*</span>
                        </th>
                        <th class="px-2 py-2 text-left w-[14%]">Action</th>
                        <th class="px-2 py-2 text-left w-[10%]">Qty</th>
                        <th class="px-2 py-2 text-left w-[10%]">UoM</th>
                        <th class="px-2 py-2 text-left w-[14%]">Unit Cost</th>
                        <th class="px-2 py-2 text-left w-[12%]">Discount</th>
                        <th class="px-2 py-2 text-left w-[12%]">Tax %</th>
                        <th class="px-2 py-2 text-left w-[14%]">Line Total</th>
                        <th class="px-2 py-2 text-left">Remarks</th>
                        <th class="px-2 py-2 text-right w-[1%]"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $i => $row)
                        @php
                            $qty   = (float) ($items[$i]['qty'] ?? 0);
                            $uc    = (float) ($items[$i]['unit_cost'] ?? 0);
                            $disc  = max(0, min(100, (float) ($items[$i]['discount'] ?? 0)));
                            $rowTr = $items[$i]['tax_rate'] ?? null;
                            $rowTr = $rowTr === '' || $rowTr === null ? null : max(0, min(100, (float) $rowTr));
                            $rate  = $rowTr ?? ($global_tax_rate ?? 0);

                            $base = $qty * $uc * (1 - $disc / 100);
                            $tax  = $base * ($rate / 100);
                            $lt   = $base + $tax;
                        @endphp
                        <tr wire:key="svc-row-{{ $row['id'] ?? 'n' }}-{{ $i }}">
                            {{-- Part / Check --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="text"
                                    placeholder="e.g. Engine Oil"
                                    wire:model.live="items.{{ $i }}.part_name"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.part_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.part_name')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Action --}}
                            <td class="px-2 py-2 align-top">
                                <select
                                    wire:model.live="items.{{ $i }}.action"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.action') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                    <option value="checked">checked</option>
                                    <option value="replaced">replaced</option>
                                    <option value="repaired">repaired</option>
                                    <option value="topped_up">topped_up</option>
                                    <option value="cleaned">cleaned</option>
                                </select>
                                @error('items.' . $i . '.action')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Qty --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    wire:model.live="items.{{ $i }}.qty"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.qty') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.qty')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- UoM --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="text"
                                    placeholder="L, pcs"
                                    wire:model.live="items.{{ $i }}.uom"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.uom') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.uom')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Unit Cost --}}
                            <td class="px-2 py-2 align-top">
                                <div class="flex rounded-md shadow-sm">
                                    <span
                                        class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-slate-500">
                                        Rp
                                    </span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        wire:model.live="items.{{ $i }}.unit_cost"
                                        class="block w-full py-2 px-3 rounded-r-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                               @error('items.' . $i . '.unit_cost') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                </div>
                                @error('items.' . $i . '.unit_cost')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Discount --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                    wire:model.live="items.{{ $i }}.discount"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.discount') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.discount')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Tax rate --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="{{ (string) ($global_tax_rate ?? 0) }}"
                                    inputmode="decimal"
                                    wire:model.live="items.{{ $i }}.tax_rate"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.tax_rate') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.tax_rate')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Line total --}}
                            <td class="px-2 py-2 align-top whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-900">
                                    Rp {{ number_format($lt, 0, ',', '.') }}
                                </div>
                                <div class="mt-0.5 text-[11px] text-slate-500 space-x-2">
                                    <span>Base: Rp {{ number_format($base, 0, ',', '.') }}</span>
                                    <span>VAT: Rp {{ number_format($tax, 0, ',', '.') }}</span>
                                </div>
                            </td>

                            {{-- Remarks --}}
                            <td class="px-2 py-2 align-top">
                                <input
                                    type="text"
                                    placeholder="Optional"
                                    wire:model.live="items.{{ $i }}.remarks"
                                    class="block w-full py-2 px-3 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('items.' . $i . '.remarks') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                                @error('items.' . $i . '.remarks')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Remove --}}
                            <td class="px-2 py-2 align-top text-right">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $i }})"
                                    class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 p-1 text-rose-600 hover:bg-rose-100"
                                    title="Remove">
                                    <i class="bi bi-x-lg text-[0.8rem]"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-6 text-center">
                                <div class="flex flex-col items-center text-slate-400">
                                    <i class="bi bi-clipboard2-x text-2xl mb-1"></i>
                                    <div class="text-sm font-semibold">No items yet</div>
                                    <div class="text-[11px] mb-2">
                                        Click “Add Item” to start listing parts or checks.
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="addItem"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                        <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                                        Add Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer totals --}}
        @php
            $totBase = 0.0;
            $totTax  = 0.0;

            foreach ($items as $r) {
                $qty   = (float) ($r['qty'] ?? 0);
                $uc    = (float) ($r['unit_cost'] ?? 0);
                $disc  = max(0, min(100, (float) ($r['discount'] ?? 0)));

                $rowTr = $r['tax_rate'] ?? null;
                $tr    = $rowTr === '' || $rowTr === null ? null : max(0, min(100, (float) $rowTr));
                $rate  = $tr ?? ($global_tax_rate ?? 0);

                $base = $qty * $uc * (1 - $disc / 100);
                $tax  = $base * ($rate / 100);

                $totBase += round($base, 2);
                $totTax  += round($tax, 2);
            }

            $grand = $totBase + $totTax;
        @endphp

        <div
            class="border-t border-slate-100 px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    Items:
                    <span class="font-semibold text-slate-800">{{ collect($items)->count() }}</span>
                </div>
                <div>
                    Workshop:
                    <span class="font-semibold text-slate-800">{{ $workshop ?: '—' }}</span>
                </div>
                <div>
                    Date:
                    <span class="font-semibold text-slate-800">
                        {{ $service_date ?: now()->toDateString() }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="text-[11px] text-slate-600">
                    Subtotal:
                    <strong class="text-slate-900">Rp {{ number_format($totBase, 0, ',', '.') }}</strong>
                    <span class="mx-1 text-slate-400">•</span>
                    VAT:
                    <strong class="text-slate-900">Rp {{ number_format($totTax, 0, ',', '.') }}</strong>
                </div>
                <div class="text-sm">
                    Grand Total:
                    <span class="font-bold text-slate-900">
                        Rp {{ number_format($grand, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Mobile actions + desktop fallback --}}
                <div class="flex items-center gap-2">
                    <a href="{{ $vehicle ? route('vehicles.show', $vehicle) : route('vehicles.index') }}"
                       class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancel
                    </a>
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60">
                        <span class="mr-1 inline-flex" wire:loading wire:target="save">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-save mr-1 text-[0.85rem]"></i>
                        </span>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
