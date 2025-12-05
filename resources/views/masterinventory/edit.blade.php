@extends('new.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Master Inventory</h1>
            <p class="mt-1 text-sm text-gray-500">
                Update data perangkat, hardware, dan software untuk master inventory ini.
            </p>
        </div>

        {{-- Success message --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Global validation errors (optional) --}}
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('masterinventory.update', $data->id) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

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
                                   value="{{ old('ip_address', $data->ip_address) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                                   value="{{ old('username', $data->username) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($depts as $dept)
                                    <option value="{{ $dept->name }}"
                                            @selected(old('dept', $data->dept) == $dept->name)>
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
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="PC" @selected(old('type', $data->type) == 'PC')>PC</option>
                                <option value="Laptop" @selected(old('type', $data->type) == 'Laptop')>Laptop</option>
                                <option value="Others" @selected(old('type', $data->type) == 'Others')>Others</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Purpose --}}
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700">
                                Purpose
                            </label>
                            <input type="text"
                                   name="purpose"
                                   id="purpose"
                                   value="{{ old('purpose', $data->purpose) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                                   value="{{ old('brand', $data->brand) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                                   value="{{ old('os', $data->os) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                               value="{{ old('description', $data->description) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
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
                                id="add-hardware"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                                @foreach ($data->hardwares as $index => $hardware)
                                    <tr data-index="{{ $index }}">
                                        <td class="px-3 py-2">
                                            <select name="hardwares[{{ $index }}][type]"
                                                    class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach ($hardwareTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                            @selected($hardware->hardware_id == $type->id)>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="hardwares[{{ $index }}][brand]"
                                                   value="{{ $hardware->brand }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="hardwares[{{ $index }}][hardware_name]"
                                                   value="{{ $hardware->hardware_name }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="hardwares[{{ $index }}][remark]"
                                                   value="{{ $hardware->remark }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <button type="button"
                                                    class="remove-hardware inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
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
                                id="add-software"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                                @foreach ($data->softwares as $index => $software)
                                    <tr data-index="{{ $index }}">
                                        <td class="px-3 py-2">
                                            <select name="softwares[{{ $index }}][type]"
                                                    class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach ($softwareTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                            @selected($software->software_id == $type->id)>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="softwares[{{ $index }}][software_brand]"
                                                   value="{{ $software->software_brand }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="softwares[{{ $index }}][software_name]"
                                                   value="{{ $software->software_name }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="softwares[{{ $index }}][license]"
                                                   value="{{ $software->license }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="softwares[{{ $index }}][remark]"
                                                   value="{{ $software->remark }}"
                                                   required
                                                   class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <button type="button"
                                                    class="remove-software inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Update Inventory
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let hardwareIndex = {{ count($data->hardwares) }};
            let softwareIndex = {{ count($data->softwares) }};
            const hardwareTypes = @json($hardwareTypes);
            const softwareTypes = @json($softwareTypes);

            // Add Hardware row
            const addHardwareBtn = document.getElementById('add-hardware');
            const hardwaresContainer = document.getElementById('hardwares-container');

            addHardwareBtn.addEventListener('click', function() {
                const row = document.createElement('tr');
                row.dataset.index = hardwareIndex;

                const options = hardwareTypes.map(type =>
                    `<option value="${type.id}">${type.name}</option>`
                ).join('');

                row.innerHTML = `
                    <td class="px-3 py-2">
                        <select name="hardwares[${hardwareIndex}][type]"
                                class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            ${options}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="hardwares[${hardwareIndex}][brand]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="hardwares[${hardwareIndex}][hardware_name]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="hardwares[${hardwareIndex}][remark]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
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
                    `<option value="${type.id}">${type.name}</option>`
                ).join('');

                row.innerHTML = `
                    <td class="px-3 py-2">
                        <select name="softwares[${softwareIndex}][type]"
                                class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            ${options}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="softwares[${softwareIndex}][software_brand]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="softwares[${softwareIndex}][software_name]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="softwares[${softwareIndex}][license]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text"
                               name="softwares[${softwareIndex}][remark]"
                               required
                               class="block w-full rounded-md border-gray-300 bg-slate-50 px-2 py-1.5 text-xs sm:text-sm shadow-sm
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
                    event.target.closest('tr')?.remove();
                }
                if (event.target.classList.contains('remove-software')) {
                    event.target.closest('tr')?.remove();
                }
            });
        });
    </script>
@endpush
