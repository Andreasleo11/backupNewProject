<div class="max-w-4xl mx-auto px-3 md:px-4 py-4 space-y-4">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1">
            <li>
                <a href="{{ route('vehicles.index') }}" class="hover:text-slate-800 hover:underline">
                    Vehicles
                </a>
            </li>
            <li class="text-slate-400">/</li>
            <li class="font-medium text-slate-700">
                {{ $vehicle?->exists ? 'Edit Vehicle' : 'New Vehicle' }}
            </li>
        </ol>
    </nav>

    {{-- Page header / hero --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-start justify-between gap-3 px-4 py-3 md:px-5 md:py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <i class="bi bi-truck text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-semibold text-slate-900">
                        {{ $vehicle?->exists ? 'Edit Vehicle' : 'New Vehicle' }}
                    </h1>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Keep your fleet info up to date.
                    </p>
                </div>
            </div>

            {{-- Desktop actions --}}
            <div class="hidden md:flex items-center gap-2">
                @if ($fullFeature && $vehicle?->exists)
                    <button type="button" wire:click="delete"
                        wire:confirm="Delete this vehicle? This cannot be undone." wire:loading.attr="disabled"
                        wire:target="delete"
                        class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 shadow-sm hover:bg-rose-100 disabled:opacity-60">
                        <span wire:loading wire:target="delete"
                            class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-rose-400 border-t-transparent"></span>
                        <i class="bi bi-trash mr-1 text-[0.9rem]" wire:loading.remove wire:target="delete"></i>
                        Delete
                    </button>
                @endif

                <a href="{{ route('vehicles.index') }}"
                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    Cancel
                </a>

                <button type="button" wire:click="save"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <i class="bi bi-save mr-1 text-[0.9rem]"></i>
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Main card / form --}}
    <form wire:submit.prevent="save" x-data="{
        driver_name: @entangle('driver_name'),
        plate_number: @entangle('plate_number'),
        @if ($fullFeature) brand: @entangle('brand'),
                    model: @entangle('model'),
                    year: @entangle('year'),
                    vin: @entangle('vin'),
                    odometer: @entangle('odometer'),
                    status: @entangle('status'),
                    sold_at: @entangle('sold_at'), @endif
    }"
        class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="px-4 py-4 md:px-5 md:py-5 space-y-4">

            {{-- Section: Driver & Plate --}}
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Driver &amp; Plate
                    </h2>

                    @if ($fullFeature)
                        <span
                            class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-1 text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200">
                            <span class="mr-1 text-[0.7rem] text-slate-400">Status:</span>
                            <span class="font-semibold capitalize" x-text="status || 'active'"></span>
                        </span>
                    @endif
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    {{-- Driver name --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            Driver Name
                        </label>
                        <div
                            class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                            <span class="pl-2 pr-1 text-slate-400">
                                <i class="bi bi-person text-[0.9rem]"></i>
                            </span>
                            <input type="text" x-model="driver_name" autocomplete="name" placeholder="Raymond"
                                class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none">
                        </div>
                        @error('driver_name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Optional, assign current driver.
                        </p>
                    </div>

                    {{-- Plate number --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            Plate Number <span class="text-rose-500">*</span>
                        </label>
                        <div
                            class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                            <span class="pl-2 pr-1 text-slate-400">
                                <i class="bi bi-credit-card-2-front text-[0.9rem]"></i>
                            </span>
                            <input type="text" x-model.trim="plate_number"
                                @input="plate_number = (plate_number || '').toUpperCase()" autocomplete="off"
                                placeholder="B 1234 XYZ"
                                class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm uppercase text-slate-900 placeholder:text-slate-400 focus:outline-none">
                        </div>
                        @error('plate_number')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Unique per vehicle.
                        </p>
                    </div>

                    {{-- Status --}}
                    @php use App\Enums\VehicleStatus; @endphp

                    @if ($fullFeature)
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Status
                            </label>

                            <div class="flex flex-wrap gap-1">
                                @foreach (VehicleStatus::cases() as $case)
                                    @php $v = $case->value; @endphp

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" class="peer sr-only" id="st-{{ $v }}"
                                            value="{{ $v }}" x-model="status" autocomplete="off">

                                        <span
                                            class="inline-flex items-center rounded-md border border-slate-300 bg-white
                               px-2.5 py-1 text-[11px] font-medium text-slate-700
                               hover:bg-slate-50 transition
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-slate-400
                               {{ $case->radioCheckedClasses() }}">
                                            <i class="bi bi-{{ $case->icon() }} mr-1 text-[0.85rem]"></i>
                                            {{ $case->label() }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @error('status')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                </div>
            </div>

            @if ($fullFeature)
                <div class="h-px bg-slate-100 my-1"></div>

                {{-- Section: Vehicle Specs --}}
                <div class="space-y-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Vehicle Specs
                    </h2>

                    <div class="grid gap-3 md:grid-cols-3">
                        {{-- Brand --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Brand
                            </label>
                            <div
                                class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <span class="pl-2 pr-1 text-slate-400">
                                    <i class="bi bi-badge-ad text-[0.9rem]"></i>
                                </span>
                                <input type="text" x-model.trim="brand" placeholder="Toyota"
                                    autocomplete="organization"
                                    class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none">
                            </div>
                            @error('brand')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Model --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Model
                            </label>
                            <div
                                class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <span class="pl-2 pr-1 text-slate-400">
                                    <i class="bi bi-badge-3d text-[0.9rem]"></i>
                                </span>
                                <input type="text" x-model.trim="model" placeholder="Avanza"
                                    class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none">
                            </div>
                            @error('model')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Year --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Year
                            </label>
                            <div
                                class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <span class="pl-2 pr-1 text-slate-400">
                                    <i class="bi bi-calendar3 text-[0.9rem]"></i>
                                </span>
                                <input type="number" min="1900" max="{{ now()->year }}"
                                    x-model.number="year" placeholder="{{ now()->year }}"
                                    class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 focus:outline-none">
                            </div>
                            @error('year')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3 md:items-end">
                        {{-- VIN --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                VIN
                            </label>
                            <div
                                class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <span class="pl-2 pr-1 text-slate-400">
                                    <i class="bi bi-upc-scan text-[0.9rem]"></i>
                                </span>
                                <input type="text" x-model.trim="vin" placeholder="Vehicle Identification Number"
                                    class="w-full rounded-r-md border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none">
                            </div>
                            @error('vin')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px] text-slate-400">
                                Useful for official records and warranty.
                            </p>
                        </div>

                        {{-- Odometer --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Odometer
                            </label>
                            <div
                                class="flex items-center rounded-md border border-slate-300 bg-white text-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <span class="pl-2 pr-1 text-slate-400">
                                    <i class="bi bi-speedometer text-[0.9rem]"></i>
                                </span>
                                <input type="number" min="0" step="1" x-model.number="odometer"
                                    placeholder="0" inputmode="numeric"
                                    class="w-full border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 focus:outline-none">
                                <span class="pr-2 pl-1 text-[11px] text-slate-500">
                                    km
                                </span>
                            </div>
                            @error('odometer')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Preview pill --}}
                    <div class="md:w-1/3 md:ml-auto">
                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-center">
                            <div class="text-[11px] uppercase tracking-wide text-slate-400">
                                Preview
                            </div>
                            <div class="mt-1 text-xs font-semibold text-slate-800">
                                <span x-text="(plate_number || 'PLATE').toUpperCase()"></span>
                                <template x-if="brand || model">
                                    <span>
                                        &nbsp;—&nbsp;
                                        <span x-text="brand || ''"></span>
                                        <span x-text="model || 'MODEL'"></span>
                                    </span>
                                </template>
                                <template x-if="year">
                                    <span>
                                        &nbsp;(<span x-text="year"></span>)
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sold details --}}
                <div x-show="status === 'sold'" x-transition class="mt-4 space-y-3">
                    <div class="h-px bg-slate-100"></div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Sold Details
                    </h2>
                    <div class="grid gap-3 md:grid-cols-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Sold Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" x-model="sold_at" name="sold_at"
                                class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @error('sold_at')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-3">
                            <div
                                class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <p class="mb-0">
                                    Marking as <strong>Sold</strong> will hide this vehicle from active lists and
                                    prevent new service records.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Mobile action bar --}}
        <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50 px-3 py-2 md:hidden">
            <a href="{{ route('vehicles.index') }}"
                class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Cancel
            </a>
            <div class="flex items-center gap-2">
                @if ($fullFeature && $vehicle?->exists)
                    <button type="button" wire:click="delete"
                        wire:confirm="Delete this vehicle? This cannot be undone." wire:loading.attr="disabled"
                        wire:target="delete"
                        class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 shadow-sm hover:bg-rose-100 disabled:opacity-60">
                        <span wire:loading wire:target="delete"
                            class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-rose-400 border-t-transparent"></span>
                        <i class="bi bi-trash text-[0.9rem]" wire:loading.remove wire:target="delete"></i>
                    </button>
                @endif

                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <i class="bi bi-save mr-1 text-[0.9rem]"></i>
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
