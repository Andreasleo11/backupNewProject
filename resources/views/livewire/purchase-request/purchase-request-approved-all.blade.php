<div class="sm:px-4 lg:px-0 space-y-6" x-data="{
    activeDrawer: null,
    filtersOpen: false,

    // ── Alpine-managed filter state (instant UI, no server round-trip until value committed) ──
    fDept: @js($department),
    fBranch: @js($branch),
    fDateRange: @js($dateRange),

    get activeFilterCount() {
        return [this.fDept, this.fBranch, this.fDateRange].filter(Boolean).length;
    },

    applyFilter(prop, livewireProp, value) {
        this[prop] = value;
        $wire.set(livewireProp, value);
    },

    resetAllFilters() {
        this.fDept = '';
        this.fBranch = '';
        this.fDateRange = '';
        $wire.clearFilters();
        $wire.set('page', 1);
    },
}">

    {{-- MODULE IDENTITY HEADER --}}
    <div class="mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                <i class='bx bx-receipt text-2xl'></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-800">
                    Approved Purchase Requests
                </h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    All Departments
                </p>
            </div>
        </div>

        <div class="flex-1 w-full max-w-2xl relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class='bx bx-search text-slate-400 group-focus-within:text-indigo-500 transition-colors text-xl'></i>
            </div>

            <input type="text" wire:model.live.debounce.400ms="search"
                placeholder="Find PRs, makers, or departments..."
                class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-24 py-3 text-sm font-medium text-slate-800 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-200 transition-all shadow-sm placeholder-slate-400">

            <div class="absolute inset-y-0 right-2 flex items-center gap-1">
                <button type="button" @click="activeDrawer = (activeDrawer === 'filters' ? null : 'filters')"
                    class="h-9 px-3 rounded-xl flex items-center gap-1.5 transition-all text-[10px] font-bold uppercase tracking-tight"
                    :class="activeFilterCount > 0 ? 'bg-indigo-600 text-white shadow-md' :
                        'text-slate-400 hover:bg-slate-100 hover:text-slate-600'">
                    <i class="bi bi-funnel"></i>
                    <span x-show="activeFilterCount > 0" x-text="activeFilterCount"></span>
                </button>
                <select
                    class="h-9 rounded-xl border-slate-200 bg-white text-[10px] font-black text-slate-700 focus:ring-indigo-500"
                    wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        @can('pr.create')
            <a href="{{ route('purchase-requests.create') }}"
                class="h-12 px-6 rounded-2xl bg-indigo-600 flex items-center justify-center gap-2 text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all font-bold text-sm uppercase tracking-widest whitespace-nowrap">
                <i class="bi bi-plus-lg text-lg"></i>
                New Request
            </a>
        @endcan
    </div>



    {{-- ADVANCED FILTERS (Alpine-managed for instant snappy UI) --}}
    <div x-show="activeDrawer === 'filters'" x-collapse x-cloak>
        <div class="mb-6 bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-wrap items-center gap-4">



            {{-- Department Filter --}}
            <div class="w-full sm:w-52">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">To
                    Department</label>
                <div class="relative">
                    <select x-model="fDept" @change="applyFilter('fDept', 'department', fDept)"
                        class="w-full form-select text-xs font-bold bg-white border-slate-200 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 uppercase transition-all cursor-pointer"
                        :class="fDept ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : ''">
                        <option value="">All To Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <span x-show="fDept" @click="applyFilter('fDept', 'department', '')"
                        class="absolute right-7 top-1/2 -translate-y-1/2 cursor-pointer text-indigo-400 hover:text-rose-500 transition-colors text-[10px]">✕</span>
                </div>
            </div>

            {{-- Branch Filter --}}
            <div class="w-full sm:w-44">
                <label
                    class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Branch</label>
                <div class="relative">
                    <select x-model="fBranch" @change="applyFilter('fBranch', 'branch', fBranch)"
                        class="w-full form-select text-xs font-bold bg-white border-slate-200 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 uppercase transition-all cursor-pointer"
                        :class="fBranch ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : ''">
                        <option value="">All Branches</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                    <span x-show="fBranch" @click="applyFilter('fBranch', 'branch', '')"
                        class="absolute right-7 top-1/2 -translate-y-1/2 cursor-pointer text-indigo-400 hover:text-rose-500 transition-colors text-[10px]">✕</span>
                </div>
            </div>

            {{-- Active filter pills --}}
            <div class="flex flex-wrap gap-1.5 flex-1">
                <template x-if="fStatus">
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-tighter">
                        <i class="bx bx-check-circle text-xs"></i>
                        <span x-text="fStatus"></span>
                        <button @click="applyFilter('fStatus', 'status', '')"
                            class="ml-0.5 hover:text-rose-600">✕</button>
                    </span>
                </template>
                <template x-if="fDept">
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-tighter">
                        <i class="bx bx-building text-xs"></i>
                        <span x-text="fDept"></span>
                        <button @click="applyFilter('fDept', 'department', '')"
                            class="ml-0.5 hover:text-rose-600">✕</button>
                    </span>
                </template>
                <template x-if="fBranch">
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-tighter">
                        <i class="bx bx-map-pin text-xs"></i>
                        <span x-text="fBranch"></span>
                        <button @click="applyFilter('fBranch', 'branch', '')"
                            class="ml-0.5 hover:text-rose-600">✕</button>
                    </span>
                </template>
                @if ($page > 1)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-tighter">
                        <i class="bx bx-list-ul text-xs"></i>
                        Page: {{ $page }}
                        <button @click="$wire.set('page', 1)" class="ml-0.5 hover:text-rose-600">✕</button>
                    </span>
                @endif
            </div>

            <button @click="resetAllFilters()"
                class="ml-auto flex items-center gap-1.5 text-[10px] font-bold text-slate-400 hover:text-rose-500 uppercase tracking-widest transition-colors"
                :class="activeFilterCount > 0 ? 'text-rose-400' : ''">
                <i class='bx bx-reset'></i> Reset All
            </button>
        </div>
    </div>



    {{-- THE TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative">
        {{-- Scoped loading dim --}}
        <div wire:loading
            class="absolute inset-0 z-20 bg-white/60 backdrop-blur-[2px] flex items-center justify-center rounded-2xl">
            <div class="flex items-center gap-3 bg-white rounded-2xl px-5 py-3 shadow-xl border border-slate-100">
                <div class="h-5 w-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Loading...</span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100">

                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('doc_num')">
                            Document & Maker
                            @if ($sortField === 'doc_num')
                                <i
                                    class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-500"></i>
                            @endif
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('date_pr')">
                            Requested
                            @if ($sortField === 'date_pr')
                                <i
                                    class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-500"></i>
                            @endif
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('from_department')">
                            Items & Routing
                            @if ($sortField === 'from_department')
                                <i
                                    class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-500"></i>
                            @endif
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('supplier')">
                            Supplier
                            @if ($sortField === 'supplier')
                                <i
                                    class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-500"></i>
                            @endif
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('po_number')">
                            PO Number
                            @if ($sortField === 'po_number')
                                <i
                                    class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-indigo-500"></i>
                            @endif
                        </th>
                        <th
                            class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            Status</th>
                        <th
                            class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $row)
                        <tr wire:key="row-{{ $row->id }}" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-bold text-slate-900 tracking-tight">{{ $row->pr_no ?: 'No PR Num' }}</span>
                                        <span
                                            class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold border border-slate-200 uppercase">{{ $row->branch->value ?? ($row->branch ?? 'HQ') }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                                        <i class="bx bx-user text-xs"></i>
                                        <span>{{ $row->createdBy->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 text-xs">
                                    {{ \Carbon\Carbon::parse($row->date_pr)->diffForHumans() }}</div>
                                <div class="text-[9px] text-slate-400">
                                    {{ \Carbon\Carbon::parse($row->date_pr)->format('d-m-Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 w-fit">
                                        {{ $row->items_count }} Items
                                    </span>
                                    <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">
                                        <span class="text-slate-500">{{ $row->from_department }}</span>
                                        <i class="bx bx-right-arrow-alt mx-0.5"></i>
                                        <span
                                            class="text-indigo-600 font-semibold">{{ $row->to_department->value ?? $row->to_department }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-slate-700 line-clamp-1" title="{{ $row->supplier }}">
                                    {{ $row->supplier ?: 'Not Specified' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-slate-700 line-clamp-1" title="{{ $row->po_number }}">
                                    {{ $row->po_number ?: '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @include('partials.workflow-status-badge', ['pr' => $row])
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="$dispatch('open-quick-view-modal', { id: {{ $row->id }} })"
                                    class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all hover:scale-110 active:scale-95 flex items-center justify-center">
                                    <i class="bx bx-show text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div
                                        class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                                        <i class="bx bx-search-alt text-3xl opacity-50"></i>
                                    </div>
                                    <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No matching
                                        requests found</h5>
                                    <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">
                                        We couldn't find any purchase requests for the current filters or your
                                        visibility scope.
                                    </p>

                                    @if ($search || $status || $department || $dateRange || $branch || $preset !== 'all')
                                        <button wire:click="resetFilters"
                                            class="mt-6 px-5 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all hover:scale-105 active:scale-95 shadow-sm">
                                            Clear all filters
                                        </button>
                                    @elseif(auth()->user()->hasRole('super-admin'))
                                        <div
                                            class="mt-8 p-3 bg-amber-50 rounded-xl border border-amber-100 text-[10px] text-amber-700 font-bold flex items-start gap-2 text-left">
                                            <i class="bx bx-info-circle text-base"></i>
                                            <div>
                                                SYSTEM NOTE: Total records exist in DB
                                                ({{ \App\Models\PurchaseRequest::count() }}), but your current scope or
                                                filters returned zero.
                                                Ensure records have a valid creator or approval workflow.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }} total
            </div>
            <div>
                {{ $rows->links() }}
            </div>
        </div>
    </div>

    @push('modals')
        @livewire('purchase-request.quick-view')
    @endpush
</div>
