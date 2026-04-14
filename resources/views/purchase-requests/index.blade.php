@extends('new.layouts.app')

@section('title', 'Purchase Requisition List')
@section('page-title', 'Purchase Requests Overview')
@section('page-subtitle', 'Centralized tracking and workflow management for all departmental requisitions.')

@section('content')
@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Premium Datatable Overrides */
        .premium-datatable-wrapper .dataTable {
            border-collapse: separate;
            border-spacing: 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .premium-datatable-wrapper .dataTable thead th {
            border-bottom: 2px solid #e2e8f0 !important;
            color: #475569 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 1rem 0.75rem !important;
            background-color: transparent !important;
        }
        .premium-datatable-wrapper .dataTable tbody tr {
            transition: all 0.2s ease;
        }
        .premium-datatable-wrapper .dataTable.table-striped > tbody > tr.odd > * {
            box-shadow: none !important;
            background-color: rgba(248, 250, 252, 0.4); 
        }
        .premium-datatable-wrapper .dataTable tbody tr:hover > * {
            background-color: rgba(241, 245, 249, 0.8) !important;
        }
        .premium-datatable-wrapper .dataTable td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 0.85rem 0.75rem !important;
            font-size: 0.875rem;
        }
        
        /* DataTables Controls */
        .premium-datatable-wrapper .dataTables_length select,
        .premium-datatable-wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 0.75rem;
            outline: none;
            transition: all 0.2s;
            font-size: 0.875rem;
        }
        .premium-datatable-wrapper .dataTables_length select:focus,
        .premium-datatable-wrapper .dataTables_filter input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }
        .premium-datatable-wrapper .dataTables_info {
            color: #64748b !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }
        
        /* Pagination Styling */
        .premium-datatable-wrapper .page-link {
            border-radius: 0.5rem;
            margin: 0 2px;
            color: #64748b;
            border: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        .premium-datatable-wrapper .page-item.active .page-link {
            background-color: #4f46e5 !important;
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2) !important;
        }
        .premium-datatable-wrapper .page-item:not(.active) .page-link:hover {
            background-color: #f1f5f9;
            color: #4f46e5;
        }
        
    </style>
@endpush

    <div class="sm:px-4 lg:px-0 space-y-6" x-data="prIndex()">
        {{-- HEADER CARD --}}
        <div class="glass-card mb-6 overflow-hidden pt-5 pb-4 px-6 relative">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/5 to-purple-600/5 pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4 border-l-4 border-indigo-600 pl-4">
                    <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <i class='bx bx-receipt text-xl'></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-800">
                                Purchase Requests Overview
                            </h1>
                            @if(request()->filled('filter'))
                                <div class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold border border-indigo-200 flex items-center gap-1 shadow-sm uppercase tracking-wide">
                                    <i class="bx bx-filter-alt"></i>
                                    @if(request('filter') === 'my_approval') Pending My Approval
                                    @elseif(request('filter') === 'in_review') In Review
                                    @elseif(request('filter') === 'approved_month') Approved This Month
                                    @else Custom Saved Filter
                                    @endif
                                    <a href="{{ route('purchase-requests.index', ['filter' => 'all']) }}" class="ml-1 text-indigo-400 hover:text-indigo-900 transition-colors bg-white rounded-full h-4 w-4 flex items-center justify-center border border-indigo-200"><i class="bx bx-x text-xs"></i></a>
                                </div>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-slate-500 mt-0.5">
                            Centralized tracking and workflow management for all departmental requisitions.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 flex flex-wrap items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                    {{-- Insights Toggle --}}
                    <button type="button" @click="activeDrawer = (activeDrawer === 'insights' ? null : 'insights')" 
                            class="inline-flex items-center justify-center rounded-xl bg-white h-10 w-10 text-sm font-semibold shadow-sm border transition-all hover:-translate-y-0.5"
                            :class="{'border-amber-300 ring-2 ring-amber-50 bg-amber-50 text-amber-700': activeDrawer === 'insights', 'border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600': activeDrawer !== 'insights'}"
                            title="Toggle Statistics & Insights">
                        <i class="bx bx-bar-chart-alt-2 text-xl"></i>
                    </button>

                    {{-- Filters Toggle --}}
                    <button type="button" @click="activeDrawer = (activeDrawer === 'filters' ? null : 'filters')" 
                            class="inline-flex items-center justify-center rounded-xl bg-white h-10 w-10 text-sm font-semibold shadow-sm border transition-all hover:-translate-y-0.5 relative"
                            :class="{'border-indigo-300 ring-2 ring-indigo-50 bg-indigo-50 text-indigo-700': activeDrawer === 'filters' || activeFilterCount > 0, 'border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600': activeDrawer !== 'filters' && activeFilterCount === 0}"
                            title="Filter & Export Options">
                        <i class="bi bi-funnel text-lg"></i>
                        <span x-show="activeFilterCount > 0" class="absolute -top-1.5 -right-1.5 bg-indigo-600 text-white text-[10px] h-4 min-w-[16px] px-1 rounded-full flex items-center justify-center border-2 border-white" x-text="activeFilterCount"></span>
                    </button>

                    <div class="h-6 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                    {{-- Create PR button --}}
                    @can('pr.create')
                        <a href="{{ route('purchase-requests.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:shadow-indigo-300 hover:from-indigo-500 hover:to-violet-500 hover:-translate-y-0.5 h-10">
                            <i class="bi bi-plus-lg text-base"></i>
                            <span class="hidden sm:inline">New Request</span>
                            <span class="sm:hidden">New</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- STATS DASHBOARD --}}
        <div x-show="activeDrawer === 'insights'" x-collapse x-cloak>
            <div class="mb-6">
                @include('partials.pr-stats-cards', ['stats' => $stats])
            </div>
        </div>

        {{-- BATCH ACTIONS --}}
        @if($canBatchApprove)
            <div x-show="selectedIds.length > 0" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="glass-card mb-4 p-4 flex flex-wrap items-center gap-3 backdrop-blur-md bg-white/70 border border-indigo-100 shadow-md shadow-indigo-100/50" id="batch-action-bar">
                
                <span class="text-sm font-bold text-slate-600 uppercase tracking-wider flex items-center gap-2">
                    <div class="h-6 w-1 bg-indigo-500 rounded-full animate-pulse"></div>
                    Director Actions
                </span>

                <div class="h-6 w-px bg-slate-200 mx-2"></div>

                {{-- Approve Selected --}}
                <button id="batch-approve-btn"
                        @click="confirmBatchApprove('{{ route('purchase-requests.batch-approve') }}')"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all hover:-translate-y-0.5 group">
                    <i class="bi bi-check-lg group-hover:scale-110 transition-transform"></i>
                    Approve Selected
                </button>

                {{-- Reject Selected --}}
                <button id="batch-reject-btn"
                        x-show="!showRejectReason"
                        @click="showRejectReason = true"
                        class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-100 hover:border-rose-300 transition-all hover:-translate-y-0.5 group">
                    <i class="bi bi-x-lg group-hover:scale-110 transition-transform"></i>
                    Reject Selected
                </button>

                <span class="text-xs font-bold text-slate-500 bg-slate-100/80 px-3 py-1.5 border border-slate-200 rounded-full ml-auto" 
                      x-text="selectedIds.length === 0 ? 'No items selected' : selectedIds.length + ' item(s) selected'">
                </span>

                {{-- Reject reason input --}}
                <div id="batch-reject-reason-wrapper" x-show="showRejectReason" x-cloak class="w-full mt-3 flex items-center gap-3 animate-fade-in">
                    <div class="relative flex-1">
                        <i class="bi bi-pencil-square absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="text" id="batch-reject-reason" x-model="rejectionReason"
                               placeholder="Please provide a rejection reason (required)"
                               class="w-full rounded-xl border border-rose-200 bg-rose-50/50 pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400 transition-all placeholder-rose-300">
                    </div>
                    <button id="batch-reject-confirm-btn"
                            @click="confirmBatchReject('{{ route('purchase-requests.batch-reject') }}')"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-200 hover:bg-rose-700 transition-all hover:-translate-y-0.5">
                        Confirm
                    </button>
                    <button id="batch-reject-cancel-btn"
                            @click="cancelReject()"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all hover:-translate-y-0.5">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        {{-- BATCH PROGRESS DASHBOARD --}}
        <template x-if="activeBatches.length > 0">
            <div class="mt-4 space-y-3">
                <template x-for="batch in activeBatches" :key="batch.id">
                    <div class="glass-card p-4 bg-white/80 border border-indigo-100 rounded-xl relative overflow-hidden animate-fade-in shadow-sm">
                        <!-- Background Progress Fill -->
                        <div class="absolute left-0 top-0 bottom-0 bg-indigo-50/50 transition-all duration-500 ease-out z-0" 
                             :style="`width: ${batch.progress}%`"></div>
                             
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-100 text-indigo-600 w-8 h-8 rounded-full flex items-center justify-center shadow-inner">
                                    <i class="bx bx-loader-alt animate-spin text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-700">Processing Background Task</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <span x-text="batch.totalJobs - batch.pendingJobs"></span> of <span x-text="batch.totalJobs"></span> requests completed
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-indigo-600" x-text="`${Math.round(batch.progress)}%`"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <div class="glass-card overflow-hidden p-1 shadow-sm border border-slate-200/60 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-white to-slate-50/30 -z-10"></div>
            
            <div class="rounded-xl p-4">
                {{-- CUSTOM DATA FILTERS --}}
                <div x-show="activeDrawer === 'filters'" x-collapse x-cloak>
                    <div class="mb-4 bg-white border border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center gap-4 relative overflow-hidden bg-gradient-to-r from-slate-50 to-white">
                    
                    <div class="relative flex items-center gap-2 text-slate-800 font-bold text-sm uppercase tracking-wider">
                        <div class="h-5 w-1 bg-slate-300 rounded-full"></div>
                        <i class="bi bi-funnel text-slate-400"></i> Filters
                    </div>
                    
                    <div class="h-6 w-px bg-slate-100 mx-2 hidden sm:block"></div>
                    
                    <div class="w-full sm:w-56 relative z-10 group">
                        <label for="filter-status" class="sr-only">Status</label>
                        <i class="bx bx-loader-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-400 transition-colors text-lg pointer-events-none"></i>
                        <select id="filter-status" x-model="filters.status" @change="reloadTable()" class="w-full form-select text-sm border-slate-200 rounded-xl shadow-sm !pl-10 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors cursor-pointer font-medium text-slate-700">
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
                        <i class="bx bx-buildings absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-400 transition-colors text-lg pointer-events-none"></i>
                        <select id="filter-department" x-model="filters.department" @change="reloadTable()" class="w-full form-select text-sm border-slate-200 rounded-xl shadow-sm !pl-10 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors cursor-pointer font-medium text-slate-700">
                            <option value="">All Target Departments</option>
                            <option value="Purchasing">Purchasing</option>
                            <option value="Personnel">Personalia / HRD</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Computer">Computer / IT</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-56 relative z-10 group" x-ref="datepickerContainer">
                        <label for="filter-date" class="sr-only">Date Range</label>
                        <i class="bx bx-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-400 transition-colors text-lg pointer-events-none"></i>
                        <input type="text" id="filter-date" x-ref="datePickerInput" placeholder="Filter Period..." class="w-full form-input text-sm border-slate-200 rounded-xl shadow-sm !pl-10 pr-8 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 hover:bg-white transition-colors cursor-pointer font-medium text-slate-700">
                        <button type="button" x-show="filters.date" @click="clearDate()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-rose-500 transition-colors">
                            <i class="bx bx-x text-lg"></i>
                        </button>
                    </div>
                    <div class="h-8 w-px bg-slate-100 mx-2 hidden lg:block"></div>

                    <div class="flex items-center gap-3 ml-auto">
                        {{-- Export Spreadsheet within Filter workflow --}}
                        <a href="{{ route('purchase-requests.export-excel') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm border border-emerald-100 transition-all hover:bg-emerald-50 hover:shadow-emerald-100 hover:-translate-y-0.5 shrink-0"
                           title="Export active filter results to Excel">
                            <i class="bi bi-file-earmark-excel text-lg text-emerald-600"></i>
                            <span class="hidden lg:inline">Export</span>
                        </a>

                        <button id="btn-reset-filters" @click="resetFilters()" class="relative z-10 text-xs font-semibold text-slate-400 hover:text-rose-600 transition-colors flex items-center gap-1 bg-slate-50 hover:bg-rose-50 px-3 py-2.5 rounded-xl border border-transparent hover:border-rose-100 shrink-0">
                            <i class="bx bx-reset text-base"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

                <div class="premium-datatable-wrapper custom-scrollbar pb-4 block w-full mt-2">
                    {{ $dataTable->table(['class' => 'table table-hover table-striped w-full nowrap']) }}
                </div>
            </div>
        </div>

        @push('modals')
            {{-- QUICK VIEW MODAL (Alpine Headless) --}}
            <div x-data="{ 
                    show: false, 
                    id: null,
                    isLoading: false,
                    htmlContent: '',
                    open(e) {
                        this.id = e.detail.id;
                        this.show = true;
                        this.isLoading = true;
                        this.htmlContent = '';
                        
                        fetch(`/purchase-requests/${this.id}/quick-view`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => {
                            if(!res.ok) throw new Error('Failed to load detail');
                            return res.text();
                        })
                        .then(html => {
                            this.htmlContent = html;
                            this.isLoading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.htmlContent = `<div class='p-6 text-center text-rose-500'><i class='bi bi-exclamation-triangle text-3xl mb-2 block'></i><p>Could not load the purchase request details.</p></div>`;
                            this.isLoading = false;
                        });
                    },
                    closeModal() {
                        this.show = false;
                    },
                    reloadAfterAction() {
                        this.show = false;
                        this.$nextTick(() => {
                            const tableId = 'purchaserequests-table';
                            if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                                window.LaravelDataTables[tableId].ajax.reload(null, false);
                            }
                        });
                    }
                }" 
                x-effect="document.body.style.overflow = show ? 'hidden' : ''"
                @open-quick-view-modal.window="open($event)"
                @qv-close.window="closeModal()"
                @qv-action-success.window="reloadAfterAction()"
                @keydown.escape.window="show = false"
                x-show="show" 
                class="relative z-[100]" 
                aria-labelledby="prQuickViewModalLabel" 
                role="dialog" 
                aria-modal="true"
                x-cloak>
                
                {{-- Backdrop --}}
                <div x-show="show" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
                     
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="show"
                             @click.away="show = false"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative transform flex flex-col max-h-[90vh] overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 w-full max-w-4xl border border-slate-100">
                            
                            {{-- Header --}}
                            <div class="bg-gradient-to-r from-indigo-50 to-white border-b border-indigo-100 px-5 py-4 flex items-center justify-between shrink-0">
                                <h5 class="font-bold text-slate-800 flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                        <i class="bx bx-search-alt text-lg"></i>
                                    </div>
                                    Purchase Request Detail
                                </h5>
                                <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>
                            
                            {{-- Body (Scrollable) --}}
                            <div class="p-0 bg-slate-50 relative min-h-[300px] overflow-y-auto flex-1 custom-scrollbar">
                                {{-- Loader Overlay --}}
                                <div x-show="isLoading" 
                                     x-transition
                                     class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 backdrop-blur-sm">
                                    <div class="spinner-border text-indigo-600" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                
                                {{-- Injected AJAX Content --}}
                                <div x-html="htmlContent"></div>
                            </div>
                            
                            {{-- Footer --}}
                            <div class="bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2 shrink-0">
                                <button type="button" @click="show = false" class="bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 py-2 font-medium transition-colors">Close</button>
                                <a :href="`/purchase-requests/${id}`" class="bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 text-white border-0 rounded-lg text-sm px-4 py-2 font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                                    Full Details <i class="bx bx-right-arrow-alt"></i>
                                </a>
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


@endsection

@push('scripts')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{ $dataTable->scripts() }}

    <script>
        window.prIndex = function() {
            return {
                tableId: 'purchaserequests-table',
                activeDrawer: null,
                filters: {
                    status: '',
                    department: '',
                    date: ''
                },
                get activeFilterCount() {
                    return [this.filters.status, this.filters.department, this.filters.date].filter(Boolean).length;
                },
                selectedIds: [],
                showRejectReason: false,
                rejectionReason: '',
                activeBatches: [],
                pollingInterval: null,

                init() {
                    // Manual Persistence (Safest way across all Alpine setups)
                    // Default to 'insights' if no preference saved yet
                    const savedDrawer = localStorage.getItem('pr_active_drawer');
                    this.activeDrawer = savedDrawer === null ? 'insights' : savedDrawer;

                    const savedFilters = localStorage.getItem('pr_filters');
                    if (savedFilters) {
                        try {
                            const parsed = JSON.parse(savedFilters);
                            if (parsed) this.filters = parsed;
                        } catch (e) {
                            console.warn('Failed to parse saved filters', e);
                        }
                    }

                    // Save on change
                    this.$watch('activeDrawer', val => {
                        if (val) localStorage.setItem('pr_active_drawer', val);
                        else localStorage.removeItem('pr_active_drawer');
                    });
                    this.$watch('filters', val => {
                        localStorage.setItem('pr_filters', JSON.stringify(val));
                    }, { deep: true });

                    // Start intercepting Datatables data
                    const waitInterval = setInterval(() => {
                        if(window.LaravelDataTables && window.LaravelDataTables[this.tableId]) {
                            clearInterval(waitInterval);
                            const dt = window.LaravelDataTables[this.tableId];

                            // Intercept XHR parameters
                            dt.on('preXhr.dt', (e, settings, data) => {
                                data.custom_status = this.filters.status;
                                data.custom_department = this.filters.department;
                                data.custom_date = this.filters.date;
                            });

                            // Initialize Flatpickr if available
                            if (typeof window.flatpickr !== 'undefined') {
                                this.fp = window.flatpickr(this.$refs.datePickerInput, {
                                    mode: "range",
                                    dateFormat: "Y-m-d",
                                    onChange: (selectedDates, dateStr, instance) => {
                                        this.filters.date = dateStr;
                                        // Usually wait til both dates are selected for range
                                        if (selectedDates.length === 2 || selectedDates.length === 1 && instance.config.mode !== 'range') {
                                             this.reloadTable();
                                        }
                                    }
                                });
                            }

                            // Clear selection upon redraw
                            dt.on('draw', () => {
                                this.selectedIds = [];
                                const checkAll = document.getElementById('check-all-prs');
                                if(checkAll) checkAll.checked = false;
                            });

                            // Listen for row-level checkbox clicks via delegated handler using Alpine's $el
                            this.$el.addEventListener('change', (e) => {
                                if(e.target.classList.contains('pr-checkbox')) {
                                    this.syncSelection();
                                }
                                
                                if(e.target.id === 'check-all-prs') {
                                    const isChecked = e.target.checked;
                                    document.querySelectorAll('tbody input.pr-checkbox').forEach(cb => {
                                        cb.checked = isChecked;
                                    });
                                    this.syncSelection();
                                }
                            });
                        }
                    }, 500);
                },

                syncSelection() {
                    const checkboxes = document.querySelectorAll('tbody input.pr-checkbox:checked');
                    this.selectedIds = Array.from(checkboxes).map(cb => cb.value);
                    if(this.selectedIds.length === 0) {
                        this.cancelReject();
                    }
                },

                reloadTable() {
                    if(window.LaravelDataTables && window.LaravelDataTables[this.tableId]) {
                        window.LaravelDataTables[this.tableId].ajax.reload(null, false);
                    }
                },

                startBatchPolling() {
                    if (this.pollingInterval) return;
                    
                    this.pollingInterval = setInterval(() => {
                        const batchIds = this.activeBatches.map(b => b.id);
                        if (batchIds.length === 0) {
                            clearInterval(this.pollingInterval);
                            this.pollingInterval = null;
                            return;
                        }

                        const params = new URLSearchParams();
                        batchIds.forEach(id => params.append('ids[]', id));

                        fetch(`{{ route('purchase-requests.batch-status') }}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success && res.batches) {
                                // Find newly finished ones to know if we need to reload table
                                const previouslyRunning = this.activeBatches.length;
                                this.activeBatches = res.batches;
                                
                                const currentlyRunning = this.activeBatches.filter(b => !b.finished && !b.canceled).length;
                                
                                if (currentlyRunning < previouslyRunning) {
                                    // A batch finished
                                    this.reloadTable();
                                }
                                
                                // Keep only the running/incomplete batches
                                this.activeBatches = this.activeBatches.filter(b => !b.finished && !b.canceled);
                                
                                // Stop polling if none left
                                if (this.activeBatches.length === 0) {
                                    clearInterval(this.pollingInterval);
                                    this.pollingInterval = null;
                                    Swal.fire({
                                        title: 'Batch Complete',
                                        text: 'Your background tasks have finished processing.',
                                        icon: 'success',
                                        toast: true,
                                        position: 'top-end',
                                        timer: 4000,
                                        showConfirmButton: false
                                    });
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Polling error', err);
                        });
                    }, 2000);
                },

                resetFilters() {
                    this.filters.status = '';
                    this.filters.department = '';
                    this.clearDate();
                },
                
                clearDate() {
                    this.filters.date = '';
                    if (this.fp) {
                        this.fp.clear();
                    }
                    this.reloadTable();
                },

                cancelReject() {
                    this.showRejectReason = false;
                    this.rejectionReason = '';
                },

                confirmBatchApprove(url) {
                    if(this.selectedIds.length === 0) return;

                    Swal.fire({
                        title: 'Approve Selected?',
                        text: `You are about to approve ${this.selectedIds.length} purchase request(s).`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#059669', // emerald-600
                        cancelButtonColor: '#64748b',  // slate-500
                        confirmButtonText: 'Yes, approve them'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.performBulkAction(url, { ids: this.selectedIds });
                        }
                    });
                },

                confirmBatchReject(url) {
                    if(this.selectedIds.length === 0) return;
                    
                    const reason = this.rejectionReason.trim();
                    if(!reason) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Reason Required',
                            text: 'Please provide a reason for rejecting the selected requests.'
                        });
                        return;
                    }

                    this.performBulkAction(url, { ids: this.selectedIds, rejection_reason: reason }, () => {
                        this.cancelReject();
                    });
                },

                performBulkAction(url, data, successCb) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we process your request.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
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
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Task Queued!', text: res.message, timer: 2000, showConfirmButton: false });
                            if(successCb) successCb();
                            this.selectedIds = [];
                            
                            if (res.batch_id) {
                                this.activeBatches.push({ 
                                    id: res.batch_id, 
                                    progress: 0, 
                                    totalJobs: this.selectedIds.length || '...', 
                                    pendingJobs: this.selectedIds.length || '...' 
                                });
                                this.startBatchPolling();
                            } else {
                                this.reloadTable();
                            }
                        } else {
                            throw new Error(res.message || 'An error occurred during processing.');
                        }
                    })
                    .catch(err => {
                        console.error('Action failed:', err);
                        Swal.fire({ icon: 'error', title: 'Oops...', text: err.message || 'Something went wrong!' });
                    });
                }
            };
        };
    </script>

    {{-- Quick-View Action Helpers --}}
    {{-- These MUST be in a plain <script> block (not inside alpine:init) so they are --}}
    {{-- available immediately — onclick="window.qv*" calls happen before Alpine fires. --}}
    <script>
        window.qvPost = function(url, body, csrfToken) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            }).then(function(res) {
                return res.json().then(function(data) { return { ok: res.ok, data: data }; });
            });
        };

        window.qvOnSuccess = function(message) {
            window.dispatchEvent(new CustomEvent('qv-action-success'));
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Done!',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    timer: 3500,
                    showConfirmButton: false,
                });
            }
        };

        window.qvOnError = function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Action Failed',
                    text: message || 'An unexpected error occurred.',
                    customClass: { popup: 'rounded-2xl' },
                });
            } else {
                alert(message || 'An unexpected error occurred.');
            }
        };

        window.qvPromptApprove = function(approveUrl, csrfToken) {
            if (typeof Swal === 'undefined') { return; }
            Swal.fire({
                title: 'Approve Request?',
                html: '<p class="text-sm text-slate-600">This will <strong>auto-approve all pending items</strong> for the current step and advance the workflow.</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Approve',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-4 py-2 font-bold',
                    cancelButton: 'rounded-xl px-4 py-2 font-bold',
                },
            }).then(function(result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                window.qvPost(approveUrl, { auto_approve_items: true }, csrfToken)
                    .then(function(res) {
                        if (res.data && res.data.success) {
                            window.qvOnSuccess(res.data.message);
                        } else {
                            window.qvOnError(res.data ? res.data.message : null);
                        }
                    })
                    .catch(function() { window.qvOnError('Connection error. Please try again.'); });
            });
        };

        window.qvPromptReject = function(rejectUrl, csrfToken) {
            if (typeof Swal === 'undefined') { return; }
            Swal.fire({
                title: 'Reject Request',
                input: 'textarea',
                inputLabel: 'Rejection Reason (Required)',
                inputPlaceholder: 'Provide clear remarks detailing why this is rejected...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Submit Rejection',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-4 py-2 font-bold',
                    cancelButton: 'rounded-xl px-4 py-2 font-bold',
                    input: 'rounded-xl border-slate-300',
                },
                inputValidator: function(value) {
                    if (!value || value.trim().length < 5) {
                        return 'A valid reason (min 5 characters) is mandatory.';
                    }
                },
            }).then(function(result) {
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                window.qvPost(rejectUrl, { remarks: result.value }, csrfToken)
                    .then(function(res) {
                        if (res.data && res.data.success) {
                            window.qvOnSuccess(res.data.message);
                        } else {
                            window.qvOnError(res.data ? res.data.message : null);
                        }
                    })
                    .catch(function() { window.qvOnError('Connection error. Please try again.'); });
            });
        };
    </script>
@endpush
