<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verification Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Overview of all verification reports</p>
        </div>
        <a href="{{ route('verification.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="bi bi-list-ul"></i> All Reports
        </a>
    </div>

    {{-- Status Count Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total --}}
        <a href="{{ route('verification.index') }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition group">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Reports</div>
            <div class="text-3xl font-bold text-slate-900">{{ $total }}</div>
            <div class="mt-2 text-xs text-slate-500 group-hover:text-indigo-600 transition">View all →</div>
        </a>

        {{-- Draft --}}
        <a href="{{ route('verification.index', ['status' => 'DRAFT']) }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition group">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Draft</div>
            <div class="text-3xl font-bold text-slate-700">{{ $draft }}</div>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5">
                <div class="bg-slate-400 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($draft/$total*100) : 0 }}%"></div>
            </div>
        </a>

        {{-- In Review --}}
        <a href="{{ route('verification.index', ['status' => 'IN_REVIEW']) }}"
           class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 hover:shadow-md transition group">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-1">In Review</div>
            <div class="text-3xl font-bold text-amber-700">{{ $inReview }}</div>
            <div class="mt-2 w-full bg-amber-100 rounded-full h-1.5">
                <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($inReview/$total*100) : 0 }}%"></div>
            </div>
        </a>

        {{-- Approved --}}
        <a href="{{ route('verification.index', ['status' => 'APPROVED']) }}"
           class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 hover:shadow-md transition group">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Approved</div>
            <div class="text-3xl font-bold text-emerald-700">{{ $approved }}</div>
            <div class="mt-2 w-full bg-emerald-100 rounded-full h-1.5">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $total > 0 ? round($approved/$total*100) : 0 }}%"></div>
            </div>
        </a>
    </div>

    {{-- Rejected row --}}
    @if($rejected > 0)
    <div>
        <a href="{{ route('verification.index', ['status' => 'REJECTED']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 text-xs font-semibold hover:bg-rose-100 transition">
            <i class="bi bi-x-circle"></i>
            {{ $rejected }} report{{ $rejected !== 1 ? 's' : '' }} rejected — click to review
        </a>
    </div>
    @endif

    {{-- Recent Reports --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/40">
            <h3 class="text-sm font-semibold text-slate-800">Recent Reports</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($recent as $r)
                @php
                    $statusColor = [
                        'DRAFT'     => 'bg-slate-100 text-slate-700',
                        'IN_REVIEW' => 'bg-amber-50 text-amber-800',
                        'APPROVED'  => 'bg-emerald-50 text-emerald-800',
                        'REJECTED'  => 'bg-rose-50 text-rose-800',
                    ][$r->status] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <a href="{{ route('verification.show', $r->id) }}"
                   class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs font-mono font-semibold text-slate-600 shrink-0">{{ $r->document_number }}</span>
                        <span class="text-xs text-slate-500 truncate">{{ $r->customer }}</span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="text-[10px] text-slate-400">{{ $r->created_at->format('d M Y') }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusColor }}">
                            {{ str_replace('_', ' ', $r->status) }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="px-5 py-6 text-center text-xs text-slate-400">No reports yet.</div>
            @endforelse
        </div>
    </div>
</div>
