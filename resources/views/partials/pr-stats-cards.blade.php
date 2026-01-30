{{--
    Purchase Request Stats Cards
    Displays key metrics at the top of the index page
    
    Props:
    - $stats: Array with keys: pending_my_approval, in_review, approved_this_month, total_value_pending
--}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    {{-- Pending My Approval --}}
    <a href="{{ route('purchase-requests.index') }}?filter=my_approval"
       class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm transition-all hover:shadow-md hover:border-amber-300">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-amber-700 uppercase tracking-wide">Pending My Approval</p>
                <p class="mt-2 text-3xl font-bold text-amber-900">{{ $stats['pending_my_approval'] }}</p>
                <p class="mt-1 text-[11px] text-amber-600">
                    Requires your action
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 group-hover:bg-amber-200 transition-colors">
                <i class='bx bx-bell-ring text-2xl text-amber-600'></i>
            </div>
        </div>
        <div class="absolute bottom-0 right-0 opacity-10">
            <i class='bx bx-time text-[120px] text-amber-600'></i>
        </div>
    </a>

    {{-- In Review --}}
    <a href="{{ route('purchase-requests.index') }}?filter=in_review"
       class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-blue-50 to-white p-6 shadow-sm transition-all hover:shadow-md hover:border-blue-300">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wide">In Review</p>
                <p class="mt-2 text-3xl font-bold text-blue-900">{{ $stats['in_review'] }}</p>
                <p class="mt-1 text-[11px] text-blue-600">
                    Awaiting approval
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 group-hover:bg-blue-200 transition-colors">
                <i class='bx bx-loader-alt text-2xl text-blue-600'></i>
            </div>
        </div>
        <div class="absolute bottom-0 right-0 opacity-10">
            <i class='bx bx-refresh text-[120px] text-blue-600'></i>
        </div>
    </a>

    {{-- Approved This Month --}}
    <a href="{{ route('purchase-requests.index') }}?filter=approved_month"
       class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm transition-all hover:shadow-md hover:border-emerald-300">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Approved This Month</p>
                <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $stats['approved_this_month'] }}</p>
                <p class="mt-1 text-[11px] text-emerald-600">
                    {{ now()->format('F Y') }}
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 group-hover:bg-emerald-200 transition-colors">
                <i class='bx bx-check-circle text-2xl text-emerald-600'></i>
            </div>
        </div>
        <div class="absolute bottom-0 right-0 opacity-10">
            <i class='bx bx-badge-check text-[120px] text-emerald-600'></i>
        </div>
    </a>

    {{-- Total Value Pending --}}
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-50 to-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-xs font-medium text-indigo-700 uppercase tracking-wide">Total Value Pending</p>
                <p class="mt-2 text-2xl font-bold text-indigo-900">
                    ${{ number_format($stats['total_value_pending'], 0) }}
                </p>
                <p class="mt-1 text-[11px] text-indigo-600">
                    Approximate total
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 group-hover:bg-indigo-200 transition-colors">
                <i class='bx bx-dollar-circle text-2xl text-indigo-600'></i>
            </div>
        </div>
        <div class="absolute bottom-0 right-0 opacity-10">
            <i class='bx bx-money text-[120px] text-indigo-600'></i>
        </div>
    </div>
</div>
