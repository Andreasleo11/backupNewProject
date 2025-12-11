@extends('new.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Page header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Purchase Orders
                </h1>

                {{-- Breadcrumb --}}
                <nav class="mt-2" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1 text-sm text-slate-500">
                        <li>
                            <a href="{{ route('po.dashboard') }}" class="hover:text-slate-700">
                                Dashboard
                            </a>
                        </li>
                        <li class="px-1 text-slate-400">/</li>
                        <li class="text-slate-700 font-medium">
                            List
                        </li>
                    </ol>
                </nav>
            </div>

            @if (auth()->user()->department->name !== 'MANAGEMENT')
                <div class="flex justify-end">
                    <form action="{{ route('po.create') }}" method="post">
                        @csrf
                        <input type="hidden" name="parentPONumber" value="">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-indigo-500/80 text-xs font-bold">
                                +
                            </span>
                            <span>New Purchase Order</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Table shell --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Purchase Order List
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Use filters and search panes to narrow down by status, vendor, or period.
                    </p>
                </div>

                {{-- Space for future toolbar (export, saved filters, etc.) --}}
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span>Filtered total is shown at the bottom of the page.</span>
                </div>
            </div>

            <div class="px-4 pb-4 pt-3 overflow-x-auto">
                {{-- Yajra DataTable --}}
                {{ $dataTable->table(['class' => 'w-full text-sm text-left text-slate-700'], true) }}
            </div>
        </div>

        {{-- Sticky bottom total bar --}}
        <div class="fixed bottom-0 inset-x-0 z-30 bg-slate-900/90 text-slate-50 shadow-lg border-t border-slate-800/80">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="text-xs sm:text-sm text-slate-200">
                    Total amount for current filters
                </div>
                <div id="total-sum" class="text-lg sm:text-xl font-semibold tracking-tight">
                    IDR 0.00
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- DataTables + SearchPanes assets --}}
@pushOnce('head')
    {{-- Search Panes CSS (kept for DataTables plugin) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">
@endPushOnce

@pushOnce('scripts')
    {{-- Yajra DataTables initialization --}}
    {{ $dataTable->scripts() }}

    {{-- Search Panes JS --}}
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>

    <script type="module">
        $(document).ready(function() {
            const table = $('#purchaseorder-table').DataTable();

            // ===== Bulk Approve =====
            $('#approve-selected-btn').on('click', function() {
                const selectedIds = getSelectedIds();
                const invalidRows = validateRows(selectedIds, ['APPROVED', 'REJECTED']);

                if (invalidRows.length > 0) {
                    const details = invalidRows
                        .map(row => `PO Number: ${row.po_number} (Status: ${row.status})`)
                        .join('\n');

                    alert(
                        'Some records cannot be approved because of their current status:\n\n' +
                        details
                    );
                    return;
                }

                if (!selectedIds.length) {
                    alert('No rows selected.');
                    return;
                }

                if (!confirm('Approve all selected purchase orders?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route('purchase_orders.approve_selected') }}',
                    type: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        alert(response.message || 'Selected purchase orders approved.');
                        table.ajax.reload();
                    },
                    error: function(error) {
                        alert('An error occurred: ' + (error.responseJSON?.message ||
                            'Unknown error'));
                    },
                });
            });

            // ===== Bulk Reject =====
            $('#reject-selected-btn').on('click', function() {
                const selectedIds = getSelectedIds();
                const invalidRows = validateRows(selectedIds, ['REJECTED', 'APPROVED']);

                if (invalidRows.length > 0) {
                    const details = invalidRows
                        .map(row => `PO Number: ${row.po_number} (Status: ${row.status})`)
                        .join('\n');

                    alert(
                        'Some records cannot be rejected because of their current status:\n\n' +
                        details
                    );
                    return;
                }

                if (!selectedIds.length) {
                    alert('No rows selected.');
                    return;
                }

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
                        alert(response.message || 'Selected purchase orders rejected.');
                        table.ajax.reload();
                    },
                    error: function() {
                        alert(
                        'An error occurred while rejecting the selected purchase orders.');
                    },
                });
            });

            // ===== Helpers =====
            function getSelectedIds() {
                return $('.row-checkbox:checked')
                    .map(function() {
                        return $(this).val();
                    })
                    .get();
            }

            function validateRows(selectedIds, invalidStatuses) {
                const statusMap = {
                    1: 'WAITING',
                    2: 'APPROVED',
                    3: 'REJECTED',
                    4: 'CANCELED',
                };

                const invalidRows = [];

                selectedIds.forEach((id) => {
                    const rowData = table.row(`#row-${id}`).data();

                    if (rowData && rowData.status !== undefined) {
                        const currentStatus = statusMap[rowData.status] || 'UNKNOWN';

                        if (invalidStatuses.includes(currentStatus)) {
                            invalidRows.push({
                                id: rowData.id,
                                po_number: rowData.po_number,
                                status: currentStatus,
                            });
                        }
                    } else {
                        console.warn('Row data or status not found for ID:', id, rowData);
                    }
                });

                return invalidRows;
            }

            // Select all toggle
            $('#select-all').on('click', function() {
                const isChecked = $(this).prop('checked');
                $('.row-checkbox').prop('checked', isChecked);
            });

            // Keep select-all in sync when table redraws
            table.on('draw', function() {
                const isChecked = $('#select-all').prop('checked');
                $('.row-checkbox').prop('checked', isChecked);

                // Update total sum from server-side JSON
                const json = table.ajax?.json?.() || {};
                const totalSum = json.totalSum || 0;

                $('#total-sum').text(
                    new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 2,
                    }).format(totalSum)
                );
            });

            // When individual checkboxes change -> update select-all
            $(document).on('click', '.row-checkbox', function() {
                const total = $('.row-checkbox').length;
                const checked = $('.row-checkbox:checked').length;
                $('#select-all').prop('checked', total > 0 && total === checked);
            });
        });
    </script>
@endPushOnce
