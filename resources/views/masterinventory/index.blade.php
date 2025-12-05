@extends('new.layouts.app')

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
        $totalItems = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->total() : $datas->count();
        $currentPage = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->currentPage() : 1;
        $perPage = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->perPage() : $totalItems;

        $showCreateButton =
            !$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'MANAGEMENT';
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="flex items-center gap-1 text-sm text-gray-500">
                <li>
                    <a href="{{ route('masterinventory.index') }}"
                       class="font-medium text-gray-600 hover:text-indigo-600">
                        Master Inventory
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="font-medium text-gray-900">
                    List
                </li>
            </ol>
        </nav>

        {{-- Header + actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Master Inventory
            </h2>

            <div class="flex flex-wrap gap-2 justify-start sm:justify-end">
                @if ($showCreateButton)
                    <a href="{{ route('masterinventory.createpage') }}"
                       class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        New Inventory
                    </a>
                @endif

                <a href="{{ route('export.inventory') }}"
                   class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                    Export to Excel
                </a>
            </div>
        </div>

        {{-- Main card --}}
        <div class="mt-4 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="p-4 sm:p-5">
                {{-- Filter partial (masih pakai tampilan yang lama, nanti bisa kita Tailwind-kan juga) --}}
                @include('components.filter', [
                    'filtersApplied' =>
                        request()->has('filterColumn') ||
                        request()->has('filterAction') ||
                        request()->has('filterValue'),
                    'filterRoute' => route('masterinventory.index'),
                    'filterColumns' => [
                        'ip_address' => 'IP Address',
                        'username' => 'Username',
                        'dept' => 'Department',
                        'type' => 'Type',
                        'purpose' => 'Purpose',
                        'brand' => 'Brand',
                        'os' => 'OS',
                        'description' => 'Description',
                    ],
                ])

                {{-- Top controls: per page + search --}}
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <label for="itemsPerPage" class="text-sm text-gray-700">
                            Per page
                        </label>
                        <select id="itemsPerPage"
                                class="block w-28 rounded-md border-gray-300 shadow-sm text-sm
                                       px-3 py-2
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="10" {{ request()->get('itemsPerPage') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request()->get('itemsPerPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request()->get('itemsPerPage') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request()->get('itemsPerPage') == 100 ? 'selected' : '' }}>100</option>
                            <option value="all" {{ request()->get('itemsPerPage') == 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-64">
                        <input type="text" id="filter-all"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      px-3 py-2
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Search..">
                    </div>
                </div>

                {{-- Table --}}
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    No
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="ip_address">
                                    No Asset
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="username">
                                    Username
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="dept">
                                    Department
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="type">
                                    Type
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="purpose">
                                    Tanggal Pembelian
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="brand">
                                    Status
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="os">
                                    OS
                                </th>
                                <th class="sortable px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                                    data-column="description">
                                    Description
                                </th>
                                <th colspan="2"
                                    class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table" class="divide-y divide-gray-100 bg-white">
                            @forelse ($datas as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                        {{ ($currentPage - 1) * $perPage + $loop->iteration }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->ip_address }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->username }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->dept }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->type }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->purpose }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->brand }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->os }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">
                                        {{ $data->description }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <a href="{{ route('masterinventory.detail', $data->id) }}"
                                               class="inline-flex items-center justify-center rounded-md bg-slate-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-1 focus:ring-slate-500">
                                                Detail
                                            </a>

                                            <a href="{{ route('maintenance.inventory.create', ['id' => $data->id]) }}"
                                               class="inline-flex items-center justify-center rounded-md border border-emerald-500 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                Create Maintenance
                                            </a>

                                            @include('partials.delete-confirmation-modal', [
                                                'id' => $data->id,
                                                'route' => 'masterinventory.delete',
                                                'title' => 'Delete Master Inventory confirmation',
                                                'body' => "Are you sure want to delete this data with id <strong>$data->id</strong>?",
                                            ])

                                            <button
                                                class="inline-flex items-center justify-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                data-bs-toggle="modal"
                                                data-bs-target="#delete-confirmation-modal-{{ $data->id }}">
                                                <i class="bx bx-trash-alt mr-1"></i>
                                                <span class="hidden sm:inline">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-3 py-6 text-center text-sm text-gray-500">
                                        No records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($datas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 flex justify-end">
                {{ $datas->appends([
                        'itemsPerPage' => request()->get('itemsPerPage'),
                        'filterColumn' => request()->get('filterColumn'),
                        'filterAction' => request()->get('filterAction'),
                        'filterValue' => request()->get('filterValue'),
                    ])->links() }}
            </div>
        @endif
    </div>

    <script type="module" src="{{ asset('js/filter.js') }}"></script>
    <script type="module">
        $(document).ready(function() {
            // Filter input
            $('#filter-all').on('keyup', function() {
                const query = $(this).val();
                const rows = $('#inventory-table tr');
                rows.each(function() {
                    const row = $(this);
                    const text = row.text().toLowerCase();
                    row.toggle(text.includes(query.toLowerCase()));
                });
            });
        });
    </script>
@endsection

