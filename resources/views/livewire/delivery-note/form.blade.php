<div class="container py-4">

    @include('partials.alert-success-error')

    <form wire:submit.prevent="submit" class="needs-validation" novalidate>

        {{-- Draft toggle --}}
        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="is_draft" wire:model="is_draft">
            <label class="form-check-label fw-bold" for="is_draft">Save as Draft</label>
        </div>

        {{-- General Info --}}
        <fieldset class="border rounded p-3 mb-4">
            <legend class="float-none w-auto px-2 text-primary fs-5">Delivery Note Info</legend>

            <div class="row g-3 ">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select @error('branch') is-invalid @enderror" wire:model.live="branch">
                            <option value="JAKARTA">JAKARTA (DJ KBN)</option>
                            <option value="KARAWANG">KARAWANG (DJ KIIC)</option>
                        </select>
                        @error('branch')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ritasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('ritasi') is-invalid @enderror" wire:model="ritasi">
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
                        @error('ritasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Note Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('delivery_note_date') is-invalid @enderror"
                            wire:model="delivery_note_date">
                        @error('delivery_note_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Vehicle - Driver <span class="text-danger">*</span></label>
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
                        }" x-init="init" class="position-relative">
                            <input type="text" x-model="search"
                                class="form-control @error('vehicle_id') is-invalid @enderror" @focus="show = true"
                                @input="show = true" @blur="setTimeout(() => show = false, 150)"
                                placeholder="Search by plate or driver" />

                            <ul class="list-group position-absolute w-100 z-10" x-show="show && filtered().length"
                                style="max-height: 150px; overflow-y: auto;" x-transition>
                                <template x-for="item in filtered()" :key="item.id">
                                    <li class="list-group-item list-group-item-action" @click="select(item)">
                                        <span x-text="item.plate_number"></span> —
                                        <small class="text-muted" x-text="item.driver_name"></small>
                                    </li>
                                </template>
                            </ul>

                            @error('vehicle_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departure Time</label>
                        <input type="time" class="form-control @error('departure_time') is-invalid @enderror"
                            wire:model="departure_time">
                        @error('departure_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Return Time</label>
                        <input type="time" class="form-control @error('return_time') is-invalid @enderror"
                            wire:model="return_time">
                        @error('return_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </fieldset>

        {{-- DESTINATIONS --}}
        <fieldset class="border rounded p-3 mb-4">
            <legend class="float-none w-auto px-2 text-primary fs-5">Destinations</legend>

            @foreach ($destinations as $index => $dest)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold">Destination #{{ $index + 1 }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="removeDestination({{ $index }})">
                                🗑 Remove
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Destination <span class="text-danger">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: @entangle('destinations.' . $index . '.destination'),
                                    suggestions: @js($destinationSuggestions), // array of {name, city}
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
                                }" class="position-relative">
                                    <input type="text"
                                        class="form-control @error("destinations.$index.destination") is-invalid @enderror"
                                        x-model="search" @focus="open = true"
                                        @blur="setTimeout(() => open = false, 150)"
                                        placeholder="Search by name or city">

                                    <ul x-show="open && filtered().length"
                                        class="list-group position-absolute w-100 z-10"
                                        style="max-height: 150px; overflow-y: auto;" x-transition>
                                        <template x-for="(item, i) in filtered()" :key="i">
                                            <li class="list-group-item list-group-item-action" @click="select(item)">
                                                <span x-text="item.name + ' - ' + item.city"></span>
                                            </li>
                                        </template>
                                    </ul>

                                    @error("destinations.$index.destination")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col">
                                <label class="form-label small">Delivery Order(s)</label>

                                <div x-data="{
                                    orders: $wire.entangle('destinations.{{ $index }}.delivery_order_numbers') ?? [],
                                    chunkSize: 3, // change this to control items per row
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
                                        return 'col-md-' + Math.floor(12 / this.chunkSize);
                                    }
                                }">
                                    <template x-if="orders.length > 0">
                                        <template x-for="(chunk, rowIndex) in chunkedOrders()" :key="rowIndex">
                                            <div class="row g-1 mb-1">
                                                <template x-for="(order, i) in chunk" :key="i">
                                                    <div :class="colClass()">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                x-model="orders[rowIndex * chunkSize + i]"
                                                                placeholder="Order #" />
                                                            <button class="btn btn-outline-danger" type="button"
                                                                @click="removeOrder(rowIndex * chunkSize + i)">×</button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </template>

                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1"
                                        @click="addOrder()">
                                        + Add DO
                                    </button>
                                </div>
                                @error("destinations.$index.delivery_order_numbers")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea wire:model="destinations.{{ $index }}.remarks"
                                    class="form-control @error("destinations.$index.remarks") is-invalid @enderror" placeholder="Optional notes..."
                                    rows="3"></textarea>
                                @error("destinations.$index.remarks")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cost Inputs --}}
                            <div class="col-md-4">
                                @include('components.delivery-cost-input', [
                                    'label' => 'Driver Cost',
                                    'wireKey' => "destinations.{$index}.driver_cost",
                                    'currencyKey' => "destinations.{$index}.driver_cost_currency",
                                    'value' => $dest['driver_cost'] ?? 0,
                                ])
                            </div>
                            <div class="col-md-4">
                                @include('components.delivery-cost-input', [
                                    'label' => 'Kenek Cost',
                                    'wireKey' => "destinations.{$index}.kenek_cost",
                                    'currencyKey' => "destinations.{$index}.kenek_cost_currency",
                                    'value' => $dest['kenek_cost'] ?? 0,
                                ])
                            </div>
                            <div class="col-md-4">
                                @include('components.delivery-cost-input', [
                                    'label' => 'Balikan Cost',
                                    'wireKey' => "destinations.{$index}.balikan_cost",
                                    'currencyKey' => "destinations.{$index}.balikan_cost_currency",
                                    'value' => $dest['balikan_cost'] ?? 0,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="text-end">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="addDestination">
                    + Add Another Destination
                </button>
            </div>
        </fieldset>


        {{-- Action Buttons --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('delivery-notes.index') }}" class="btn btn-light">← Cancel</a>
            <button type="submit" class="btn btn-primary">💾 Save Delivery Note</button>
        </div>
    </form>
</div>
