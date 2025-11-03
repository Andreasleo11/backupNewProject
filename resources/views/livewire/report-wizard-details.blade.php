<div>
    {{-- Header --}}
    <div class="mb-4">
        <h4 class="fw-bold">📝 {{ $reportId ? 'Edit' : 'Add' }} Part Details</h4>
        <p class="text-muted">Customer: <strong>{{ session('report.customer') ?? '-' }}</strong></p>
    </div>

    {{-- Part Details List --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            @forelse ($details as $index => $detail)
                <div class="row g-3 align-items-start px-3 py-2 border-bottom" wire:key="detail-row-{{ $index }}">
                    {{-- Part Name --}}
                    <div class="col-md">
                        <label class="form-label">Part Name</label>
                        <input type="text" wire:model.live="details.{{ $index }}.part_name"
                            class="form-control @error("details.$index.part_name") is-invalid @enderror"
                            placeholder="Type to search part...">
                        @error("details.$index.part_name")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- Suggestions --}}
                        @if (!empty($partSuggestions[$index]))
                            <ul class="list-group position-absolute w-auto z-3"
                                style="max-height: 200px; overflow-y: auto;" x-data
                                x-on:scroll.passive="
                                    if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight) {
                                        $wire.loadMoreSuggestions({{ $index }})
                                    }
                                ">
                                @foreach ($partSuggestions[$index] as $suggestion)
                                    <li class="list-group-item list-group-item-action"
                                        wire:click="selectPartSuggestion({{ $index }}, '{{ addslashes($suggestion) }}')">
                                        {{ $suggestion }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Rec Quantity --}}
                    <div class="col-md">
                        <label class="form-label">Rec Qty</label>
                        <input type="number" wire:model="details.{{ $index }}.rec_quantity"
                            class="form-control @error("details.$index.rec_quantity") is-invalid @enderror"
                            placeholder="Received Qty">
                        @error("details.$index.rec_quantity")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Verified Quantity --}}
                    <div class="col-md">
                        <label class="form-label">Verified Qty</label>
                        <input type="number" wire:model="details.{{ $index }}.verify_quantity"
                            class="form-control @error("details.$index.verify_quantity") is-invalid @enderror"
                            placeholder="Verified Qty">
                        @error("details.$index.verify_quantity")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Can Use --}}
                    <div class="col-md">
                        <label class="form-label">Can Use</label>
                        <input type="number" wire:model="details.{{ $index }}.can_use"
                            class="form-control @error("details.$index.can_use") is-invalid @enderror"
                            placeholder="Can Use Qty">
                        @error("details.$index.can_use")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Can't Use --}}
                    <div class="col-md">
                        <label class="form-label">Can't Use</label>
                        <input type="number" wire:model="details.{{ $index }}.cant_use"
                            class="form-control @error("details.$index.cant_use") is-invalid @enderror"
                            placeholder="Can't Use Qty">
                        @error("details.$index.cant_use")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Price --}}
                    <div class="col">
                        <label class="form-label">Price</label>
                        <div class="input-group" x-data="{
                            input: @entangle('details.' . $index . '.price').live,
                            formatNumber(value) {
                                if (!value) return '';
                        
                                // Remove any commas (thousands separators) before formatting
                                value = value.toString().replace(/,/g, '');
                        
                                let [intPart, decimalPart] = value.split('.');
                        
                                // Add thousands separator (comma) to integer part
                                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        
                                // Rejoin with decimal part (if any)
                                return decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
                            },
                            unformat(value) {
                                return value.replace(/,/g, ''); // remove commas for internal storage
                            }
                        }">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                class="form-control @error("details.$index.price") is-invalid @enderror" x-model="input"
                                x-on:input="input = formatNumber(unformat($event.target.value))" placeholder="Price">
                        </div>
                        @if (!empty($details[$index]['part_name']) && !empty($details[$index]['price']))
                            <small class="text-success">Auto-filled from price log</small>
                        @endif
                        @error("details.$index.price")
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Remove Button --}}
                    <div class="col-auto text-center pt-4">
                        <button class="btn btn-sm btn-outline-danger"
                            wire:click.prevent="removeDetailRow({{ $index }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-4 me-2"></i>
                    No part details added yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Add Row --}}
    <div class="mb-4 text-end">
        <button class="btn btn-outline-primary" wire:click.prevent="addDetailRow">
            + Add Row
        </button>
    </div>

    {{-- Navigation Buttons --}}
    <div class="d-flex justify-content-between">
        <button class="btn btn-secondary" wire:click="goBack"><i class="bi bi-arrow-left"></i>
            Back</button>
        <button class="btn btn-primary" wire:click="saveDetails">Next <i class="bi bi-arrow-right"></i></button>
    </div>

    {{-- JS Alert Fallback --}}
    <script type="module">
        Livewire.on('showAlert', message => alert(message));
    </script>
</div>
