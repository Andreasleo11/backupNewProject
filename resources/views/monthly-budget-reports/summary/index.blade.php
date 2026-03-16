@extends('new.layouts.app')

@section('title', 'Budget Summary Reports')
@section('page-title', 'Summary Reports')
@section('page-subtitle', 'Consolidated monthly budget summary and momentum analysis.')

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-4">
            @php
                $showGenerateButton = false;
                if (!$authUser->is_head && !$authUser->is_gm && $authUser->department?->name !== 'MANAGEMENT') {
                    $showGenerateButton = true;
                }
            @endphp
            @if ($showGenerateButton)
                <form action="{{ route('monthly.budget.summary.report.store') }}" method="post"
                    class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">
                    <div class="relative">
                        <input type="text" id="monthPicker" name="month"
                            class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 min-w-[180px]"
                            placeholder="Select Month" required>
                        <i class="bx bx-calendar absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-bold text-white shadow-xl shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:scale-[1.02] active:scale-95">
                        <i class="bx bx-refresh text-[1rem]"></i>
                        Generate Summary
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-16">#</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Doc. Number</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Report Date</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total Amount</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Momentum</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($reports as $report)
                            @php
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');
                            @endphp
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-center text-xs font-bold text-slate-400 group-hover:text-indigo-600 transition-colors">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-900 leading-tight">{{ $report->doc_num }}</div>
                                    <div class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter mt-0.5">
                                        Created: {{ $report->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                            <i class="bx bx-calendar text-lg"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700">{{ $monthYear }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black text-slate-900">
                                        {{ number_format($report->total_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php($m = $report->mom)
                                    @if (!$m['has_prev'])
                                        <span class="text-[10px] font-bold text-slate-300 uppercase italic tracking-widest">New</span>
                                    @elseif($m['direction'] === 'up')
                                        <div class="flex items-center gap-1.5 text-rose-600 bg-rose-50 px-2 py-1 rounded-lg w-fit border border-rose-100/50">
                                            <i class='bx bx-trending-up text-sm'></i>
                                            <span class="text-[10px] font-bold">+{{ number_format($m['pct'], 1) }}%</span>
                                        </div>
                                    @elseif($m['direction'] === 'down')
                                        <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg w-fit border border-emerald-100/50">
                                            <i class='bx bx-trending-down text-sm'></i>
                                            <span class="text-[10px] font-bold">{{ number_format($m['pct'], 1) }}%</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400">Stable</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @include('partials.monthly-budget-summary-report-status', [
                                        'status' => $report->status,
                                    ])
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('monthly.budget.summary.report.show', $report->id) }}"
                                            class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all active:scale-95"
                                            title="View Details">
                                            <i class='bx bx-show-alt text-lg'></i>
                                        </a>

                                        @if ($authUser->id == $report->creator_id)
                                            @if ($report->status === 1)
                                                @include('partials.delete-confirmation-modal', [
                                                    'id' => $report->id,
                                                    'route' => 'monthly.budget.summary.report.delete',
                                                    'title' => 'Delete Summary confirmation',
                                                    'body' => "Are you sure want to delete report <strong>$report->doc_num</strong>?",
                                                    'iconOnly' => true,
                                                    'push' => false
                                                ])
                                            @elseif($report->status === 2 || $report->status === 3 || $report->status === 4)
                                                @include('partials.cancel-confirmation-modal', [
                                                    'id' => $report->id,
                                                    'route' => route('monthly.budget.summary.report.cancel', $report->id),
                                                    'title' => 'Cancel Summary Confirmation',
                                                    'iconOnly' => true,
                                                    'push' => false
                                                ])
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                                            <i class="bx bx-receipt text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No reports found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($reports->hasPages())
            <div class="pt-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script type="module">
        flatpickr('#monthPicker', {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "m-Y",
                    altFormat: "F Y",
                    theme: "light"
                })
            ]
        });
    </script>
@endpush
