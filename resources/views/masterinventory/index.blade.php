@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
        $totalItems = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->total() : $datas->count();
        $currentPage = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->currentPage() : 1;
        $perPage = $datas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $datas->perPage() : $totalItems;
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('masterinventory.index') }}">Master Inventory</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col">
                <h2 class="fw-bold">Master Inventory</h2>
            </div>
            <div class="col text-end">
                @php
                    $showCreateButton =
                        !$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'MANAGEMENT';
                @endphp
                @if ($showCreateButton)
                    <a href="{{ route('masterinventory.createpage') }}" class="btn btn-primary">New Inventory</a>
                @endif

                <a href="{{ route('export.inventory') }}" class="btn btn-success">Export to Excel</a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
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

                <div class="row justify-content-between align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center">
                        <label for="itemsPerPage" class="form-label me-4">Per page</label>
                        <select id="itemsPerPage" class="form-select">
                            <option value="10" {{ request()->get('itemsPerPage') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request()->get('itemsPerPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request()->get('itemsPerPage') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request()->get('itemsPerPage') == 100 ? 'selected' : '' }}>100
                            </option>
                            <option value="all" {{ request()->get('itemsPerPage') == 'all' ? 'selected' : '' }}>All
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <input type="text" id="filter-all" class="form-control" placeholder="Search..">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="sortable">No</th>
                                <th class="sortable" data-column="ip_address">No Asset</th>
                                <th class="sortable" data-column="username">Username</th>
                                <th class="sortable" data-column="dept">Department</th>
                                <th class="sortable" data-column="type">Type</th>
                                <th class="sortable" data-column="purpose">Tanggal Pembelian</th>
                                <th class="sortable" data-column="brand">Status</th>
                                <th class="sortable" data-column="os">OS</th>
                                <th class="sortable" data-column="description">Description</th>
                                <th colspan="2" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table">
                            @forelse ($datas as $data)
                                <tr>
                                    <td>{{ ($currentPage - 1) * $perPage + $loop->iteration }}</td>
                                    <td>{{ $data->ip_address }}</td>
                                    <td>{{ $data->username }}</td>
                                    <td>{{ $data->dept }}</td>
                                    <td>{{ $data->type }}</td>
                                    <td>{{ $data->purpose }}</td>
                                    <td>{{ $data->brand }}</td>
                                    <td>{{ $data->os }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>
                                        <a href="{{ route('masterinventory.detail', $data->id) }}"
                                            class="btn btn-secondary my-1">Detail</a>
                                        <a href="{{ route('maintenance.inventory.create', ['id' => $data->id]) }}"
                                            class="btn btn-outline-success my-1">Create Maintenance</a>
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $data->id,
                                            'route' => 'masterinventory.delete',
                                            'title' => 'Delete Master Inventory confirmation',
                                            'body' => "Are you sure want to delete this data with id <strong>$data->id</strong>?",
                                        ])

                                        <button class="btn btn-danger my-1" data-bs-toggle="modal"
                                            data-bs-target="#delete-confirmation-modal-{{ $data->id }}"><i
                                                class='bx bx-trash-alt'></i> <span
                                                class="d-none d-sm-inline">Delete</span></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($datas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-end mt-3">
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
