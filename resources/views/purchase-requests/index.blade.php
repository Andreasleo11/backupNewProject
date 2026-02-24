@extends('new.layouts.app')

@section('title', 'Purchase Requisition List')

@section('content')
    <style>
        /* Premium Datatable Overrides */
        .premium-datatable-wrapper .dataTable thead th {
            border-bottom: 2px solid #e2e8f0 !important;
            color: #475569 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
            background-color: transparent !important;
        }
        .premium-datatable-wrapper .dataTable tbody tr {
            transition: all 0.2s ease;
        }
        .premium-datatable-wrapper .dataTable tbody tr:hover {
            background-color: #f8fafc !important;
            transform: scale(1.001);
        }
        .premium-datatable-wrapper .dataTable td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 1rem 0.75rem !important;
        }
        .premium-datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: white !important;
            border: none !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2) !important;
        }
        .premium-datatable-wrapper .dataTables_wrapper .dataTables_info {
            color: #64748b !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }
    </style>

    <div class="mx-auto max-w-7xl px-3 py-6 sm:px-4 lg:px-0 space-y-6">
        {{-- HEADER CARD --}}
        <div class="glass-card relative overflow-hidden flex flex-wrap items-center justify-between gap-4 p-6 sm:p-8">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-50 to-slate-100/20 pointer-events-none"></div>
            
            <div class="relative z-10">
                <h1 class="text-3xl font-black tracking-tight text-slate-800">
                    Purchase Requisition
                </h1>
                <p class="mt-1.5 text-sm font-medium text-slate-500">
                    Manage and track all procurement requests in one place
                </p>
            </div>

            <div class="relative z-10 flex flex-wrap items-center gap-3">
                {{-- Export button --}}
                <a href="{{ route('purchase-requests.export-excel') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm border border-emerald-100 transition-all hover:bg-emerald-50 hover:shadow-emerald-100 hover:-translate-y-0.5">
                    <i class="bi bi-file-earmark-excel text-lg"></i>
                    <span>Export</span>
                </a>

                {{-- Create PR button --}}
                @can('pr.create')
                    <a href="{{ route('purchase-requests.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:shadow-indigo-300 hover:from-indigo-500 hover:to-violet-500 hover:-translate-y-0.5">
                        <i class="bi bi-plus-lg text-lg"></i>
                        <span>New Request</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- STATS DASHBOARD --}}
        @include('partials.pr-stats-cards', ['stats' => $stats])

        {{-- BATCH ACTIONS --}}
        @if($canBatchApprove)
            <div class="glass-card p-4 flex flex-wrap items-center gap-3 backdrop-blur-md bg-white/70" id="batch-action-bar">
                <span class="text-sm font-bold text-slate-600 uppercase tracking-wider flex items-center gap-2">
                    <div class="h-6 w-1 bg-indigo-500 rounded-full"></div>
                    Director Actions
                </span>

                <div class="h-6 w-px bg-slate-200 mx-2"></div>

                {{-- Approve Selected --}}
                <button id="batch-approve-btn"
                        data-url="{{ route('purchase-requests.batch-approve') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all hover:-translate-y-0.5 group">
                    <i class="bi bi-check-lg group-hover:scale-110 transition-transform"></i>
                    Approve Selected
                </button>

                {{-- Reject Selected --}}
                <button id="batch-reject-btn"
                        data-url="{{ route('purchase-requests.batch-reject') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-100 hover:border-rose-300 transition-all hover:-translate-y-0.5 group">
                    <i class="bi bi-x-lg group-hover:scale-110 transition-transform"></i>
                    Reject Selected
                </button>

                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1 rounded-full ml-auto" id="batch-selection-count">No items selected</span>

                {{-- Reject reason input --}}
                <div id="batch-reject-reason-wrapper" class="hidden w-full mt-3 flex items-center gap-3 animate-fade-in">
                    <div class="relative flex-1">
                        <i class="bi bi-pencil-square absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="text" id="batch-reject-reason"
                               placeholder="Please provide a rejection reason (required)"
                               class="w-full rounded-xl border border-rose-200 bg-rose-50/50 pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400 transition-all placeholder-rose-300">
                    </div>
                    <button id="batch-reject-confirm-btn"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-200 hover:bg-rose-700 transition-all hover:-translate-y-0.5">
                        Confirm
                    </button>
                    <button id="batch-reject-cancel-btn"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all hover:-translate-y-0.5">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        <div class="glass-card overflow-hidden p-1 shadow-sm border border-slate-200/60 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-white to-slate-50/30 -z-10"></div>
            
            <div class="rounded-xl p-4">
                {{-- ACTIVE URL FILTER INDICATOR --}}
                @if(request()->filled('filter'))
                    <div class="mb-5 rounded-2xl bg-indigo-50/80 border border-indigo-100 p-3.5 flex items-center justify-between shadow-sm backdrop-blur-sm animate-fade-in">
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 shadow-inner">
                                <i class="bx bx-filter-alt text-xl"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Active View</span>
                                <span class="text-sm text-indigo-950 font-bold">
                                    @if(request('filter') === 'my_approval') Pending My Approval
                                    @elseif(request('filter') === 'in_review') In Review
                                    @elseif(request('filter') === 'approved_month') Approved This Month
                                    @else Custom Saved Filter
                                    @endif
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('purchase-requests.index') }}" class="group inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 bg-white hover:bg-indigo-600 hover:text-white px-4 py-2 rounded-xl border border-indigo-200 transition-all hover:shadow-md shadow-sm">
                            <i class="bx bx-x text-sm group-hover:rotate-90 transition-transform"></i> Clear Filter
                        </a>
                    </div>
                @endif

                {{-- CUSTOM DATA FILTERS --}}
                <div class="mb-6 bg-white border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center gap-4 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-50 to-transparent pointer-events-none"></div>
                    
                    <div class="relative flex items-center gap-2 text-slate-800 font-bold text-sm uppercase tracking-wider">
                        <div class="h-5 w-1 bg-slate-300 rounded-full"></div>
                        <i class="bi bi-funnel text-slate-400"></i> Filters
                    </div>
                    
                    <div class="h-6 w-px bg-slate-100 mx-2 hidden sm:block"></div>
                    
                    <div class="w-full sm:w-56 relative z-10 group">
                        <label for="filter-status" class="sr-only">Status</label>
                        <i class="bx bx-loader-circle absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-400 transition-colors"></i>
                        <select id="filter-status" class="w-full form-select text-sm border-slate-200 rounded-xl shadow-sm pl-9 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors cursor-pointer font-medium text-slate-700">
                            <option value="">All Statuses</option>
                            <option value="DRAFT">Draft</option>
                            <option value="IN_REVIEW">In Review</option>
                            <option value="APPROVED">Approved</option>
                            <option value="REJECTED">Rejected</option>
                            <option value="CANCELED">Canceled</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-64 relative z-10 group">
                        <label for="filter-department" class="sr-only">Target Department</label>
                        <i class="bx bx-buildings absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-400 transition-colors"></i>
                        <select id="filter-department" class="w-full form-select text-sm border-slate-200 rounded-xl shadow-sm pl-9 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors cursor-pointer font-medium text-slate-700">
                            <option value="">All Target Departments</option>
                            <option value="PURCHASING">Purchasing</option>
                            <option value="PERSONALIA">Personalia / HRD</option>
                            <option value="MAINTENANCE">Maintenance</option>
                            <option value="COMPUTER">Computer / IT</option>
                        </select>
                    </div>
                    
                    <button id="btn-reset-filters" class="relative z-10 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors ml-auto sm:ml-0 flex items-center gap-1 bg-slate-50 hover:bg-indigo-50 px-3 py-2 rounded-lg border border-transparent hover:border-indigo-100">
                        <i class="bx bx-reset"></i> Reset
                    </button>
                </div>

                <div class="premium-datatable-wrapper">
                    {{ $dataTable->table(['class' => 'table table-hover table-striped w-100 dt-responsive nowrap']) }}
                </div>
            </div>
        </div>

        @push('modals')
            {{-- QUICK VIEW MODAL --}}
            <div class="modal fade" id="prQuickViewModal" tabindex="-1" aria-labelledby="prQuickViewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-xl overflow-hidden">
                        <div class="modal-header bg-gradient-to-r from-indigo-50 to-white border-b border-indigo-100 px-5 py-4">
                            <h5 class="modal-title font-semibold text-slate-800 flex items-center gap-2" id="prQuickViewModalLabel">
                                <i class="bi bi-file-earmark-text text-indigo-600"></i>
                                Purchase Request Detail
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0 bg-slate-50 relative min-h-[300px]" id="prQuickViewContent">
                            {{-- Content loaded by AJAX --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-white/80 z-10" id="prQuickViewLoader">
                                <div class="spinner-border text-indigo-600" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-t border-slate-100 px-5 py-3 rounded-b-xl">
                            <a href="#" id="prQuickViewDetailBtn" class="btn btn-primary rounded-lg text-sm px-4">Full Details &rarr;</a>
                            <button type="button" class="btn btn-secondary rounded-lg text-sm px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shared Action Modals --}}
            @include('partials.delete-pr-modal')
            @include('partials.cancel-pr-confirmation-modal')
            @include('partials.edit-purchase-request-po-number-modal')
        @endpush
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        /* Custom DataTable Styling Overrides */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        table.dataTable.table-striped > tbody > tr.odd > * {
            box-shadow: none !important; /* Remove bootstrap striped shadow */
            background-color: rgba(248, 250, 252, 0.5); /* Very light slate */
        }
        table.dataTable.table-striped > tbody > tr:hover > * {
            background-color: rgba(241, 245, 249, 0.8) !important; /* Slate-100 on hover */
        }
        table.dataTable {
            border-collapse: separate;
            border-spacing: 0;
            border-bottom: 1px solid #f1f5f9;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e2e8f0 !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        table.dataTable tbody td {
            font-size: 0.875rem; /* text-sm */
            padding-top: 0.5rem; /* Reduced padding for compact look */
            padding-bottom: 0.5rem;
            vertical-align: middle;
        }
        .page-item.active .page-link {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            border-radius: 0.5rem;
        }
        .page-link {
            border-radius: 0.5rem;
            margin: 0 2px;
            color: #64748b;
            border: none;
        }
        div.dt-button-collection {
            border-radius: 1rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0.5rem !important;
        }
    </style>
@endsection

@push('scripts')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{ $dataTable->scripts() }}

    {{-- CUSTOM FILTERS LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTables exposes its instance globally via Laravel DataTables (usually window.LaravelDataTables['purchaserequests-table'])
            const tableId = 'purchaserequests-table';
            
            // Wait for DataTable to initialize
            setTimeout(() => {
                if(window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    const dt = window.LaravelDataTables[tableId];

                    // Bind filter changes to reload table
                    $('#filter-status, #filter-department').on('change', function() {
                        dt.ajax.reload();
                    });

                    // Reset button
                    $('#btn-reset-filters').on('click', function(e) {
                        e.preventDefault();
                        $('#filter-status').val('');
                        $('#filter-department').val('');
                        dt.ajax.reload();
                    });

                    // Intercept AJAX request to append our custom filter values
                    dt.on('preXhr.dt', function ( e, settings, data ) {
                        data.custom_status = $('#filter-status').val();
                        data.custom_department = $('#filter-department').val();
                    });
                }
            }, 500);
        });
    </script>

    @if($canBatchApprove)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Helper function to get checked IDs
                const getSelectedIds = () => {
                    const checkboxes = document.querySelectorAll('tbody input.pr-checkbox:checked');
                    return Array.from(checkboxes).map(cb => cb.value);
                };

                // Update selection count text
                const updateSelectionCount = () => {
                    const count = document.querySelectorAll('tbody input.pr-checkbox:checked').length;
                    const countLabel = document.getElementById('batch-selection-count');
                    const btnApprove = document.getElementById('batch-approve-btn');
                    const btnReject = document.getElementById('batch-reject-btn');

                    if(count > 0) {
                        countLabel.textContent = `${count} item${count > 1 ? 's' : ''} selected`;
                        countLabel.classList.remove('text-slate-400');
                        countLabel.classList.add('text-indigo-600', 'font-semibold');
                        btnApprove.disabled = false;
                        btnReject.disabled = false;
                        btnApprove.classList.remove('opacity-50', 'cursor-not-allowed');
                        btnReject.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else {
                        countLabel.textContent = 'No items selected';
                        countLabel.classList.remove('text-indigo-600', 'font-semibold');
                        countLabel.classList.add('text-slate-400');
                        btnApprove.disabled = true;
                        btnReject.disabled = true;
                        btnApprove.classList.add('opacity-50', 'cursor-not-allowed');
                        btnReject.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                };

                // Use event delegation for checkboxes, as DataTable redraws them
                document.querySelector('.premium-datatable-wrapper').addEventListener('change', function(e) {
                    if(e.target.classList.contains('pr-checkbox')) {
                        updateSelectionCount();
                    }
                    
                    // Handle "Check All" if it exists
                    if(e.target.id === 'check-all-prs') {
                        const isChecked = e.target.checked;
                        document.querySelectorAll('tbody input.pr-checkbox').forEach(cb => {
                            cb.checked = isChecked;
                        });
                        updateSelectionCount();
                    }
                });

                // Handle DataTable draw events to reset selection UI
                window.LaravelDataTables["purchaserequests-table"].on('draw', function() {
                    updateSelectionCount();
                    const checkAll = document.getElementById('check-all-prs');
                    if(checkAll) checkAll.checked = false;
                });

                // Initial UI state setup
                updateSelectionCount();

                // Setup bulk Action Request function
                const performBulkAction = (url, data, successCallback) => {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we process your request.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            if (successCallback) successCallback();
                            window.LaravelDataTables["purchaserequests-table"].ajax.reload(null, false);
                            updateSelectionCount();
                        } else {
                            throw new Error(data.message || 'An error occurred during processing.');
                        }
                    })
                    .catch(error => {
                        console.error('Action failed:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message || 'Something went wrong!',
                        });
                    });
                };

                // ====== Approve Selected ======
                document.getElementById('batch-approve-btn').addEventListener('click', function() {
                    const ids = getSelectedIds();
                    if(ids.length === 0) return;

                    Swal.fire({
                        title: 'Approve Selected?',
                        text: `You are about to approve ${ids.length} purchase request(s).`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#059669', // emerald-600
                        cancelButtonColor: '#64748b',  // slate-500
                        confirmButtonText: 'Yes, approve them'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performBulkAction(this.dataset.url, { ids: ids });
                        }
                    });
                });

                // ====== Reject Selected UI logic ======
                const wrapper = document.getElementById('batch-reject-reason-wrapper');
                const rejectBtn = document.getElementById('batch-reject-btn');
                const approveBtn = document.getElementById('batch-approve-btn');
                const confirmRejectBtn = document.getElementById('batch-reject-confirm-btn');
                const cancelRejectBtn = document.getElementById('batch-reject-cancel-btn');
                const reasonInput = document.getElementById('batch-reject-reason');

                rejectBtn.addEventListener('click', function() {
                    if(getSelectedIds().length === 0) return;
                    
                    // Show inline reason input
                    wrapper.classList.remove('hidden');
                    rejectBtn.classList.add('hidden');
                    approveBtn.classList.add('hidden');
                    reasonInput.focus();
                });

                cancelRejectBtn.addEventListener('click', function() {
                    wrapper.classList.add('hidden');
                    rejectBtn.classList.remove('hidden');
                    approveBtn.classList.remove('hidden');
                    reasonInput.value = ''; // clear
                });

                confirmRejectBtn.addEventListener('click', function() {
                    const ids = getSelectedIds();
                    const reason = reasonInput.value.trim();

                    if(ids.length === 0) return;
                    if(!reason) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Reason Required',
                            text: 'Please provide a reason for rejecting the selected requests.'
                        });
                        reasonInput.focus();
                        return;
                    }

                    performBulkAction(rejectBtn.dataset.url, {
                        ids: ids,
                        rejection_reason: reason
                    }, () => {
                        // Success callback
                        cancelRejectBtn.click(); // Hide reason input
                    });
                });
            });
        </script>
    @endif

    {{-- QUICK VIEW LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableWrapper = document.querySelector('.premium-datatable-wrapper');
            if(!tableWrapper) return;

            let quickViewModal = null;
            let deleteModal = null;
            let cancelModal = null;
            let editPoModal = null;
            
            if(document.getElementById('prQuickViewModal')) {
                quickViewModal = new bootstrap.Modal(document.getElementById('prQuickViewModal'));
            }
            if(document.getElementById('delete-pr-modal')) {
                deleteModal = new bootstrap.Modal(document.getElementById('delete-pr-modal'));
            }
            if(document.getElementById('cancel-confirmation-modal')) {
                cancelModal = new bootstrap.Modal(document.getElementById('cancel-confirmation-modal'));
            }
            if(document.getElementById('edit-purchase-request-po-number')) {
                editPoModal = new bootstrap.Modal(document.getElementById('edit-purchase-request-po-number'));
            }

            tableWrapper.addEventListener('click', function(e) {
                // Shared Delete Action
                const deleteBtn = e.target.closest('.btn-delete-pr');
                if (deleteBtn && deleteModal) {
                    const id = deleteBtn.getAttribute('data-id');
                    const doc = deleteBtn.getAttribute('data-doc');
                    document.getElementById('delete-doc-num').textContent = doc;
                    document.getElementById('delete-pr-form').action = `/purchase-requests/${id}`;
                    deleteModal.show();
                    return;
                }

                // Shared Cancel Action
                const cancelBtn = e.target.closest('.btn-cancel-pr');
                if (cancelBtn && cancelModal) {
                    const id = cancelBtn.getAttribute('data-id');
                    const doc = cancelBtn.getAttribute('data-doc');
                    document.getElementById('cancel-doc-num').textContent = doc;
                    document.getElementById('cancel-description').value = '';
                    document.getElementById('cancel-pr-form').action = `/purchase-requests/${id}/cancel`;
                    cancelModal.show();
                    return;
                }

                // Shared Edit PO Action
                const poBtn = e.target.closest('.btn-edit-po');
                if (poBtn && editPoModal) {
                    const id = poBtn.getAttribute('data-id');
                    const doc = poBtn.getAttribute('data-doc');
                    const po = poBtn.getAttribute('data-po') || '';
                    document.getElementById('edit-po-doc-num').textContent = doc;
                    document.getElementById('po_number_input').value = po;
                    document.getElementById('edit-po-form').action = `/purchase-requests/${id}/po-number`;
                    editPoModal.show();
                    return;
                }

                // Find nearest button with class quick-view-btn
                const btn = e.target.closest('.quick-view-btn');
                if(!btn) return;

                const prId = btn.getAttribute('data-id');
                if(!prId || !quickViewModal) return;

                // Show modal & loader
                const contentDiv = document.getElementById('prQuickViewContent');
                const loaderDiv = document.getElementById('prQuickViewLoader');
                const detailBtn = document.getElementById('prQuickViewDetailBtn');
                
                detailBtn.href = `/purchase-requests/${prId}`;
                quickViewModal.show();
                
                // Clear old content and show loader
                Array.from(contentDiv.children).forEach(child => {
                    if(child.id !== 'prQuickViewLoader') child.remove();
                });
                loaderDiv.classList.remove('hidden');

                // Fetch HTML
                fetch(`/purchase-requests/${prId}/quick-view`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if(!res.ok) throw new Error('Failed to load detail');
                    return res.text();
                })
                .then(html => {
                    // Hide loader and insert HTML
                    loaderDiv.classList.add('hidden');
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    contentDiv.appendChild(wrapper);
                })
                .catch(err => {
                    console.error(err);
                    loaderDiv.classList.add('hidden');
                    contentDiv.innerHTML = `<div class="p-6 text-center text-rose-500"><i class="bi bi-exclamation-triangle text-3xl mb-2 block"></i><p>Could not load the purchase request details.</p></div>`;
                });
            });
        });
    </script>
@endpush
