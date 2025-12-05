@extends('new.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-slate-900">
                Form Request Trial
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Lengkapi data berikut untuk membuat request trial baru.
            </p>
        </div>

        <form method="POST" action="{{ route('pe.input') }}" class="space-y-8">
            @csrf

            {{-- Bagian 1: Info Part --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 md:p-5">
                <h2 class="text-sm font-semibold text-slate-800 mb-4">
                    Informasi Part
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Customer --}}
                    <div>
                        <label for="customer" class="block text-sm font-medium text-slate-700">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="customer" name="customer" required
                               value="{{ old('customer') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('customer')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Part Name --}}
                    <div>
                        <label for="part_name" class="block text-sm font-medium text-slate-700">
                            Part Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="part_name" name="part_name" required
                               value="{{ old('part_name') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('part_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Part No --}}
                    <div>
                        <label for="part_no" class="block text-sm font-medium text-slate-700">
                            Part No <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="part_no" name="part_no" required
                               value="{{ old('part_no') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('part_no')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Model --}}
                    <div>
                        <label for="model" class="block text-sm font-medium text-slate-700">
                            Model <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="model" name="model" required
                               value="{{ old('model') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('model')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cavity --}}
                    <div>
                        <label for="cavity" class="block text-sm font-medium text-slate-700">
                            Cavity <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="cavity" name="cavity" required
                               value="{{ old('cavity') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('cavity')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status Trial --}}
                    <div>
                        <label for="status_trial" class="block text-sm font-medium text-slate-700">
                            Status Trial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="status_trial" name="status_trial" required
                               value="{{ old('status_trial') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('status_trial')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Bagian 2: Material --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 md:p-5">
                <h2 class="text-sm font-semibold text-slate-800 mb-4">
                    Informasi Material
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Material --}}
                    <div>
                        <label for="material" class="block text-sm font-medium text-slate-700">
                            Material <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="material" name="material" required
                               value="{{ old('material') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('material')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status Material --}}
                    <div>
                        <label for="status_material" class="block text-sm font-medium text-slate-700">
                            Status Material <span class="text-red-500">*</span>
                        </label>
                        <select id="status_material" name="status_material" required
                                class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            <option value="Virgin" {{ old('status_material') === 'Virgin' ? 'selected' : '' }}>Virgin</option>
                            <option value="Recycle" {{ old('status_material') === 'Recycle' ? 'selected' : '' }}>Recycle</option>
                            <option value="Mixing" {{ old('status_material') === 'Mixing' ? 'selected' : '' }}>Mixing</option>
                        </select>
                        @error('status_material')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Color --}}
                    <div>
                        <label for="color" class="block text-sm font-medium text-slate-700">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="color" name="color" required
                               value="{{ old('color') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('color')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Material Consump --}}
                    <div>
                        <label for="material_consump" class="block text-sm font-medium text-slate-700">
                            Material Consump <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="material_consump" name="material_consump" required
                               value="{{ old('material_consump') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('material_consump')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dimension Tooling --}}
                    <div>
                        <label for="dimension_tooling" class="block text-sm font-medium text-slate-700">
                            Dimension Tooling
                        </label>
                        <input type="text" id="dimension_tooling" name="dimension_tooling"
                               value="{{ old('dimension_tooling') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('dimension_tooling')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Member Trial --}}
                    <div>
                        <label for="member_trial" class="block text-sm font-medium text-slate-700">
                            Member Trial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="member_trial" name="member_trial" required
                               value="{{ old('member_trial') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('member_trial')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Bagian 3: Jadwal & Waktu --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 md:p-5">
                <h2 class="text-sm font-semibold text-slate-800 mb-4">
                    Jadwal & Waktu Trial
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Request Trial --}}
                    <div>
                        <label for="request_trial" class="block text-sm font-medium text-slate-700">
                            Request Trial <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="request_trial" name="request_trial" required
                               value="{{ old('request_trial') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('request_trial')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Trial Date --}}
                    <div>
                        <label for="trial_date" class="block text-sm font-medium text-slate-700">
                            Trial Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="trial_date" name="trial_date" required
                               value="{{ old('trial_date') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('trial_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Time Set Up Tooling --}}
                    <div>
                        <label for="time_set_up_tooling" class="block text-sm font-medium text-slate-700">
                            Time Set Up Tooling
                        </label>
                        <input type="text" id="time_set_up_tooling" name="time_set_up_tooling"
                               value="{{ old('time_set_up_tooling') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
                               placeholder="contoh: 08:00">
                        @error('time_set_up_tooling')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Time Setting Tooling --}}
                    <div>
                        <label for="time_setting_tooling" class="block text-sm font-medium text-slate-700">
                            Time Setting Tooling
                        </label>
                        <input type="text" id="time_setting_tooling" name="time_setting_tooling"
                               value="{{ old('time_setting_tooling') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
                               placeholder="contoh: 09:30">
                        @error('time_setting_tooling')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Time Finish Inject --}}
                    <div>
                        <label for="time_finish_inject" class="block text-sm font-medium text-slate-700">
                            Time Finish Inject
                        </label>
                        <input type="text" id="time_finish_inject" name="time_finish_inject"
                               value="{{ old('time_finish_inject') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
                               placeholder="contoh: 14:00">
                        @error('time_finish_inject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Time Set Down Tooling --}}
                    <div>
                        <label for="time_set_down_tooling" class="block text-sm font-medium text-slate-700">
                            Time Set Down Tooling
                        </label>
                        <input type="text" id="time_set_down_tooling" name="time_set_down_tooling"
                               value="{{ old('time_set_down_tooling') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
                               placeholder="contoh: 15:00">
                        @error('time_set_down_tooling')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Trial Cost --}}
                    <div>
                        <label for="trial_cost" class="block text-sm font-medium text-slate-700">
                            Trial Cost
                        </label>
                        <input type="text" id="trial_cost" name="trial_cost"
                               value="{{ old('trial_cost') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900"
                               placeholder="contoh: 1.500.000">
                        @error('trial_cost')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Qty --}}
                    <div>
                        <label for="qty" class="block text-sm font-medium text-slate-700">
                            Qty <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="qty" name="qty" required
                               value="{{ old('qty') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('qty')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Adjuster --}}
                    <div>
                        <label for="adjuster" class="block text-sm font-medium text-slate-700">
                            Adjuster
                        </label>
                        <input type="text" id="adjuster" name="adjuster"
                               value="{{ old('adjuster') }}"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                      text-slate-900 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        @error('adjuster')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold
                               text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                               focus:ring-slate-500 focus:ring-offset-1">
                    Submit
                </button>
            </div>
        </form>
    </div>
@endsection
