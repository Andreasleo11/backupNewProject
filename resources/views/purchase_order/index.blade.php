@extends('layouts.app')

@section('content')
    <div class="row align-items-center">
        <div class="col">
            <h1>Purchase Orders</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('po.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ol>
            </nav>
        </div>
        @if (auth()->user()->specification->name === 'ADMIN')
            <div class="col text-end">
                {{-- <form action="{{ route('po.create') }}" method="post">
                    @csrf
                    <input type="hidden" name="parentPONumber" value="">
                    <button type="submit" class="btn btn-primary">+ Create</button>
                </form> --}}
                <a href="{{ route('purchase_orders.import.index') }}" class="btn btn-primary">Upload</a>
            </div>
        @endif
    </div>

    <div class="card pe-3 mb-5">
        <div class="table-responsive">
            <div class="card-body">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
    <div class="p-2 fixed-bottom bg-dark shadow text-light text-center" style="opacity: 0.9;">
        Total based on the applied filter : <div id="total-sum" class="fw-bold fs-4">IDR 0.00</div>
    </div>

    <!-- Search Panes CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">

    <!-- Search Panes JS -->
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script type="module">
        $(document).ready(function() {
            const table = $('#purchaseorder-table').DataTable();

            $('#approve-selected-btn').on('click', function() {
                const selectedIds = getSelectedIds();
                const invalidRows = validateRows(selectedIds, ['APPROVED',
                    'REJECTED'
                ]); // Validate against APPROVED or REJECTED rows

                if (invalidRows.length > 0) {
                    // Generate a detailed message
                    const details = invalidRows
                        .map(row => `PO Number: ${row.po_number} (Status: ${row.status})`)
                        .join('\n');
                    alert(
                        `The following records cannot be approved because of their current status:\n\n${details}`
                    );
                    return;
                }

                if (selectedIds.length > 0) {
                    $.ajax({
                        url: '{{ route('purchase_orders.approve_selected') }}',
                        type: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            alert(response.message);
                            table.ajax.reload();
                        },
                        error: function(error) {
                            alert('An error occurred: ' + error);
                        },
                    });
                } else {
                    alert('No rows selected.');
                }
            });

            $('#reject-selected-btn').on('click', function() {
                const selectedIds = getSelectedIds();
                const invalidRows = validateRows(selectedIds, ['REJECTED',
                    'APPROVED'
                ]); // Validate against REJECTED or APPROVED rows

                if (invalidRows.length > 0) {
                    // Generate a detailed message
                    const details = invalidRows
                        .map(row => `PO Number: ${row.po_number} (Status: ${row.status})`)
                        .join('\n');
                    alert(
                        `The following records cannot be rejected because of their current status:\n\n${details}`
                    );
                    return;
                }

                if (selectedIds.length > 0) {
                    const reason = prompt('Please provide a reject reason:');
                    if (!reason) {
                        alert('Reject reason is required.');
                        return;
                    }
                    $.ajax({
                        url: '{{ route('purchase_orders.reject_selected') }}',
                        type: 'POST',
                        data: {
                            ids: selectedIds,
                            reason: reason,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            alert(response.message);
                            table.ajax.reload();
                        },
                        error: function() {
                            alert('An error occurred.');
                        },
                    });
                } else {
                    alert('No rows selected.');
                }
            });

            // Helper to get selected row IDs
            function getSelectedIds() {
                return $('.row-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            function validateRows(selectedIds, invalidStatuses) {
                const statusMap = {
                    1: 'WAITING',
                    2: 'APPROVED',
                    3: 'REJECTED',
                    4: 'CANCELED',
                };

                const invalidRows = [];
                console.log(invalidRows);

                selectedIds.forEach((id) => {
                    const rowData = table.row(`#row-${id}`).data(); // Retrieve row data by ID

                    if (rowData && rowData.status) { // Ensure rowData and status exist
                        const currentStatus = statusMap[rowData.status]; // Map numeric status to label
                        if (invalidStatuses.includes(currentStatus)) {
                            invalidRows.push({
                                id: rowData.id,
                                po_number: rowData.po_number,
                                status: currentStatus,
                            });
                        }
                    } else {
                        console.warn(`Row data or status not found for ID: ${id}`, rowData);
                    }
                });

                return invalidRows;
            }

            // Select All Checkboxes
            $('#select-all').on('click', function() {
                const isChecked = $(this).prop('checked');
                $('.row-checkbox').prop('checked', isChecked);
            });

            // Handle Table Redraw
            table.on('draw', function() {
                const isChecked = $('#select-all').prop('checked');
                $('.row-checkbox').prop('checked', isChecked);
            });

            // Handle Individual Checkbox Clicks
            $(document).on('click', '.row-checkbox', function() {
                const allChecked =
                    $('.row-checkbox:checked').length === $('.row-checkbox').length && $('.row-checkbox')
                    .length > 0;
                $('#select-all').prop('checked', allChecked);
            });

            table.on('draw', function() {
                // Update the total sum dynamically
                const totalSum = table.ajax.json().totalSum || 0;
                $('#total-sum').text(
                    new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(totalSum)
                );
            });
        });
    </script>
@endpush
