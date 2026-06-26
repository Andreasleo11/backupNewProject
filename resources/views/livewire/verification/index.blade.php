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
        <div>
            <a href="{{ route('verification.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="bi bi-plus-lg"></i> New Report
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-slate-400 text-xs"></i>
            </div>
            <input type="text" 
                   class="block w-full text-xs pl-9 bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400" 
                   placeholder="Search document #, customer, or invoice…" 
                   wire:model.live.debounce.300ms="search">
        </div>

        <!-- Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach (['all', 'DRAFT', 'IN_REVIEW', 'APPROVED', 'REJECTED'] as $st)
                @php
                    $isActive = $status === $st;
                    $pillLabel = $st === 'all' ? 'All' : str_replace('_', ' ', ucwords(strtolower($st), '_'));
                    $pillColor = $isActive 
                        ? 'bg-indigo-50 text-indigo-700 border-indigo-200 shadow-2xs' 
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
                @endphp
                <button type="button" 
                        wire:click="$set('status', '{{ $st }}')"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $pillColor }}">
                    {{ $pillLabel }}
                </button>
            @endforeach
        </div>
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
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse align-middle">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 font-semibold w-12 text-center">#</th>
                        <th class="px-5 py-3 font-semibold">Doc No</th>
                        <th class="px-5 py-3 font-semibold">Customer</th>
                        <th class="px-5 py-3 font-semibold">Invoice</th>
                        <th class="px-4 py-3 text-center font-semibold">Rec Date</th>
                        <th class="px-4 py-3 text-center font-semibold">Verify Date</th>
                        <th class="px-5 py-3 text-end font-semibold">Total Value</th>
                        <th class="px-5 py-3 text-center font-semibold">Status</th>
                        <th class="px-5 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reports as $r)
                        @php
                            $rowNum = $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage();
                            $statusColor = [
                                'DRAFT' => 'bg-slate-100 text-slate-800 border-slate-200',
                                'IN_REVIEW' => 'bg-amber-50 text-amber-800 border-amber-200/60',
                                'APPROVED' => 'bg-emerald-50 text-emerald-800 border-emerald-200/60',
                                'REJECTED' => 'bg-rose-50 text-rose-800 border-rose-200/60',
                            ][$r->status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                        @endphp
                        <tr class="hover:bg-slate-50/50 text-xs text-slate-700 transition cursor-pointer" onclick="window.location='{{ route('verification.show', $r->id) }}'">
                            <td class="px-5 py-3.5 text-center font-mono text-slate-400 font-bold w-12">{{ $rowNum }}</td>
                            <td class="px-5 py-3.5 font-semibold text-indigo-600 hover:text-indigo-950">
                                <a href="{{ route('verification.show', $r->id) }}" onclick="event.stopPropagation();">
                                    {{ $r->document_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-900 truncate max-w-[160px]" title="{{ $r->customer }}">
                                {{ $r->customer ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-600">{{ $r->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center font-mono">
                                {{ optional($r->rec_date)?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono">
                                {{ optional($r->verify_date)?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ number_format($r->total_value ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $statusColor }}">
                                    {{ str_replace('_', ' ', ucwords(strtolower($r->status), '_')) }}
                                 </span>
                            </td>
                            <td class="px-5 py-3.5 text-end">
                                <a class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-indigo-600 text-xs font-semibold rounded-lg shadow-sm transition" 
                                   href="{{ route('verification.show', $r->id) }}"
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
