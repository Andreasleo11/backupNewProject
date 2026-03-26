{{--
    Purchase Request Stats Cards
    Displays key metrics at the top of the index page
    
    Props:
    - $stats: Array with keys: pending_my_approval, in_review, approved_this_month, total_value_pending
--}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Pending My Approval --}}
    @php $isActive = request('filter') === 'my_approval'; @endphp
    <a href="{{ route('purchase-requests.index') }}?filter=my_approval"
       class="group relative overflow-hidden rounded-2xl bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $isActive ? 'border-amber-400 shadow-lg shadow-amber-100/50' : 'border-slate-100 shadow-sm hover:border-amber-200' }}">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-50/80 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 {{ $isActive ? 'opacity-100' : '' }}"></div>
        
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-[10px] font-black tracking-widest uppercase {{ $isActive ? 'text-amber-600' : 'text-slate-500 group-hover:text-amber-600' }} transition-colors">Pending Action</p>
                <p class="mt-2.5 text-3xl font-black text-slate-800 tracking-tight group-hover:text-amber-700 transition-colors">
                    {{ $stats['pending_my_approval'] }}
                </p>
                <p class="mt-1 text-[11px] font-semibold text-slate-400">
                    Requires your approval
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 {{ $isActive ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-amber-50 text-amber-500 group-hover:bg-amber-500 group-hover:text-white' }}">
                <i class='bx bx-bell text-2xl'></i>
            </div>
        </div>
        
        {{-- Progress bar decoration --}}
        <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-50">
            <div class="h-full bg-gradient-to-r from-amber-400 to-orange-400 transition-all duration-1000 ease-out" 
                 style="width: {{ min($stats['pending_my_approval'] * 10, 100) }}%"></div>
        </div>
    </a>

    {{-- In Review --}}
    @php $isActive = request('filter') === 'in_review'; @endphp
    <a href="{{ route('purchase-requests.index') }}?filter=in_review"
       class="group relative overflow-hidden rounded-2xl bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $isActive ? 'border-blue-400 shadow-lg shadow-blue-100/50' : 'border-slate-100 shadow-sm hover:border-blue-200' }}">
       <div class="absolute inset-0 bg-gradient-to-br from-blue-50/80 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 {{ $isActive ? 'opacity-100' : '' }}"></div>
       
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-[10px] font-black tracking-widest uppercase {{ $isActive ? 'text-blue-600' : 'text-slate-500 group-hover:text-blue-600' }} transition-colors">In Review</p>
                <p class="mt-2.5 text-3xl font-black text-slate-800 tracking-tight group-hover:text-blue-700 transition-colors">
                    {{ $stats['in_review'] }}
                </p>
                <p class="mt-1 text-[11px] font-semibold text-slate-400">
                    Awaiting approval chain
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 {{ $isActive ? 'bg-blue-500 text-white shadow-blue-200' : 'bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white' }}">
                <i class='bx bx-loader-alt text-2xl animate-spin-slow'></i>
            </div>
        </div>
    </a>

    {{-- Approved This Month --}}
    @php $isActive = request('filter') === 'approved_month'; @endphp
    <a href="{{ route('purchase-requests.index') }}?filter=approved_month"
       class="group relative overflow-hidden rounded-2xl bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $isActive ? 'border-emerald-400 shadow-lg shadow-emerald-100/50' : 'border-slate-100 shadow-sm hover:border-emerald-200' }}">
       <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/80 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 {{ $isActive ? 'opacity-100' : '' }}"></div>
       
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <p class="text-[10px] font-black tracking-widest uppercase {{ $isActive ? 'text-emerald-600' : 'text-slate-500 group-hover:text-emerald-600' }} transition-colors">Approved ({{ now()->format('M') }})</p>
                <p class="mt-2.5 text-3xl font-black text-slate-800 tracking-tight group-hover:text-emerald-700 transition-colors">
                    {{ $stats['approved_this_month'] }}
                </p>
                <p class="mt-1 text-[11px] font-semibold text-slate-400">
                    Completed requests
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 {{ $isActive ? 'bg-emerald-500 text-white shadow-emerald-200' : 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white' }}">
                <i class='bx bx-check-double text-2xl'></i>
            </div>
        </div>
    </a>

    {{-- Total Value Pending --}}
    <div class="group relative overflow-hidden rounded-2xl bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border border-slate-100 shadow-sm hover:border-indigo-200">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/80 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
        
        <div class="relative z-10 flex items-start justify-between">
            <div class="flex-1">
                <p class="text-[10px] font-black tracking-widest uppercase text-slate-500 group-hover:text-indigo-600 transition-colors">Est. Value Pending</p>
                <div class="mt-2.5 text-[1.35rem] leading-tight font-black text-slate-800 tracking-tight group-hover:text-indigo-700 transition-colors">
                    @foreach($stats['total_value_pending'] ?? ['IDR' => 0] as $currency => $amount)
                        <div class="truncate flex items-baseline gap-1.5">
                            <span class="text-sm text-slate-400 font-bold">{{ $currency }}</span>
                            <span>{{ number_format($amount, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:bg-indigo-500 group-hover:text-white">
                <i class='bx bx-pie-chart-alt text-2xl'></i>
            </div>
        </div>
    </div>
</div>
