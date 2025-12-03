@extends('new.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">Create Master Inventory</h1>
            <p class="mt-1 text-sm text-gray-500">
                Lengkapi data perangkat, hardware, dan software untuk master inventory.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('masterinventory.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            {{-- Master Inventory Card --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Master Inventory
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- IP Address --}}
                        <div>
                            <label for="ip_address" class="block text-sm font-medium text-gray-700">
                                IP Address
                            </label>
                            <input type="text"
                                   name="ip_address"
                                   id="ip_address"
                                   value="{{ old('ip_address') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          px-3 py-2 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('ip_address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">
                                Username
                            </label>
                            <input type="text"
                                   name="username"
                                   id="username"
                                   value="{{ old('username') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          px-3 py-2 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Position Image --}}
                    <div>
                        <label for="position_image" class="block text-sm font-medium text-gray-700">
                            Position Image
                        </label>
                        <input type="file"
                               name="position_image"
                               id="position_image"
                               class="mt-1 block w-full text-sm text-gray-700
                                      file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2
                                      file:text-sm file:font-medium file:text-gray-700
                                      hover:file:bg-gray-200">
                        @error('position_image')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Department --}}
                        <div>
                            <label for="dept" class="block text-sm font-medium text-gray-700">
                                Department
                            </label>
                            <select name="dept"
                                    id="dept"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           px-3 py-2 bg-gray-50
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($depts as $dept)
                                    <option value="{{ $dept->name }}" {{ old('dept') == $dept->name ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dept')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Type --}}
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">
                                Type
                            </label>
                            <select name="type"
                                    id="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           px-3 py-2 bg-gray-50
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="PC" {{ old('type') == 'PC' ? 'selected' : '' }}>PC</option>
                                <option value="Laptop" {{ old('type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                <option value="Others" {{ old('type') == 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Purpose --}}
                        <div class="sm:col-span-1">
                            <label for="purpose" class="block text-sm font-medium text-gray-700">
                                Purpose
                            </label>
                            <input type="text"
                                   name="purpose"
                                   id="purpose"
                                   value="{{ old('purpose') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          px-3 py-2 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('purpose')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Brand --}}
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700">
                                Brand
                            </label>
                            <input type="text"
                                   name="brand"
                                   id="brand"
                                   value="{{ old('brand') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          px-3 py-2 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('brand')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- OS --}}
                        <div>
                            <label for="os" class="block text-sm font-medium text-gray-700">
                                OS
                            </label>
                            <input type="text"
                                   name="os"
                                   id="os"
                                   value="{{ old('os') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          px-3 py-2 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('os')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <input type="text"
                               name="description"
                               id="description"
                               value="{{ old('description') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      px-3 py-2 bg-gray-50
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Hardwares --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">
                            Hardwares
                        </h2>
                        <button type="button"
                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                id="add-hardware">
                            Add Hardware
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm" id="hardwares-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Hardware Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Nomor Inventaris</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Hardware Name</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Pembelian</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="hardwares-container" class="divide-y divide-gray-100">
                                {{-- Dynamic hardware rows will be added here --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Softwares --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">
                            Softwares
                        </h2>
                        <button type="button"
                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                id="add-software">
                            Add Software
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm" id="softwares-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Software Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Software Brand</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Software Name</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">License</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Pembelian</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="softwares-container" class="divide-y divide-gray-100">
                                {{-- Dynamic software rows will be added here --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Create Inventory
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let hardwareIndex = 0;
                let softwareIndex = 0;

                // Normalize data dari PHP (bisa array atau object keyed)
                const hardwareTypesRaw = @json($hardwares);
                const softwareTypesRaw  = @json($softwares);

                const hardwareTypes = Array.isArray(hardwareTypesRaw)
                    ? hardwareTypesRaw
                    : Object.values(hardwareTypesRaw || {});

                const softwareTypes = Array.isArray(softwareTypesRaw)
                    ? softwareTypesRaw
                    : Object.values(softwareTypesRaw || {});

                const getHardwareLabel = (type) =>
                    type.name ?? type.hardware_type ?? type.label ?? '';

                const getSoftwareLabel = (type) =>
                    type.name ?? type.software_type ?? type.label ?? '';

                // Add Hardware row
                const addHardwareBtn = document.getElementById('add-hardware');
                const hardwaresContainer = document.getElementById('hardwares-container');

                addHardwareBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.dataset.index = hardwareIndex;

                    const options = hardwareTypes.map(type =>
                        `<option value="${type.id}">${getHardwareLabel(type)}</option>`
                    ).join('');

                    row.innerHTML = `
                        <td class="px-3 py-2">
                            <select name="hardwares[${hardwareIndex}][type]"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                           px-2 py-1.5 bg-gray-50
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" disabled selected>Select type</option>
                                ${options}
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="hardwares[${hardwareIndex}][brand]"
                                   placeholder="Nomor inventaris"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="hardwares[${hardwareIndex}][hardware_name]"
                                   placeholder="Hardware name"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="hardwares[${hardwareIndex}][remark]"
                                   placeholder="Tanggal pembelian"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <button type="button"
                                    class="remove-hardware inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                Remove
                            </button>
                        </td>
                    `;
                    hardwaresContainer.appendChild(row);
                    hardwareIndex++;
                });

                // Add Software row
                const addSoftwareBtn = document.getElementById('add-software');
                const softwaresContainer = document.getElementById('softwares-container');

                addSoftwareBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.dataset.index = softwareIndex;

                    const options = softwareTypes.map(type =>
                        `<option value="${type.id}">${getSoftwareLabel(type)}</option>`
                    ).join('');

                    row.innerHTML = `
                        <td class="px-3 py-2">
                            <select name="softwares[${softwareIndex}][type]"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                           px-2 py-1.5 bg-gray-50
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" disabled selected>Select type</option>
                                ${options}
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="softwares[${softwareIndex}][software_brand]"
                                   placeholder="Software brand"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="softwares[${softwareIndex}][software_name]"
                                   placeholder="Software name"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="softwares[${softwareIndex}][license]"
                                   placeholder="License"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text"
                                   name="softwares[${softwareIndex}][remark]"
                                   placeholder="Tanggal pembelian"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-xs sm:text-sm
                                          px-2 py-1.5 bg-gray-50
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-3 py-2">
                            <button type="button"
                                    class="remove-software inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                Remove
                            </button>
                        </td>
                    `;
                    softwaresContainer.appendChild(row);
                    softwareIndex++;
                });

                // Remove row handlers (event delegation)
                document.addEventListener('click', function(event) {
                    if (event.target.classList.contains('remove-hardware')) {
                        event.target.closest('tr').remove();
                    }
                    if (event.target.classList.contains('remove-software')) {
                        event.target.closest('tr').remove();
                    }
                });
            });
        </script>
    @endpush
@endsection

