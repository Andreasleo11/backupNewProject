{{--
    Purchase Request Stats Cards
    Displays key metrics at the top of the index page
    
    Props:
    - $stats: Array with keys: pending_my_approval, in_review, approved_this_month, total_value_pending
--}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Pending My Approval --}}
    <a href="{{ route('purchase-requests.index') }}?filter=my_approval"
       class="glass-card group relative overflow-hidden p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-amber-300/50">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-50/50 to-transparent opacity-50 transition-opacity group-hover:opacity-100"></div>
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Pending Action</p>
                <p class="mt-2 text-3xl font-black text-slate-800 group-hover:text-amber-700 transition-colors">
                    {{ $stats['pending_my_approval'] }}
                </p>
                <p class="mt-1 text-[11px] font-medium text-slate-500">
                    Requires your approval
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 shadow-sm transition-all group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white">
                <i class='bx bx-bell-ring text-2xl'></i>
            </div>
        </div>
        {{-- Progress bar decoration --}}
        <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-100">
            <div class="h-full bg-amber-400" style="width: {{ min($stats['pending_my_approval'] * 10, 100) }}%"></div>
        </div>
    </a>

    {{-- In Review --}}
    <a href="{{ route('purchase-requests.index') }}?filter=in_review"
       class="glass-card group relative overflow-hidden p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-300/50">
       <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent opacity-50 transition-opacity group-hover:opacity-100"></div>
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">In Review</p>
                <p class="mt-2 text-3xl font-black text-slate-800 group-hover:text-blue-700 transition-colors">
                    {{ $stats['in_review'] }}
                </p>
                <p class="mt-1 text-[11px] font-medium text-slate-500">
                    Awaiting approval chain
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 shadow-sm transition-all group-hover:scale-110 group-hover:bg-blue-500 group-hover:text-white">
                <i class='bx bx-loader-alt text-2xl'></i>
            </div>
        </div>
    </a>

    {{-- Approved This Month --}}
    <a href="{{ route('purchase-requests.index') }}?filter=approved_month"
       class="glass-card group relative overflow-hidden p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300/50">
       <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-transparent opacity-50 transition-opacity group-hover:opacity-100"></div>
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Approved ({{ now()->format('M') }})</p>
                <p class="mt-2 text-3xl font-black text-slate-800 group-hover:text-emerald-700 transition-colors">
                    {{ $stats['approved_this_month'] }}
                </p>
                <p class="mt-1 text-[11px] font-medium text-slate-500">
                    Completed requests
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-sm transition-all group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white">
                <i class='bx bx-check-circle text-2xl'></i>
            </div>
        </div>
    </a>

    {{-- Total Value Pending --}}
    <div class="glass-card group relative overflow-hidden p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-indigo-300/50">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent opacity-50 transition-opacity group-hover:opacity-100"></div>
        <div class="relative z-10 flex items-start justify-between">
            <div class="flex-1">
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Est. Value Pending</p>
                <p class="mt-2 text-2xl font-black text-slate-800 group-hover:text-indigo-700 transition-colors truncate">
                    ${{ number_format($stats['total_value_pending'], 0) }}
                </p>
                <p class="mt-1 text-[11px] font-medium text-slate-500">
                    Total pipeline value
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 shadow-sm transition-all group-hover:scale-110 group-hover:bg-indigo-500 group-hover:text-white">
                <i class='bx bx-dollar-circle text-2xl'></i>
            </div>
        </div>
    </div>
</div>
