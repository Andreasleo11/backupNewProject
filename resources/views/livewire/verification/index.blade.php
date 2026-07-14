<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-200">
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verification Reports</h1>
            <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ url('/') }}" class="hover:text-slate-800 transition">Home</a>
                <span class="text-slate-300">•</span>
                <span class="text-slate-800 font-medium">Verification Reports</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- Export Dropdown -->
            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button type="button" 
                        @click="open = !open" 
                        @click.outside="open = false"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition duration-150">
                    <i class="bi bi-file-earmark-excel text-emerald-600"></i> Export Excel <i class="bi bi-chevron-down text-[10px]"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-75" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95" 
                     class="absolute right-0 mt-1.5 w-48 origin-top-right rounded-lg bg-white shadow-lg border border-slate-100 focus:outline-hidden z-30 py-1"
                     style="display: none;">
                    <a href="{{ route('verification.export') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                        <i class="bi bi-list-task text-slate-400"></i> Export All Reports
                    </a>
                    <a href="{{ route('verification.export.monthly', ['month' => now()->month, 'year' => now()->year]) }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                        <i class="bi bi-calendar-event text-slate-400"></i> Export Current Month
                    </a>
                </div>
            </div>

            <a href="{{ route('verification.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="bi bi-plus-lg"></i> New Report
            </a>
        </div>
    </div>

    {{-- Status Statistics Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- ALL CARD -->
        <button type="button" 
                wire:click="$set('status', 'all')"
                class="flex flex-col p-4 bg-white rounded-xl border {{ $status === 'all' ? 'border-indigo-500 ring-2 ring-indigo-550/20' : 'border-slate-200 shadow-xs' }} text-left transition duration-150 hover:shadow-md">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">All Reports</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900">{{ $statusCounts['ALL'] }}</span>
                <span class="text-xs font-semibold text-slate-400">total</span>
            </div>
        </button>

        <!-- DRAFT CARD -->
        <button type="button" 
                wire:click="$set('status', 'DRAFT')"
                class="flex flex-col p-4 bg-white rounded-xl border {{ $status === 'DRAFT' ? 'border-slate-500 ring-2 ring-slate-500/20' : 'border-slate-200 shadow-xs' }} text-left transition duration-150 hover:shadow-md">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Drafts</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-700">{{ $statusCounts['DRAFT'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-700">DRAFT</span>
            </div>
        </button>

        <!-- IN REVIEW CARD -->
        <button type="button" 
                wire:click="$set('status', 'IN_REVIEW')"
                class="flex flex-col p-4 bg-white rounded-xl border {{ $status === 'IN_REVIEW' ? 'border-amber-500 ring-2 ring-amber-550/20' : 'border-slate-200 shadow-xs' }} text-left transition duration-150 hover:shadow-md">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">In Review</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-600">{{ $statusCounts['IN_REVIEW'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-800">REVIEW</span>
            </div>
        </button>

        <!-- APPROVED CARD -->
        <button type="button" 
                wire:click="$set('status', 'APPROVED')"
                class="flex flex-col p-4 bg-white rounded-xl border {{ $status === 'APPROVED' ? 'border-emerald-500 ring-2 ring-emerald-550/20' : 'border-slate-200 shadow-xs' }} text-left transition duration-150 hover:shadow-md">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Approved</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-600">{{ $statusCounts['APPROVED'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-800">PASSED</span>
            </div>
        </button>

        <!-- REJECTED CARD -->
        <button type="button" 
                wire:click="$set('status', 'REJECTED')"
                class="flex flex-col p-4 bg-white rounded-xl border {{ $status === 'REJECTED' ? 'border-rose-500 ring-2 ring-rose-550/20' : 'border-slate-200 shadow-xs' }} text-left transition duration-150 hover:shadow-md col-span-2 lg:col-span-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rejected</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-rose-600">{{ $statusCounts['REJECTED'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-800">FAILED</span>
            </div>
        </button>
    </div>

    @if(auth()->check() && auth()->user()->hasRole('super-admin'))
        {{-- Advanced Filters Panel for Superuser --}}
        <div x-data="{ showAdvanced: false }" class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
            <div class="flex items-center justify-between cursor-pointer" @click="showAdvanced = !showAdvanced">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5 select-none">
                    <i class="bi bi-sliders text-indigo-500"></i> Advanced Filters
                </h4>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="bi" :class="showAdvanced ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
            
            <div x-show="showAdvanced" x-collapse class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-slate-100" style="display: none;" x-cloak>
                <!-- Department Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Department</label>
                    <select wire:model.live="filterDept" class="block w-full text-xs bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-slate-700">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Creator Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Created By</label>
                    <select wire:model.live="filterCreator" class="block w-full text-xs bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-slate-700">
                        <option value="">All Users</option>
                        @foreach($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Rec Date Range (Start) -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Rec Date From</label>
                    <input type="date" wire:model.live="filterRecStart" class="block w-full text-xs bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-slate-700">
                </div>

                <!-- Rec Date Range (End) -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Rec Date To</label>
                    <input type="date" wire:model.live="filterRecEnd" class="block w-full text-xs bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-slate-700">
                </div>
            </div>
        </div>
    @endif

    {{-- Search & Active Filters --}}
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-slate-400 text-xs"></i>
            </div>
            <input type="text" 
                   class="block w-full text-xs pl-9 pr-8 bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 shadow-2xs" 
                   placeholder="Search document #, customer, or invoice…" 
                   wire:model.live.debounce.300ms="search">
            @if ($search)
                <button type="button" 
                        wire:click="$set('search', '')" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-450 hover:text-slate-700 transition-colors">
                    <i class="bi bi-x-circle-fill text-xs"></i>
                </button>
            @endif
        </div>

        <!-- Active Filter Badges -->
        @if ($status !== 'all' || $search || $filterDept || $filterCreator || $filterRecStart || $filterRecEnd)
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-400 font-medium">Active Filters:</span>
                @if ($status !== 'all')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        Status: {{ str_replace('_', ' ', ucwords(strtolower($status), '_')) }}
                        <button type="button" wire:click="$set('status', 'all')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                @if ($search)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        Search: "{{ $search }}"
                        <button type="button" wire:click="$set('search', '')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                @if ($filterDept)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        Dept: {{ $filterDept }}
                        <button type="button" wire:click="$set('filterDept', '')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                @if ($filterCreator)
                    @php
                        $creatorName = collect($users)->firstWhere('id', $filterCreator)?->name ?? 'User';
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        Maker: {{ $creatorName }}
                        <button type="button" wire:click="$set('filterCreator', '')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                @if ($filterRecStart)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        From: {{ \Carbon\Carbon::parse($filterRecStart)->format('d M Y') }}
                        <button type="button" wire:click="$set('filterRecStart', '')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                @if ($filterRecEnd)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        To: {{ \Carbon\Carbon::parse($filterRecEnd)->format('d M Y') }}
                        <button type="button" wire:click="$set('filterRecEnd', '')" class="hover:text-indigo-900 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif
                <button type="button" 
                        wire:click="$set('status', 'all'); $set('search', ''); $set('filterDept', ''); $set('filterCreator', ''); $set('filterRecStart', ''); $set('filterRecEnd', '');"
                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold transition ml-1">
                    Clear All
                </button>
            </div>
        @endif
    </div>

    {{-- Registry Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Table Header Summary -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/40">
            <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                <i class="bi bi-file-earmark-text text-slate-500"></i> Report Registry
            </h3>
            <div class="text-xs text-slate-500 font-medium">
                Showing <span class="font-bold text-slate-800">{{ $reports->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-800">{{ $reports->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-800">{{ $reports->total() }}</span> reports
            </div>
        </div>

        <!-- Responsive Table Wrapper -->
        <div class="overflow-x-auto relative">
            {{-- Loading Skeleton Overlay --}}
            <div wire:loading wire:target="status, search, filterDept, filterCreator, filterRecStart, filterRecEnd, sortBy, gotoPage, nextPage, previousPage" class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center z-10 transition-opacity">
                <div class="flex items-center gap-2 text-indigo-600 font-semibold text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-ping"></span>
                    Loading Registry Data...
                </div>
            </div>

            <table class="w-full text-left border-collapse align-middle">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider select-none">
                        <th class="px-4 py-3 font-semibold w-12 text-center cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150" wire:click="sortBy('id')">
                            ID
                            @if($sortField === 'id')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150" wire:click="sortBy('document_number')">
                            Doc No
                            @if($sortField === 'document_number')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150" wire:click="sortBy('customer')">
                            Customer
                            @if($sortField === 'customer')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150 hidden md:table-cell" wire:click="sortBy('invoice_number')">
                            Invoice
                            @if($sortField === 'invoice_number')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150 hidden lg:table-cell" wire:click="sortBy('rec_date')">
                            Rec Date
                            @if($sortField === 'rec_date')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150 hidden lg:table-cell" wire:click="sortBy('verify_date')">
                            Verify Date
                            @if($sortField === 'verify_date')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-end font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150" wire:click="sortBy('total_value')">
                            Total Value
                            @if($sortField === 'total_value')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-center font-semibold cursor-pointer hover:bg-slate-100 hover:text-slate-700 transition duration-150" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                                <i class="bi {{ $sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }} ml-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-slate-300 ml-1"></i>
                            @endif
                        </th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reports as $r)
                        @php
                            $statusColor = [
                                'DRAFT' => 'bg-slate-100 text-slate-800 border-slate-200',
                                'IN_REVIEW' => 'bg-amber-50 text-amber-800 border-amber-200/60',
                                'APPROVED' => 'bg-emerald-50 text-emerald-800 border-emerald-200/60',
                                'REJECTED' => 'bg-rose-50 text-rose-800 border-rose-200/60',
                            ][$r->status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                        @endphp
                        <tr class="hover:bg-slate-50/50 text-xs text-slate-700 transition cursor-pointer border-l-2 border-transparent hover:border-indigo-500" 
                            x-data
                            @click="Livewire.navigate('{{ route('verification.show', $r->id) }}')">
                            <td class="px-4 py-3.5 text-center font-mono text-slate-400 font-bold w-12">{{ $r->id }}</td>
                            <td class="px-4 py-3.5 font-semibold text-indigo-600 hover:text-indigo-950">
                                <a href="{{ route('verification.show', $r->id) }}" wire:navigate onclick="event.stopPropagation();">
                                    {{ $r->document_number }}
                                </a>
                                {{-- Mobile sub-details to prevent horizontal scroll --}}
                                <div class="lg:hidden text-[10px] text-slate-400 font-medium mt-0.5 space-y-0.5">
                                    <div class="md:hidden">Inv: <span class="text-slate-600 font-mono">{{ $r->invoice_number ?? '—' }}</span></div>
                                    <div>Rec: <span class="text-slate-600 font-mono">{{ optional($r->rec_date)?->format('d M y') ?? '—' }}</span></div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-900 truncate max-w-[120px] sm:max-w-[160px]" title="{{ $r->customer }}">
                                {{ $r->customer ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 hidden md:table-cell">{{ $r->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center font-mono hidden lg:table-cell">
                                {{ optional($r->rec_date)?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono hidden lg:table-cell">
                                {{ optional($r->verify_date)?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ number_format($r->total_value ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $statusColor }}">
                                    {{ str_replace('_', ' ', ucwords(strtolower($r->status), '_')) }}
                                 </span>
                            </td>
                            <td class="px-4 py-3.5 text-end">
                                <a class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-indigo-600 text-xs font-semibold rounded-lg shadow-sm transition" 
                                   href="{{ route('verification.show', $r->id) }}"
                                   wire:navigate
                                   onclick="event.stopPropagation();">
                                    Open <i class="bi bi-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center py-4">
                                    <i class="bi bi-file-earmark-x text-4xl text-slate-300 mb-2.5"></i>
                                    <span class="text-sm font-semibold text-slate-700">No reports found</span>
                                    <p class="text-xs text-slate-400 mt-1 max-w-xs">No verification reports match your current filter selection or search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $reports->links() }}
    </div>
</div>
