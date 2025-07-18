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

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="branch" class="form-label">Branch</label>
                    <select id="branch" class="form-select @error('branch') is-invalid @enderror"
                        wire:model="branch">
                        <option value="JAKARTA">JAKARTA (DJ KBN)</option>
                        <option value="KARAWANG">KARAWANG (DJ KIIC)</option>
                    </select>
                    @error('branch')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="ritasi" class="form-label">Ritasi</label>
                    <select id="ritasi" class="form-select @error('ritasi') is-invalid @enderror"
                        wire:model="ritasi">
                        <option value="">-- Select Ritasi --</option>
                        <option value="1">1 (Pagi)</option>
                        <option value="2">2 (Siang)</option>
                        <option value="3">3 (Sore)</option>
                        <option value="4">4 (Malam)</option>
                    </select>
                    @error('ritasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Delivery Note Date</label>
                    <input type="date" class="form-control @error('delivery_note_date') is-invalid @enderror"
                        wire:model="delivery_note_date">
                    @error('delivery_note_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Departure Time</label>
                    <input type="time" class="form-control @error('departure_time') is-invalid @enderror"
                        wire:model="departure_time">
                    @error('departure_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Return Time</label>
                    <input type="time" class="form-control @error('return_time') is-invalid @enderror"
                        wire:model="return_time">
                    @error('return_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror"
                        wire:model="vehicle_number" placeholder="B 1234 XYZ">
                    @error('vehicle_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Driver Name</label>
                    <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                        wire:model="driver_name" placeholder="John Doe">
                    @error('driver_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </fieldset>

        {{-- Destinations --}}
        <fieldset class="border rounded p-3 mb-4">
            <legend class="float-none w-auto px-2 text-primary fs-5">Destinations</legend>

            @foreach ($destinations as $index => $dest)
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <input type="text"
                            class="form-control @error("destinations.$index.destination") is-invalid @enderror"
                            wire:model="destinations.{{ $index }}.destination" placeholder="Destination">
                        @error("destinations.$index.destination")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <input type="text"
                            class="form-control @error("destinations.$index.delivery_order_number") is-invalid @enderror"
                            wire:model="destinations.{{ $index }}.delivery_order_number"
                            placeholder="Delivery Order #">
                        @error("destinations.$index.delivery_order_number")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <input type="text"
                            class="form-control @error("destinations.$index.remarks") is-invalid @enderror"
                            wire:model="destinations.{{ $index }}.remarks" placeholder="Remarks (optional)">
                        @error("destinations.$index.remarks")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm"
                            wire:click="removeDestination({{ $index }})" title="Remove">×</button>
                    </div>
                </div>
            @endforeach

            <div class="text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addDestination">
                    + Add Destination
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
