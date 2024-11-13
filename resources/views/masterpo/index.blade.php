@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1>Purchase Orders</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </nav>
            </div>
            <div class="col text-end">
                <a href="{{ route('po.create') }}" class="btn btn-primary">+ Create</a>
            </div>
        </div>

        <div class="mt-2">
            <div class="row">
                <div class="col">
                    @if (auth()->user()->department->name === 'DIRECTOR')
                        <button id="sign-selected-btn" class="btn btn-outline-success ">Sign Selected</button>
                        <button id="reject-selected-btn" class="btn btn-outline-danger ">Reject Selected</button>
                    @endif
                </div>
                <div class="col-md-3">
                    <input type="text" id="search-input" class="form-control" placeholder="Search...">
                </div>
                <div class="col-auto">
                    <button id="reset-filters-btn" class="btn btn-secondary">Reset Filters</button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover">
                    <thead>
                        <tr class="text-center">
                            @if (auth()->user()->department->name === 'DIRECTOR')
                                <th><input type="checkbox" id="select-all"></th>
                            @endif
                            <th>PO Number <input type="text" class="form-control column-filter" data-column="0"
                                    placeholder="Filter PO Number"></th>
                            <th>Vendor Name <input type="text" class="form-control column-filter" data-column="1"
                                    placeholder="Filter Vendor"></th>
                            <th>PO Date <input type="date" class="form-control column-filter" data-column="2"></th>
                            <th>Total <input type="text" class="form-control column-filter" data-column="3"
                                    placeholder="Filter Total"></th>
                            <th>Upload Date <input type="date" class="form-control column-filter" data-column="4"></th>
                            <th>Uploaded By <input type="text" class="form-control column-filter" data-column="5"
                                    placeholder="Filter By"></th>
                            <th>Approved Date <input type="date" class="form-control column-filter" data-column="6"></th>
                            <th>Status <input type="text" class="form-control column-filter" data-column="7"
                                    placeholder="Filter Status"></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $datum)
                            <tr class="text-center">
                                @if (auth()->user()->department->name === 'DIRECTOR')
                                    <td><input type="checkbox" name="po-select[]" value="{{ $datum->id }}"></td>
                                @endif
                                <td>{{ $datum->po_number }}</td>
                                <td>{{ $datum->vendor_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($datum->po_date)->format('d-m-Y') }}</td>
                                <td>{{ $datum->currency . ' ' . number_format($datum->total, 1, '.', ',') }}</td>
                                <td>{{ \Carbon\Carbon::parse($datum->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)') }}
                                </td>
                                <td>{{ $datum->user->name }}</td>
                                <td>{{ $datum->approved_date? \Carbon\Carbon::parse($datum->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)'): '-' }}
                                </td>
                                <td>@include('partials.po-status', ['po' => $datum])</td>
                                <td>
                                    <a href="{{ route('po.view', $datum->id) }}" class="btn btn-outline-primary">View</a>
                                    @if (auth()->user()->role->name === 'SUPERADMIN')
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $datum->id,
                                            'route' => 'po.destroy',
                                            'title' => 'Delete PO confirmation',
                                            'body' => "Are you sure want to delete this PO with id <strong>$datum->id</strong>?",
                                        ])
                                        <button class="btn btn-outline-danger my-1" data-bs-toggle="modal"
                                            data-bs-target="#delete-confirmation-modal-{{ $datum->id }}"><i
                                                class='bx bx-trash-alt'></i> <span
                                                class="d-none d-sm-inline">Delete</span></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="text-center">
                                    No data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
        // General search function
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        const isDirector = document.getElementById('select-all') !== null;
        const offset = isDirector ? 1 : 0;

        // Assign data-column attributes dynamically
        document.querySelectorAll('.column-filter').forEach((filter, index) => {
            filter.setAttribute('data-column', index + offset);
        });

        // Column-specific filter functionality, including improved date filtering and handling empty cells
        document.querySelectorAll('.column-filter').forEach(filter => {
            filter.addEventListener('input', function() {
                const filters = document.querySelectorAll('.column-filter');
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    let isVisible = true; // Assume row is visible initially

                    filters.forEach(filter => {
                        const column = filter.getAttribute('data-column');
                        const value = filter.value.trim();
                        const cell = row.cells[column];

                        if (cell) {
                            const cellText = cell.textContent.trim();

                            // Check if the filter is a date input and has a value
                            if (filter.type === 'date' && value) {
                                const inputDate = value;

                                // Only proceed if cellText is not empty or a placeholder like '-'
                                if (cellText && cellText !== '-') {
                                    // Extract only the date part (YYYY-MM-DD or DD-MM-YYYY) from cell content
                                    let cellDate = cellText.split(' ')[
                                        0]; // Remove any time part if present
                                    if (cellDate.includes('-')) {
                                        const parts = cellDate.split('-');
                                        // Convert 'DD-MM-YYYY' to 'YYYY-MM-DD' if necessary
                                        cellDate = parts[2].length === 4 ?
                                            `${parts[2]}-${parts[1]}-${parts[0]}` :
                                            cellDate;
                                    }

                                    // Log to debug date formats
                                    console.log("Cell Date:", cellDate);
                                    console.log("Input Date:", inputDate);

                                    // Compare the normalized date formats
                                    if (cellDate !== inputDate) {
                                        isVisible =
                                            false; // Hide row if date does not match
                                    }
                                } else {
                                    isVisible =
                                        false; // Hide row if cellText is empty or placeholder
                                }
                            } else if (filter.type !== 'date' && value) {
                                // For non-date columns, check if the cell text includes the filter text (case-insensitive)
                                if (!cellText.toLowerCase().includes(value.toLowerCase())) {
                                    isVisible = false; // Hide row if text does not match
                                }
                            }
                        }
                    });

                    // Apply visibility based on filter results
                    row.style.display = isVisible ? '' : 'none';
                });
            });
        });

        document.getElementById('reset-filters-btn').addEventListener('click', function() {
            // Clear all column filters
            document.querySelectorAll('.column-filter').forEach(filter => {
                filter.value = ''; // Clear the filter input
            });

            // Reset the general search filter
            document.getElementById('search-input').value = '';

            // Show all rows
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = ''; // Set all rows to visible
            });
        });

        // JavaScript for select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('input[name="po-select[]"]').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Sign All functionality
        document.getElementById('sign-selected-btn').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="po-select[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (selectedIds.length > 0) {
                fetch("{{ route('po.signAll') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: selectedIds
                        })
                    }).then(response => response.json())
                    .then(data => alert(data.message));
            } else {
                alert('Please select at least one PO to sign.');
            }
        });

        // Reject All functionality
        document.getElementById('reject-selected-btn').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="po-select[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (selectedIds.length > 0) {
                const reason = prompt("Enter rejection reason");
                if (reason) {
                    fetch("{{ route('po.rejectAll') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: selectedIds,
                                reason: reason
                            })
                        }).then(response => response.json())
                        .then(data => alert(data.message));
                }
            } else {
                alert('Please select at least one PO to reject.');
            }
        });
    </script>
@endsection
