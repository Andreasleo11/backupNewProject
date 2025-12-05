<div class="max-w-5xl mx-auto px-4 py-6">

    <form wire:submit.prevent="submit" novalidate class="space-y-6">

        {{-- Draft toggle --}}
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <label for="is_draft" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="is_draft" type="checkbox" wire:model="is_draft"
                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-medium text-slate-800">
                    Save as draft
                </span>
            </label>
            <span class="text-xs text-slate-500">
                Draft tidak akan muncul sebagai dokumen final.
            </span>
        </div>

        {{-- DELIVERY NOTE INFO --}}
        <fieldset class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <legend class="pt-3 px-4 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Delivery Note Info
            </legend>

            <div class="px-4 pb-5 pt-3">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- LEFT --}}
                    <div class="space-y-4">
                        {{-- Branch --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Branch <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="branch"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500
                                               @error('branch') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="JAKARTA">JAKARTA (DJ KBN)</option>
                                <option value="KARAWANG">KARAWANG (DJ KIIC)</option>
                            </select>
                            @error('branch')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ritasi --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Ritasi <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="ritasi"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500
                                               @error('ritasi') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="">-- Select ritasi --</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                @if ($branch === 'KARAWANG')
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                @endif
                            </select>
                            @error('ritasi')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Delivery note date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="delivery_note_date"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('delivery_note_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('delivery_note_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- RIGHT --}}
                    <div class="space-y-4">

                        {{-- Vehicle - Driver --}}
                        <div x-data="{
                            search: '',
                            show: false,
                            selectedId: @entangle('vehicle_id'),
                            vehicles: @js($vehicleSuggestions),
                            filtered() {
                                if (!this.search) return [];
                                return this.vehicles.filter(v =>
                                    v.plate_number.toLowerCase().includes(this.search.toLowerCase()) ||
                                    v.driver_name.toLowerCase().includes(this.search.toLowerCase())
                                ).slice(0, 10);
                            },
                            select(vehicle) {
                                this.search = `${vehicle.plate_number} — ${vehicle.driver_name}`;
                                this.selectedId = vehicle.id;
                                this.show = false;
                            },
                            init() {
                                const selected = this.vehicles.find(v => v.id === this.selectedId);
                                if (selected) {
                                    this.search = `${selected.plate_number} — ${selected.driver_name}`;
                                }
                            }
                        }" x-init="init" class="relative">
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Vehicle – Driver <span class="text-red-500">*</span>
                            </label>

                            <input type="text" x-model="search" @focus="show = true" @input="show = true"
                                @blur="setTimeout(() => show = false, 150)" placeholder="Search by plate or driver"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('vehicle_id')
                                                border-red-500 focus:border-red-500 focus:ring-red-500
                                                @enderror">

                            <ul x-show="show && filtered().length" x-transition
                                class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white text-sm shadow-lg">
                                <template x-for="item in filtered()" :key="item.id">
                                    <li @click="select(item)" class="cursor-pointer px-3 py-1.5 hover:bg-slate-50">
                                        <span class="font-medium" x-text="item.plate_number"></span>
                                        <span class="text-slate-400">—</span>
                                        <span class="text-xs text-slate-600" x-text="item.driver_name"></span>
                                    </li>
                                </template>
                            </ul>

                            @error('vehicle_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Departure time --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Departure time
                            </label>
                            <input type="time" wire:model="departure_time"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('departure_time') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('departure_time')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Return time --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Return time
                            </label>
                            <input type="time" wire:model="return_time"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('return_time') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('return_time')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        {{-- DESTINATIONS --}}
        <fieldset class="space-y-3 rounded-xl border border-slate-200 bg-white shadow-sm">
            <legend class="pt-3 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Destinations
            </legend>

            <div class="px-4 pb-4 pt-1 space-y-3">
                @foreach ($destinations as $index => $dest)
                    <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-3 py-3">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Destination #{{ $index + 1 }}
                            </p>
                            <button type="button" class="text-xs font-medium text-red-600 hover:text-red-700"
                                wire:click="removeDestination({{ $index }})">
                                Remove
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

                            {{-- Destination search --}}
                            <div class="md:col-span-1">
                                <label class="mb-1 block text-sm font-medium text-slate-700">
                                    Destination <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{
                                    open: false,
                                    search: @entangle('destinations.' . $index . '.destination'),
                                    suggestions: @js($destinationSuggestions),
                                    filtered() {
                                        if (!this.search) return [];
                                        return this.suggestions.filter(item =>
                                            item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                            item.city.toLowerCase().includes(this.search.toLowerCase())
                                        ).slice(0, 10);
                                    },
                                    select(item) {
                                        this.search = item.name + ' - ' + item.city;
                                        this.open = false;
                                    }
                                }" class="relative">
                                    <input type="text" x-model="search" @focus="open = true"
                                        @blur="setTimeout(() => open = false, 150)" placeholder="Search by name or city"
                                        class="py-2 px-3 block w-full rounded-md border-slate-300 text-sm shadow-sm
                                                      focus:border-indigo-500 focus:ring-indigo-500
                                                      @error("destinations.$index.destination")
                                    border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                                <ul x-show="open && filtered().length" x-transition
                                    class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white shadow-lg">
                                    <template x-for="(item, i) in filtered()" :key="i">
                                        <li @click="select(item)"
                                            class="cursor-pointer px-3 py-1.5 text-sm hover:bg-slate-50">
                                            <span class="font-medium" x-text="item.name"></span>
                                            <span class="text-slate-400"> – </span>
                                            <span class="text-xs text-slate-500" x-text="item.city"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            @error("destinations.$index.destination")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Delivery orders --}}
                        <div class="md:col-span-1 lg:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Delivery order(s)
                            </label>

                            <div x-data="{
                                orders: $wire.entangle('destinations.{{ $index }}.delivery_order_numbers') ?? [],
                                chunkSize: 3,
                                chunkedOrders() {
                                    const size = this.chunkSize;
                                    const result = [];
                                    for (let i = 0; i < this.orders.length; i += size) {
                                        result.push(this.orders.slice(i, i + size));
                                    }
                                    return result;
                                },
                                addOrder() {
                                    if (!Array.isArray(this.orders)) this.orders = [];
                                    this.orders.push('');
                                },
                                removeOrder(index) {
                                    this.orders.splice(index, 1);
                                },
                                colClass() {
                                    return 'grid-cols-' + this.chunkSize;
                                }
                            }" class="space-y-1">

                                <template x-if="orders.length > 0">
                                    <template x-for="(chunk, rowIndex) in chunkedOrders()" :key="rowIndex">
                                        <div class="grid gap-1 md:grid-cols-3">
                                            <template x-for="(order, i) in chunk" :key="i">
                                                <div class="flex items-center gap-1">
                                                    <input type="text"
                                                        x-model="orders[rowIndex * chunkSize + i]"
                                                        placeholder="Order #"
                                                        class="block w-full rounded-md border-slate-300 text-xs shadow-sm
                                                                          focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2.5">
                                                    <button type="button"
                                                        class="rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-500 hover:bg-slate-100"
                                                        @click="removeOrder(rowIndex * chunkSize + i)">
                                                        Remove
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </template>

                                <button type="button"
                                    class="mt-1 inline-flex items-center rounded-md border border-slate-300
                                                       px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    @click="addOrder()">
                                    + Add DO
                                </button>
                            </div>
                            @error("destinations.$index.delivery_order_numbers")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remarks --}}
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Remarks
                            </label>
                            <textarea rows="3" wire:model="destinations.{{ $index }}.remarks" placeholder="Optional notes..."
                                class="py-2 px-3 block w-full rounded-md border-slate-300 text-sm shadow-sm
                                                     focus:border-indigo-500 focus:ring-indigo-500
                                                     @error("destinations.$index.remarks") border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                            @error("destinations.$index.remarks")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cost inputs (masih pakai component, tinggal update templatenya ke Tailwind) --}}
                        <div class="md:col-span-1">
                            @include('components.delivery-cost-input', [
                                'label' => 'Driver cost',
                                'wireKey' => "destinations.{$index}.driver_cost",
                                'currencyKey' => "destinations.{$index}.driver_cost_currency",
                                'value' => $dest['driver_cost'] ?? 0,
                            ])
                        </div>
                        <div class="md:col-span-1">
                            @include('components.delivery-cost-input', [
                                'label' => 'Kenek cost',
                                'wireKey' => "destinations.{$index}.kenek_cost",
                                'currencyKey' => "destinations.{$index}.kenek_cost_currency",
                                'value' => $dest['kenek_cost'] ?? 0,
                            ])
                        </div>
                        <div class="md:col-span-1">
                            @include('components.delivery-cost-input', [
                                'label' => 'Balikan cost',
                                'wireKey' => "destinations.{$index}.balikan_cost",
                                'currencyKey' => "destinations.{$index}.balikan_cost_currency",
                                'value' => $dest['balikan_cost'] ?? 0,
                            ])
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end pt-1">
                <button type="button" wire:click="addDestination"
                    class="inline-flex items-center rounded-md border border-dashed border-slate-300
                                       px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    + Add another destination
                </button>
            </div>
        </div>
    </fieldset>

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('delivery-notes.index') }}"
            class="inline-flex items-center rounded-md border border-slate-200 bg-white
                          px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Cancel
        </a>
        <button type="submit"
            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-1.5
                               text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Save delivery note
        </button>
    </div>
</form>
</div>
