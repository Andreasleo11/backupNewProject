@extends('layouts.app')

@section('content')
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
        @if (auth()->user()->department->name !== 'DIRECTOR')
            <div class="col text-end">
                <a href="{{ route('po.create') }}" class="btn btn-primary">+ Create</a>
            </div>
        @endif
    </div>

    <div class="mt-2">
        <div class="row align-items-center">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    @if (auth()->user()->department->name === 'DIRECTOR')
                        <div class="col-auto me-2">
                            <button id="sign-selected-btn" class="btn btn-outline-success">Sign Selected</button>
                            <button id="reject-selected-btn" class="btn btn-outline-danger">Reject Selected</button>
                        </div>
                    @endif
                    <div class="col-auto me-2">
                        <form id="export-form" method="GET" action="{{ route('po.export') }}">
                            <input type="hidden" name="po_number" id="export-po-number">
                            <input type="hidden" name="vendor_name" id="export-vendor-name">
                            <input type="hidden" name="invoice_date" id="export-invoice-date">
                            <input type="hidden" name="status" id="export-status">
                            <button type="submit" class="btn btn-outline-success">Export to Excel</button>
                        </form>
                    </div>
                    <div class="col-auto">
                        <button id="reset-filters-btn" class="btn btn-secondary">Reset Filters</button>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="col">
                        <input type="text" id="search-input" class="form-control" placeholder="Search...">
                    </div>

                </div>
            </div>
        </div>


        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead>
                    <tr class="text-center">
                        @if (auth()->user()->department->name === 'DIRECTOR')
                            <th><input type="checkbox" id="select-all"></th>
                        @endif
                        <th>
                            PO Number
                            <input type="text" class="form-control column-filter" data-column="0"
                                placeholder="Filter PO Number">
                        </th>
                        <th>
                            Vendor Name
                            <button class="btn btn-link btn-sm sort" data-column="1" data-order="asc">&#9650;</button>
                            <button class="btn btn-link btn-sm sort" data-column="1" data-order="desc">&#9660;</button>
                            <input type="text" class="form-control column-filter" data-column="1"
                                placeholder="Filter Vendor">
                        </th>
                        <th>
                            Invoice Date
                            <button class="btn btn-link btn-sm sort" data-column="2" data-order="asc">&#9650;</button>
                            <button class="btn btn-link btn-sm sort" data-column="2" data-order="desc">&#9660;</button>
                            <input type="date" class="form-control column-filter" data-column="2">
                            <select id="month-filter-invoice-date" class="form-select mt-1" data-column="2">
                                <option value="">All Month</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </th>
                        <th>
                            Invoice Number
                            <button class="btn btn-link btn-sm sort" data-column="3" data-order="asc">&#9650;</button>
                            <button class="btn btn-link btn-sm sort" data-column="3" data-order="desc">&#9660;</button>
                            <input type="text" class="form-control column-filter" data-column="3"
                                placeholder="Filter Invoice Number">
                        </th>
                        <th>
                            Tanggal Pembayaran
                            <button class="btn btn-link btn-sm sort" data-column="4" data-order="asc">&#9650;</button>
                            <button class="btn btn-link btn-sm sort" data-column="4" data-order="desc">&#9660;</button>
                            <input type="date" class="form-control column-filter" data-column="4">
                        </th>
                        <th>Total
                            <button class="btn btn-link btn-sm sort" data-column="5" data-order="asc">&#9650;</button>
                            <button class="btn btn-link btn-sm sort" data-column="5" data-order="desc">&#9660;</button>
                            <input type="text" class="form-control column-filter" data-column="5"
                                placeholder="Filter Total">
                        </th>
                        <th>Upload Date <input type="date" class="form-control column-filter" data-column="6"></th>
                        <th>Uploaded By <input type="text" class="form-control column-filter" data-column="7"
                                placeholder="Filter By"></th>
                        <th>Approved Date <input type="date" class="form-control column-filter" data-column="8"></th>
                        <th>Status <input type="text" class="form-control column-filter" data-column="9"
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
                            <td>{{ $datum->invoice_date ? \Carbon\Carbon::parse($datum->invoice_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $datum->invoice_number }}</td>
                            <td>{{ $datum->tanggal_pembayaran ? \Carbon\Carbon::parse($datum->tanggal_pembayaran)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $datum->currency . ' ' . number_format($datum->total, 1, '.', ',') }}</td>
                            <td>{{ \Carbon\Carbon::parse($datum->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)') }}
                            </td>
                            <td>{{ $datum->user->name }}</td>
                            <td>{{ $datum->approved_date? \Carbon\Carbon::parse($datum->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)'): '-' }}
                            </td>
                            <td>@include('partials.po-status', ['po' => $datum])</td>
                            <td>
                                <a href="{{ route('po.view', $datum->id) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i></i> View
                                </a>
                                @if ($datum->status === 1)
                                    <a href="{{ route('po.edit', $datum->id) }}" class="btn btn-outline-secondary my-1">
                                        <i class="bi bi-pencil"></i></i> Edit
                                    </a>
                                @endif
                                @if (auth()->user()->role->name === 'SUPERADMIN')
                                    @include('partials.delete-confirmation-modal', [
                                        'id' => $datum->id,
                                        'route' => 'po.destroy',
                                        'title' => 'Delete PO confirmation',
                                        'body' => "Are you sure want to delete this PO with id <strong>$datum->id</strong>?",
                                    ])
                                    <button class="btn btn-outline-danger my-1" data-bs-toggle="modal"
                                        data-bs-target="#delete-confirmation-modal-{{ $datum->id }}">
                                        <i class="bi bi-trash"></i>
                                        <span class="d-none d-sm-inline">Delete</span>
                                    </button>
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

        document.getElementById('month-filter-invoice-date').addEventListener('change', function() {
            const selectedMonth = this.value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const dateCell = row.cells[2]; // Assuming 'invoice_date' is in the third column (index 2)
                console.log(dateCell);

                if (dateCell) {
                    const cellText = dateCell.textContent.trim();
                    let isVisible = true;

                    // Check if the date matches the selected month
                    if (selectedMonth) {
                        // Extract month from cell date in 'DD-MM-YYYY' or 'YYYY-MM-DD' format
                        const cellMonth = cellText.includes('-') ? cellText.split('-')[1] : '';

                        if (cellMonth !== selectedMonth) {
                            isVisible = false; // Hide row if month does not match
                        }
                    }

                    // Apply visibility based on the filter result
                    row.style.display = isVisible ? '' : 'none';
                }
            });
        });

        document.getElementById('export-form').addEventListener('submit', function() {
            const poNumber = document.querySelector('[data-column="0"]').value || '';
            const vendorName = document.querySelector('[data-column="1"]').value || '';
            const invoiceDate = document.querySelector('[data-column="2"]').value || '';
            const status = document.querySelector('[data-column="7"]').value || '';

            console.log({
                poNumber,
                vendorName,
                invoiceDate,
                status
            }); // Debugging

            document.getElementById('export-po-number').value = poNumber;
            document.getElementById('export-vendor-name').value = vendorName;
            document.getElementById('export-invoice-date').value = invoiceDate;
            document.getElementById('export-status').value = status;
        });

        document.querySelectorAll('.sort').forEach(button => {
            button.addEventListener('click', function() {
                const column = parseInt(this.dataset.column, 10); // Column index
                const order = this.dataset.order; // Sorting order: 'asc' or 'desc'
                const tbody = document.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !==
                    'none');

                // Function to parse cell value
                const parseValue = value => {
                    // Check if value is a number
                    if (!isNaN(value) && value !== '') {
                        return parseFloat(value);
                    }

                    // Check if value is a valid date
                    const dateValue = Date.parse(value);
                    if (!isNaN(dateValue)) {
                        return dateValue;
                    }

                    // Return as string (case-insensitive)
                    return value ? value.toLowerCase() : '';
                };

                // Sort rows based on parsed values
                rows.sort((a, b) => {
                    const aValue = parseValue(a.cells[column]?.textContent.trim() || '');
                    const bValue = parseValue(b.cells[column]?.textContent.trim() || '');

                    // Compare values for ascending or descending order
                    if (aValue < bValue) {
                        return order === 'asc' ? -1 : 1;
                    }
                    if (aValue > bValue) {
                        return order === 'asc' ? 1 : -1;
                    }
                    return 0;
                });

                // Re-append sorted rows to the table body
                rows.forEach(row => tbody.appendChild(row));
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
