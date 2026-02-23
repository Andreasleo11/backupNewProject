@extends('new.layouts.app')

@section('title', 'Purchase Requisition List')

@section('content')
    <div class="mx-auto max-w-7xl px-3 py-6 sm:px-4 lg:px-0 space-y-6">
        {{-- HEADER CARD --}}
        <div class="glass-card flex flex-wrap items-center justify-between gap-4 p-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    Purchase Requisition
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage and track all procurement requests in one place.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Export button --}}
                <a href="{{ route('purchase-requests.export-excel') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition-all hover:bg-emerald-100 hover:shadow-md hover:-translate-y-0.5">
                    <i class="bi bi-file-earmark-excel text-lg"></i>
                    <span>Export</span>
                </a>

            {{-- Create PR button — shown to users who can create PRs --}}
                @can('pr.create')
                    <a href="{{ route('purchase-requests.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:shadow-indigo-300 hover:-translate-y-0.5">
                        <i class="bi bi-plus-lg text-lg"></i>
                        <span>New Request</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- STATS DASHBOARD --}}
        @include('partials.pr-stats-cards', ['stats' => $stats])

        {{-- BATCH ACTIONS — only visible to users with pr.batch-approve permission --}}
        @if($canBatchApprove)
            <div class="glass-card p-4 flex flex-wrap items-center gap-3" id="batch-action-bar">
                <span class="text-sm font-medium text-slate-600">
                    <i class="bi bi-check2-square me-1"></i>
                    Director Actions:
                </span>

                {{-- Approve Selected --}}
                <button id="batch-approve-btn"
                        data-url="{{ route('purchase-requests.batch-approve') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all hover:-translate-y-0.5">
                    <i class="bi bi-check-lg"></i>
                    Approve Selected
                </button>

                {{-- Reject Selected --}}
                <button id="batch-reject-btn"
                        data-url="{{ route('purchase-requests.batch-reject') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-all hover:-translate-y-0.5">
                    <i class="bi bi-x-lg"></i>
                    Reject Selected
                </button>

                <span class="text-xs text-slate-400 ml-auto" id="batch-selection-count">No items selected</span>

                {{-- Reject reason input (hidden until reject clicked) --}}
                <div id="batch-reject-reason-wrapper" class="hidden w-full mt-2 flex items-center gap-3">
                    <input type="text" id="batch-reject-reason"
                           placeholder="Rejection reason (required)"
                           class="flex-1 rounded-xl border border-rose-200 bg-rose-50/50 px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400">
                    <button id="batch-reject-confirm-btn"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition-all">
                        Confirm Reject
                    </button>
                    <button id="batch-reject-cancel-btn"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        <div class="glass-card overflow-hidden p-1">
            <div class="rounded-xl bg-white/50 p-4">
                {{-- Tips/Info --}}
                <div class="mb-4 flex items-center gap-2 rounded-lg bg-blue-50/50 px-3 py-2 text-xs text-blue-700 border border-blue-100">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><strong>Pro Tip:</strong> Use the <strong>Search Panes</strong> button above the table to filter by multiple criteria instantly.</span>
                </div>

                <div class="premium-datatable-wrapper">
                    {{ $dataTable->table(['class' => 'table table-hover table-striped w-100 dt-responsive nowrap']) }}
                </div>
            </div>
        </div>

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

    {{-- Search Panes CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    {{-- Search Panes JS --}}
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>

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
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
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
            if(document.getElementById('prQuickViewModal')) {
                quickViewModal = new bootstrap.Modal(document.getElementById('prQuickViewModal'));
            }

            tableWrapper.addEventListener('click', function(e) {
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
