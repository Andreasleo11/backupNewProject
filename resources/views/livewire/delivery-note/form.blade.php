@section('page-title', $deliveryNote?->exists ? 'Edit Delivery Note' : 'Create Delivery Note')
@section('page-subtitle', 'Specify branch, vehicle, and destinations for logistical estimations.')

<div class="max-w-7xl mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
    {{-- MAIN FORM AREA --}}
    <div class="flex-1 w-full lg:max-w-4xl space-y-6">
        
        <div class="mb-2">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Create Delivery Note</h1>
            <p class="text-sm text-slate-500 mt-1">Specify branch, vehicle, and destinations carefully. Estimations will be processed through the approval workflow.</p>
        </div>

        <form wire:submit.prevent="submit" novalidate id="dn-form" class="space-y-6">

            {{-- Draft toggle --}}
            <label for="is_draft" class="flex items-start gap-3 rounded-xl border border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm">
                <div class="flex h-5 items-center">
                    <input id="is_draft" type="checkbox" wire:model="is_draft"
                        class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition duration-150">
                </div>
                <div>
                    <span class="text-sm font-semibold text-slate-800 tracking-wide">
                        Save as Draft
                    </span>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Drafts are only visible to you and will not trigger approval workflows.
                    </p>
                </div>
            </label>

            {{-- DELIVERY NOTE INFO --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-slate-50/80 border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        General Information
                    </h2>
                </div>

                <div class="p-5 grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- LEFT --}}
                    <div class="space-y-5">
                        {{-- Branch --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Branch <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="branch"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition
                                               focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                               @error('branch') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="JAKARTA">JAKARTA (DJ KBN)</option>
                                <option value="KARAWANG">KARAWANG (DJ KIIC)</option>
                            </select>
                            @error('branch')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ritasi --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Ritasi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model="ritasi"
                                    class="block w-full appearance-none rounded-lg border-slate-300 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition
                                                   focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                                   @error('ritasi') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">-- Select Ritasi --</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    @if ($branch === 'KARAWANG')
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                    @endif
                                </select>
                            </div>
                            @error('ritasi')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Delivery note date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="delivery_note_date"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition
                                              focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                              @error('delivery_note_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('delivery_note_date')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- RIGHT --}}
                    <div class="space-y-5">
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
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Fleet Assignment <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <input type="text" x-model="search" @focus="show = true" @input="show = true"
                                    @blur="setTimeout(() => show = false, 150)" placeholder="Search plate or driver..."
                                    class="block w-full rounded-lg border-slate-300 bg-slate-50 pl-10 px-3 py-2.5 text-sm shadow-sm transition
                                                  focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                                  @error('vehicle_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            </div>
                            
                            <ul x-show="show && filtered().length" x-transition
                                class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-xl">
                                <template x-for="item in filtered()" :key="item.id">
                                    <li @click="select(item)" class="cursor-pointer px-4 py-2 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10" x-text="item.plate_number"></span>
                                            <span class="text-slate-600 font-medium text-xs" x-text="item.driver_name"></span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                            @error('vehicle_id')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Departure time --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Departure time
                            </label>
                            <input type="time" wire:model="departure_time"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition
                                              focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                              @error('departure_time') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('departure_time')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Return time --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Return time
                            </label>
                            <input type="time" wire:model="return_time"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition
                                              focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                              @error('return_time') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('return_time')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESTINATIONS --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between mt-8 mb-2">
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Trip Destinations
                    </h2>
                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 shadow-sm">
                        {{ count($destinations) }} Locations
                    </span>
                </div>

                @foreach ($destinations as $index => $dest)
                    <fieldset class="relative rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow transition-shadow overflow-hidden group">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500 group-hover:bg-indigo-600 transition-colors"></div>

                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <p class="text-sm font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-[10px] text-indigo-700 ring-1 ring-inset ring-indigo-200">{{ $index + 1 }}</span>
                                Stop Location
                            </p>
                            <button type="button" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-700 transition"
                                wire:click="removeDestination({{ $index }})">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                Remove
                            </button>
                        </div>

                        <div class="p-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {{-- Destination search --}}
                            <div class="space-y-1.5 lg:col-span-1">
                                <label class="block text-sm font-medium text-slate-700">
                                    Target Facility / City <span class="text-red-500">*</span>
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
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input type="text" x-model="search" @focus="open = true"
                                            @blur="setTimeout(() => open = false, 150)" placeholder="Search location..."
                                            class="block w-full rounded-lg border-slate-300 bg-slate-50 pl-9 px-3 py-2.5 text-sm shadow-sm transition
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                                          @error("destinations.$index.destination") border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    </div>
                                    <ul x-show="open && filtered().length" x-transition
                                        class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl">
                                        <template x-for="(item, i) in filtered()" :key="i">
                                            <li @click="select(item)"
                                                class="cursor-pointer px-4 py-2 text-sm hover:bg-slate-50 border-b border-slate-50 transition-colors last:border-0">
                                                <span class="font-bold text-slate-800" x-text="item.name"></span>
                                                <span class="text-slate-400 mx-1">•</span>
                                                <span class="text-xs text-slate-500 font-medium" x-text="item.city"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                @error("destinations.$index.destination")
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Delivery orders --}}
                            <div class="space-y-1.5 lg:col-span-1">
                                <label class="block text-sm font-medium text-slate-700">
                                    Assigned Delivery Orders
                                </label>
                                <div x-data="{
                                    orders: $wire.entangle('destinations.{{ $index }}.delivery_order_numbers') ?? [],
                                    chunkSize: 2,
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
                                    }
                                }">
                                    <template x-if="orders.length > 0">
                                        <div class="space-y-2 mb-2">
                                            <template x-for="(chunk, rowIndex) in chunkedOrders()" :key="rowIndex">
                                                <div class="grid gap-2 grid-cols-2">
                                                    <template x-for="(order, i) in chunk" :key="i">
                                                        <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-md pr-1 overflow-hidden focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                                                            <div class="bg-slate-100 px-2 py-2 border-r border-slate-200 text-slate-500 text-xs font-semibold">
                                                                #
                                                            </div>
                                                            <input type="text"
                                                                x-model="orders[rowIndex * chunkSize + i]"
                                                                placeholder="DO Number"
                                                                class="block w-full border-0 bg-transparent text-sm py-1.5 px-2 focus:ring-0">
                                                            <button type="button"
                                                                class="rounded p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                                                @click="removeOrder(rowIndex * chunkSize + i)">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <button type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300
                                                           px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300 transition"
                                        @click="addOrder()">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Attach DO Number
                                    </button>
                                </div>
                                @error("destinations.$index.delivery_order_numbers")
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Remarks --}}
                            <div class="space-y-1.5 lg:col-span-2">
                                <label class="block text-sm font-medium text-slate-700">
                                    Operational Notes / Remarks
                                </label>
                                <textarea rows="2" wire:model="destinations.{{ $index }}.remarks" placeholder="Optional notes for this leg of the trip..."
                                    class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm transition
                                                         focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                                         @error("destinations.$index.remarks") border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                                @error("destinations.$index.remarks")
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Costing Grid --}}
                            <div class="lg:col-span-2 mt-2" x-data="{ showCosts: {{ ($dest['driver_cost'] > 0 || $dest['kenek_cost'] > 0 || $dest['balikan_cost'] > 0) ? 'true' : 'false' }} }">
                                <button type="button" @click="showCosts = !showCosts" 
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors focus:outline-none">
                                    <svg x-show="!showCosts" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                    <svg x-show="showCosts" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                    <span x-text="showCosts ? 'Hide Cost Estimations' : 'Add Cost Estimations (Optional)'"></span>
                                </button>
                                
                                <div x-show="showCosts" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                     style="display: none;" class="mt-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Logistics Cost Estimations
                                    </h3>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        {{-- We use the original variables matching the backend components, styled cleaner --}}
                                        <div>
                                            @include('components.delivery-cost-input', [
                                                'label' => 'Driver cost',
                                                'wireKey' => "destinations.{$index}.driver_cost",
                                                'currencyKey' => "destinations.{$index}.driver_cost_currency",
                                                'value' => $dest['driver_cost'] ?? 0,
                                            ])
                                        </div>
                                        <div>
                                            @include('components.delivery-cost-input', [
                                                'label' => 'Kenek cost',
                                                'wireKey' => "destinations.{$index}.kenek_cost",
                                                'currencyKey' => "destinations.{$index}.kenek_cost_currency",
                                                'value' => $dest['kenek_cost'] ?? 0,
                                            ])
                                        </div>
                                        <div>
                                            @include('components.delivery-cost-input', [
                                                'label' => 'Balikan cost',
                                                'wireKey' => "destinations.{$index}.balikan_cost",
                                                'currencyKey' => "destinations.{$index}.balikan_cost_currency",
                                                'value' => $dest['balikan_cost'] ?? 0,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </fieldset>
                @endforeach

                <div class="flex justify-center pt-4">
                    <button type="button" wire:click="addDestination"
                        class="inline-flex items-center gap-2 rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 w-full justify-center
                                           px-4 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300 transition-colors shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        Add Another Location Stop
                    </button>
                </div>
            </div>
            
            {{-- Ghost Submit for Enter Key --}}
            <button type="submit" class="hidden"></button>
        </form>
    </div>

    {{-- RIGHT PANEL: SUMMARY REALTIME WIDGET --}}
    <aside class="w-full lg:w-80 flex-shrink-0 mt-6 lg:mt-0">
        <div class="sticky top-8 rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 overflow-hidden flex flex-col">
            
            <div class="bg-indigo-600 px-6 py-5">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Trip Summary
                </h3>
                <p class="text-indigo-100 text-xs mt-1 font-medium mix-blend-lighten">Real-time estimations</p>
            </div>
            
            <div class="px-6 py-5 flex-1 relative">
                
                {{-- Decorative pattern --}}
                <div class="absolute right-0 top-0 opacity-[0.03] pointer-events-none">
                    <svg width="120" height="120" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z"/></svg>
                </div>

                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between pb-3 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">No. of Stops</dt>
                        <dd class="font-bold text-slate-800">{{ count($destinations) }} Locations</dd>
                    </div>

                    <div class="flex justify-between pb-3 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Selected Branch</dt>
                        <dd class="font-bold text-slate-800">{{ $branch }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500 font-medium">Trip Ritasi</dt>
                        <dd class="font-bold text-slate-800">{{ $ritasi ?: '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-8 rounded-xl bg-slate-50 border border-slate-100 p-4 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-100 rounded-full blur-xl scale-0 group-hover:scale-100 transition-transform duration-700"></div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest relative z-10">Total Est. Cost</p>
                    <p class="mt-1 text-2xl font-black text-emerald-600 tracking-tight flex items-baseline gap-1 relative z-10">
                        <span class="text-sm font-semibold text-emerald-500">IDR</span>
                        {{ number_format($this->total_cost, 0, ',', '.') }}
                    </p>
                </div>

                @if($this->total_cost > 10000000)
                    <div class="mt-4 flex items-start gap-2 rounded-lg bg-red-50 p-3 border border-red-100">
                        <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <p class="text-xs font-medium text-red-800">
                            High cost trip. Will require extended Director Approval Flow.
                        </p>
                    </div>
                @else
                    <div class="mt-4 flex items-start gap-2 rounded-lg bg-indigo-50 p-3 border border-indigo-100">
                        <svg class="h-4 w-4 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-xs font-medium text-indigo-800">
                            Subject to automated Approval Flow resolution based on criteria.
                        </p>
                    </div>
                @endif
            </div>

            <div class="bg-white border-t border-slate-100 p-5 space-y-3">
                <button type="submit" form="dn-form" class="relative group w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-bold text-white shadow-md hover:shadow-xl hover:bg-black transition-all overflow-hidden focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                    <span class="absolute inset-0 w-full h-full -mt-1 rounded-lg opacity-30 bg-gradient-to-b from-transparent via-transparent to-black pointer-events-none"></span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    Save Delivery Note
                </button>
                <a href="{{ route('delivery-notes.index') }}" class="w-full flex justify-center items-center py-3 text-sm font-bold text-slate-500 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">
                    Discard Changes
                </a>
            </div>

        </div>
    </aside>
</div>
