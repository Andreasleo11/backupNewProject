<div class="container py-3">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
            <li class="breadcrumb-item active">{{ $vehicle?->exists ? 'Edit Vehicle' : 'New Vehicle' }}</li>
        </ol>
    </nav>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <div class="fw-semibold">Please fix the following:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast show align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check2-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    @endif


    {{-- Page header / hero --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                    style="width:44px;height:44px">
                    <i class="bi bi-truck fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0">{{ $vehicle?->exists ? 'Edit Vehicle' : 'New Vehicle' }}</h5>
                    <div class="text-muted small">Keep your fleet info up to date</div>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2">
                @if ($isSuperadmin && $vehicle?->exists)
                    <button type="button" class="btn btn-outline-danger" wire:click="delete"
                        wire:confirm="Delete this vehicle? This cannot be undone." wire:loading.attr="disabled"
                        wire:target="delete">
                        <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="delete"></span>
                        <i class="bi bi-trash me-1" wire:loading.remove wire:target="delete"></i>
                    </button>
                @endif
                <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button wire:click="save" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>

    {{-- Main card --}}
    <form wire:submit.prevent="save" class="card border-0 shadow-sm" x-data="{
        driver_name: @entangle('driver_name'),
        plate_number: @entangle('plate_number'),
        @if ($isSuperadmin) brand: @entangle('brand'),
          model: @entangle('model'),
          year: @entangle('year'),
          vin: @entangle('vin'),
          odometer: @entangle('odometer'),
          status: @entangle('status'), @endif
    }">
        <div class="card-body mb-2">
            {{-- Section: Driver & Plate --}}
            <div class ="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-uppercase text-muted">Driver & Plate</h6>
                @if ($isSuperadmin)
                    <span class="badge text-bg-light">
                        Status: <span class="ms-1 fw-semibold text-capitalize" x-text="status"></span>
                    </span>
                @endif
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Driver Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                            x-model="driver_name" placeholder="Raymond" autocomplete="name">
                        @error('driver_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Optional, assign current driver.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                        <input type="text" class="form-control @error('plate_number') is-invalid @enderror"
                            x-model.trim="plate_number" @input="plate_number = (plate_number || '').toUpperCase()"
                            placeholder="B 1234 XYZ" autocomplete="off" style="text-transform:uppercase">
                        @error('plate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Unique per vehicle.</div>
                </div>

                @if($isSuperadmin)
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <div class="btn-group w-100" role="group" aria-label="Status">
                        <input type="radio" class="btn-check" id="st-active" autocomplete="off" value="active"
                            x-model="status">
                        <label class="btn btn-outline-success" for="st-active"><i
                                class="bi bi-check2-circle me-1"></i>Active</label>

                        <input type="radio" class="btn-check" id="st-maint" autocomplete="off" value="maintenance"
                            x-model="status">
                        <label class="btn btn-outline-warning" for="st-maint"><i
                                class="bi bi-tools me-1"></i>Maintenance</label>

                        <input type="radio" class="btn-check" id="st-retired" autocomplete="off" value="retired"
                            x-model="status">
                        <label class="btn btn-outline-secondary" for="st-retired"><i
                                class="bi bi-archive me-1"></i>Retired</label>
                    </div>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @endif
            </div>

            @if ($isSuperadmin)
                <hr class="text-body-tertiary">

                {{-- Section: Vehicle Specs --}}
                <h6 class="text-uppercase text-muted mb-2">Vehicle Specs</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-badge-ad"></i></span>
                            <input type="text" class="form-control @error('brand') is-invalid @enderror"
                                x-model.trim="brand" placeholder="Toyota" autocomplete="organization">
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Model</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-badge-3d"></i></span>
                            <input type="text" class="form-control @error('model') is-invalid @enderror"
                                x-model.trim="model" placeholder="Avanza" autocomplete="on">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Year</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                            <input type="number" min="1900" max="{{ now()->year }}"
                                class="form-control @error('year') is-invalid @enderror" x-model.number="year"
                                placeholder="{{ now()->year }}">
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">VIN</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" class="form-control @error('vin') is-invalid @enderror"
                                x-model.trim="vin" placeholder="Vehicle Identification Number">
                            @error('vin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Useful for official records and warranty.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Odometer</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-speedometer"></i></span>
                            <input type="number" min="0" step="1"
                                class="form-control @error('odometer') is-invalid @enderror" x-model.number="odometer"
                                placeholder="0" inputmode="numeric" min="0" step="1">
                            <span class="input-group-text">km</span>
                            @error('odometer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        {{-- Quick preview pill --}}
                        <div class="bg-body-tertiary rounded-3 px-3 py-2 w-100 text-center">
                            <div class="small text-muted">Preview</div>
                            <div class="fw-semibold ">
                                <span x-text="(plate_number || 'PLATE').toUpperCase()"></span>
                                <template x-if="brand || model">
                                    <span> — <span x-text="brand"></span> <span
                                            x-text="model || 'MODEL'"></span></span>
                                </template>
                                <template x-if="year">
                                    <span> (<span x-text="year"></span>)</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
