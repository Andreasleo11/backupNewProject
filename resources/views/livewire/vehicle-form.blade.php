<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('vehicles.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Fleet Index
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-emerald-400"></div>

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                @if ($vehicleId)
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Edit Vehicle Profile
                @else
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Register New Fleet Vehicle
                @endif
            </h1>
        </div>

        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 border border-emerald-100 flex items-start gap-3">
                    <svg class="h-5 w-5 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Plate Number --}}
                    <div>
                        <label class="block text-sm font-semibold tracking-wide text-slate-700 mb-1.5">
                            Plate Number <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" wire:model.defer="plate_number" placeholder="e.g. B 1234 ABC"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 uppercase shadow-sm transition
                                       px-4 py-2.5 text-sm text-slate-900 font-bold placeholder:font-normal placeholder:normal-case placeholder:text-slate-400
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 focus:ring-2 focus:ring-opacity-20
                                       @error('plate_number') border-red-500 focus:border-red-500 focus:ring-red-500 bg-red-50 @enderror">
                        </div>
                        @error('plate_number')
                            <p class="mt-1.5 text-xs font-medium text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Driver Name --}}
                    <div>
                        <label class="block text-sm font-semibold tracking-wide text-slate-700 mb-1.5">
                            Driver Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.defer="driver_name" placeholder="Assigned driver name"
                                class="block w-full rounded-lg border-slate-300 bg-slate-50 pl-10 px-4 py-2.5 text-sm text-slate-900 font-medium shadow-sm transition
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 focus:ring-2 focus:ring-opacity-20 placeholder:text-slate-400 placeholder:font-normal
                                       @error('driver_name') border-red-500 focus:border-red-500 focus:ring-red-500 bg-red-50 @enderror">
                        </div>
                        @error('driver_name')
                            <p class="mt-1.5 text-xs font-medium text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- We can add the rest of the optional fields if needed, but keeping KISS to what was in the original Bootstrap form. --}}

                <div class="mt-8 border-t border-slate-100 pt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('vehicles.index') }}"
                        class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
