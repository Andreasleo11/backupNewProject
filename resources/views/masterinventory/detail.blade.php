@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
         x-data="{ tab: 'hardware', repairModal: false }">
        {{-- Header + actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Master Inventory Detail</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan aset, hardware, software, dan riwayat perbaikan & maintenance.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('masterinventory.editpage', $data->id) }}"
                   class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1">
                    Edit
                </a>
                <a href="{{ route('masterinventory.index') }}"
                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    Back to list
                </a>
            </div>
        </div>

        {{-- Master inventory summary --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200 mb-6">
            <div class="px-4 py-4 sm:px-6 sm:py-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">
                    Master Inventory Details
                </h2>

                <div class="flex flex-col md:flex-row gap-6">
                    {{-- Text info --}}
                    <div class="flex-1">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                            <div>
                                <dt class="font-medium text-gray-600">No Asset</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->ip_address }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Username</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->username }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Department</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->dept }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Type</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->type }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Tanggal Pembelian</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->purpose }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">Status</dt>
                                <dd class="mt-0.5">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                                        {{ $data->brand }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-600">OS</dt>
                                <dd class="mt-0.5 text-gray-900">{{ $data->os }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="font-medium text-gray-600">Description</dt>
                                <dd class="mt-0.5 text-gray-900">
                                    {{ $data->description }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Position image --}}
                    <div class="md:w-64">
                        <div class="text-sm font-medium text-gray-600 mb-1">
                            Position Image
                        </div>
                        <div class="border border-dashed border-gray-200 rounded-md p-2 flex items-center justify-center bg-slate-50">
                            @if ($data->position_image)
                                <a href="{{ asset('storage/' . $data->position_image) }}"
                                   data-fancybox="gallery"
                                   data-caption="Position Image"
                                   class="block">
                                    <img src="{{ asset('storage/' . $data->position_image) }}"
                                         alt="Position Image"
                                         class="max-h-40 w-auto rounded-md object-contain">
                                </a>
                            @else
                                <p class="text-xs text-gray-400 text-center py-4">
                                    No image available
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div>
            <div class="border-b border-gray-200 mb-3">
                <nav class="-mb-px flex flex-wrap gap-2" aria-label="Tabs">
                    <button type="button"
                            @click="tab = 'hardware'"
                            :class="tab === 'hardware'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">
                        Hardware
                    </button>
                    <button type="button"
                            @click="tab = 'software'"
                            :class="tab === 'software'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">
                        Software
                    </button>
                    <button type="button"
                            @click="tab = 'repair'"
                            :class="tab === 'repair'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">
                        Repair History
                    </button>
                    <button type="button"
                            @click="tab = 'maint'"
                            :class="tab === 'maint'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">
                        Maintenance History
                    </button>
                </nav>
            </div>

            {{-- Hardware Tab --}}
            <div x-show="tab === 'hardware'" x-cloak>
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                    <div class="px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold text-gray-900">Hardware Details</h2>
                        </div>

                        @if ($data->hardwares->isEmpty())
                            <p class="text-sm text-gray-500">No hardware details available.</p>
                        @else
                            <div class="overflow-x-auto mt-2">
                                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Nomor Inventaris</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Hardware Name</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Pembelian</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Last Update</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($data->hardwares as $hardware)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-900">
                                                    {{ $hardware->hardwareType->name ?? 'Unknown Type' }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $hardware->brand }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $hardware->hardware_name }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $hardware->remark }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-500">
                                                    {{ $hardware->updated_at->format('Y-m-d') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <form action="{{ route('generate.hardware.qrcode', $hardware->id) }}"
                                                          method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                            Generate QR Code
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Software Tab --}}
            <div x-show="tab === 'software'" x-cloak>
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                    <div class="px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold text-gray-900">Software Details</h2>
                        </div>

                        @if ($data->softwares->isEmpty())
                            <p class="text-sm text-gray-500">No software details available.</p>
                        @else
                            <div class="overflow-x-auto mt-2">
                                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Software Brand</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Software Name</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">License</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Pembelian</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Last Update</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($data->softwares as $software)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-900">
                                                    {{ $software->softwareType->name ?? 'Unknown Type' }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $software->software_brand }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $software->software_name }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $software->license }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">
                                                    {{ $software->remark }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-500">
                                                    {{ $software->updated_at->format('Y-m-d') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Repair History Tab --}}
            <div x-show="tab === 'repair'" x-cloak>
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                    <div class="px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold text-gray-900">Repair History</h2>
                            <button type="button"
                                    @click="repairModal = true"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                Create Repair History
                            </button>
                        </div>

                        <div class="overflow-x-auto mt-2">
                            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Master ID</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Request Name</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Action</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Old Part</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Item Type</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Item Brand</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Item Name</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Action Date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Pembelian</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($processedHistories as $history)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->master_id }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->request_name }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->action }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->type }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->old_part }}</td>
                                            <td class="px-3 py-2 text-gray-700">
                                                @if ($history->type === 'hardware')
                                                    {{ $history->hardwareType->name ?? 'N/A' }}
                                                @elseif ($history->type === 'software')
                                                    {{ $history->softwareType->name ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->item_brand }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->item_name }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->action_date }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->remark }}</td>
                                            <td class="px-3 py-2">
                                                @if ($history->action_date)
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                        Finished
                                                    </span>
                                                @else
                                                    <form action="{{ route('inventory.update', $history->id) }}"
                                                          method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit"
                                                                class="inline-flex items-center rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                                            Update / Sync
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11"
                                                class="px-3 py-4 text-center text-xs text-gray-500">
                                                No data available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Maintenance History Tab --}}
            <div x-show="tab === 'maint'" x-cloak>
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                    <div class="px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold text-gray-900">Maintenance History</h2>
                        </div>

                        <div class="overflow-x-auto mt-2">
                            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">ID</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Nomor Dokumen</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Username</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Periode</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Created Date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Revision Date</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($inventoryHistories as $history)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->id }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->no_dokumen }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->master->username }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->periode_caturwulan }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->created_at }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $history->revision_date }}</td>
                                            <td class="px-3 py-2">
                                                <a href="{{ route('maintenance.inventory.show', $history->id) }}"
                                                   class="inline-flex items-center rounded-md bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-600">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7"
                                                class="px-3 py-4 text-center text-xs text-gray-500">
                                                No data available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Repair History Modal (Tailwind) --}}
        <div x-show="repairModal"
             x-cloak
             class="fixed inset-0 z-40 flex items-center justify-center px-4 py-6 bg-slate-900/40">
            <div class="relative w-full max-w-lg rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 sm:px-6">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Create Repair History
                    </h3>
                    <button type="button"
                            @click="repairModal = false"
                            class="inline-flex items-center rounded-md p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 
                                  1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 
                                  1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 
                                  10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-4 sm:px-6 sm:py-5 max-h-[70vh] overflow-y-auto">
                    <form action="{{ route('repair.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="master_id" value="{{ $data->id }}">

                        {{-- Request Name --}}
                        <div>
                            <label for="requestName" class="block text-sm font-medium text-gray-700">
                                Request Name
                            </label>
                            <input type="text"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                   id="requestName"
                                   name="requestName"
                                   required>
                        </div>

                        {{-- Type --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">
                                    Type
                                </label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                               focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                        id="type"
                                        name="type">
                                    <option value="">Select Type</option>
                                    <option value="hardware">Hardware</option>
                                    <option value="software">Software</option>
                                </select>
                            </div>

                            {{-- Action --}}
                            <div>
                                <label for="action" class="block text-sm font-medium text-gray-700">
                                    Action
                                </label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                               focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                        id="action"
                                        name="action"
                                        required>
                                    <option value="">Select Action</option>
                                    <option value="replacement">Replacement</option>
                                    <option value="installation">Installation</option>
                                </select>
                            </div>
                        </div>

                        {{-- Replacement Fields --}}
                        <div id="replacementFields" style="display: none;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="oldPart" class="block text-sm font-medium text-gray-700">
                                        Old Part
                                    </label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                   focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                            id="oldPart"
                                            name="oldPart">
                                        <option value="">Select Item</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="itemType" class="block text-sm font-medium text-gray-700">
                                        Item Type
                                    </label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                   focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                            id="itemType"
                                            name="itemType">
                                        <option value="">Select Item Type</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label for="itemBrand" class="block text-sm font-medium text-gray-700">
                                        Item Brand
                                    </label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                  focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                           id="itemBrand"
                                           name="itemBrand">
                                </div>
                                <div>
                                    <label for="itemName" class="block text-sm font-medium text-gray-700">
                                        Item Name
                                    </label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                  focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                           id="itemName"
                                           name="itemName">
                                </div>
                            </div>
                        </div>

                        {{-- Installation Fields --}}
                        <div id="installationFields" style="display: none;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="itemTypeInstallation" class="block text-sm font-medium text-gray-700">
                                        Item Type
                                    </label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                   focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                            id="itemTypeInstallation"
                                            name="itemTypeInstallation">
                                        <option value="">Select Item Type</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="itemBrandInstallation" class="block text-sm font-medium text-gray-700">
                                        Item Brand
                                    </label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                                  focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                           id="itemBrandInstallation"
                                           name="itemBrandInstallation">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="itemNameInstallation" class="block text-sm font-medium text-gray-700">
                                    Item Name
                                </label>
                                <input type="text"
                                       class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                              focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                       id="itemNameInstallation"
                                       name="itemNameInstallation">
                            </div>
                        </div>

                        {{-- Remark --}}
                        <div>
                            <label for="remark" class="block text-sm font-medium text-gray-700">
                                Tanggal Pembelian (YYYY-MM-DD)
                            </label>
                            <input type="text"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-slate-50 px-3 py-2 text-sm shadow-sm
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                   id="remark"
                                   name="remark">
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button"
                                    @click="repairModal = false"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                Save Repair History
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const actionSelect = document.getElementById('action');
            const replacementFields = document.getElementById('replacementFields');
            const installationFields = document.getElementById('installationFields');
            const oldPartSelect = document.getElementById('oldPart');
            const itemTypeSelect = document.getElementById('itemType');
            const itemTypeInstallationSelect = document.getElementById('itemTypeInstallation');
            const masterIdInput = document.querySelector('input[name="master_id"]');
            const masterId = masterIdInput ? masterIdInput.value : null;

            if (!actionSelect || !masterId) return;

            actionSelect.addEventListener('change', function() {
                const action = this.value;

                if (action === 'replacement') {
                    replacementFields.style.display = 'block';
                    installationFields.style.display = 'none';
                    loadOldParts();
                    loadItemTypes();
                } else if (action === 'installation') {
                    replacementFields.style.display = 'none';
                    installationFields.style.display = 'block';
                    loadItemTypesInstallation();
                } else {
                    replacementFields.style.display = 'none';
                    installationFields.style.display = 'none';
                }
            });

            function loadOldParts() {
                const type = document.getElementById('type').value;
                if (type && masterId) {
                    fetch(`/items/available?type=${encodeURIComponent(type)}&master_id=${encodeURIComponent(masterId)}`)
                        .then(response => response.json())
                        .then(data => {
                            oldPartSelect.innerHTML =
                                `<option value="">Select Item</option>` +
                                data.map(item => `<option value="${item.name}">${item.name}</option>`).join('');
                        })
                        .catch(error => console.error('Error fetching old parts:', error));
                }
            }

            function loadItemTypes() {
                const type = document.getElementById('type').value;
                if (type) {
                    fetch(`/items/types/${encodeURIComponent(type)}`)
                        .then(response => response.json())
                        .then(data => {
                            itemTypeSelect.innerHTML =
                                `<option value="">Select Item Type</option>` +
                                data.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
                        })
                        .catch(error => console.error('Error fetching item types:', error));
                }
            }

            function loadItemTypesInstallation() {
                const type = document.getElementById('type').value;
                if (type) {
                    fetch(`/items/types/${encodeURIComponent(type)}`)
                        .then(response => response.json())
                        .then(data => {
                            itemTypeInstallationSelect.innerHTML =
                                `<option value="">Select Item Type</option>` +
                                data.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
                        })
                        .catch(error => console.error('Error fetching item types for installation:', error));
                }
            }

            document.getElementById('type').addEventListener('change', function() {
                const action = actionSelect.value;
                if (action === 'replacement') {
                    loadOldParts();
                    loadItemTypes();
                } else if (action === 'installation') {
                    loadItemTypesInstallation();
                }
            });

            // Fancybox (kalau sudah include di layout)
            if (window.Fancybox) {
                Fancybox.bind("[data-fancybox]", {});
            }
        });
    </script>
@endpush
