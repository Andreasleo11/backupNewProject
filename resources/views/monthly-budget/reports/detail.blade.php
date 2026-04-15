@extends('new.layouts.app')

@section('title', 'Budget Report Detail')
@section('page-title', 'Report Detail')
@section('page-subtitle', 'Review department budget entries, specifications, and approval progress.')

@push('head')
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 1.25rem;
        }

        .premium-shadow {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 20px 25px -5px rgba(0, 0, 0, 0.02);
        }

        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 0.75rem;
            background-color: rgba(248, 250, 252, 0.5);
        }
    </style>
@endpush

@section('content')
    @php
        $authUser = Auth::user();
        $isCreator = $report->creator_id === $authUser->id;
        $isDraft = $report->isDraft();

        // Date formatting
        $reportDate = \Carbon\Carbon::parse($report->report_date);
        $monthYear = $reportDate->format('F Y');

        // Calculate Total
        $totalAmount = $report->details->sum(fn($d) => $d->quantity * ($d->cost_per_unit ?? 0));

        // Check if molding
        $isMoulding = (string) $report->dept_no === '363';
    @endphp

    <div class="space-y-6">
        {{-- Main Layout Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Data --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Header + Actions --}}
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between mb-2">
                    <div>
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-slate-400">
                                <li><a href="{{ route('home') }}"
                                        class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                                <li><i class="bx bx-chevron-right"></i></li>
                                <li><a href="{{ route('monthly-budget-reports.index') }}"
                                        class="hover:text-indigo-600 transition-colors">Budget Reports</a></li>
                                <li><i class="bx bx-chevron-right"></i></li>
                                <li class="text-slate-600">Detail</li>
                            </ol>
                        </nav>

                        <div class="flex items-center gap-3">
                            <div
                                class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                                <i class="bx bx-receipt text-white text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                                    Budget Report Analysis
                                    @if ($isMoulding)
                                        <span
                                            class="bg-indigo-100 text-indigo-700 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest ring-1 ring-indigo-200">
                                            Moulding
                                        </span>
                                    @endif
                                </h2>
                                <p class="text-sm text-slate-500 font-medium mt-0.5">
                                    Department: <span class="text-slate-800 font-bold">{{ $report->department->name }}
                                        ({{ $report->dept_no }})</span> • Period: <span
                                        class="text-indigo-600 font-bold">{{ $monthYear }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                        {{-- Status Badge --}}
                        @include('partials.workflow-status-badge', ['record' => $report])

                        {{-- Action Buttons --}}
                        @if ($isCreator && $isDraft)
                            <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-bold text-white shadow-xl shadow-amber-900/10 transition-all hover:bg-amber-600 hover:scale-[1.02] active:scale-95">
                                <i class="bx bx-edit-alt text-base"></i>
                                Edit Report
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Spotlight Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div class="glass-card p-6 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-500/5 transition-transform group-hover:scale-150">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mb-1">Total Budgeted</p>
                        <h4 class="text-2xl font-black text-slate-800">@currency($totalAmount)</h4>
                        <div class="mt-4 flex items-center gap-2 text-slate-400">
                            <i class="bx bx-calculator text-sm"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Sum of all entries</span>
                        </div>
                    </div>

                    <div class="glass-card p-6 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-purple-500/5 transition-transform group-hover:scale-150">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-purple-500 mb-1">Line Items</p>
                        <h4 class="text-2xl font-black text-slate-800">{{ $report->details->count() }} <span
                                class="text-sm text-slate-400 font-bold uppercase tracking-widest">Entries</span></h4>
                        <div class="mt-4 flex items-center gap-2 text-slate-400">
                            <i class="bx bx-list-check text-sm"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Detailed breakdown</span>
                        </div>
                    </div>
                </div>

                {{-- Table Container --}}
                <div class="glass-card premium-shadow overflow-hidden">
                    <div class="p-6">
                        <div class="overflow-x-auto rounded-xl">
                            <table class="min-w-full text-xs text-slate-600 border-separate border-spacing-0">
                                <thead>
                                    <tr class="bg-slate-900">
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 rounded-tl-xl w-12">
                                            #</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Item Name</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Qty</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            UoM</th>
                                        @if ($isMoulding)
                                            <th
                                                class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                                Spec</th>
                                            <th
                                                class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                                Stock</th>
                                            <th
                                                class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                                Usage</th>
                                        @endif
                                        <th
                                            class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 rounded-tr-xl">
                                            Remark</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($report->details as $index => $item)
                                        <tr class="group hover:bg-indigo-50/40 transition-colors border-b border-slate-100">
                                            <td
                                                class="px-4 py-4 text-center font-black text-slate-400 border-r border-slate-50">
                                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-left font-black text-slate-800 group-hover:text-indigo-600 transition-colors">
                                                {{ $item->name }}
                                            </td>
                                            <td class="px-4 py-4 text-center font-bold text-slate-700">
                                                {{ number_format($item->quantity) }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">
                                                {{ $item->uom }}
                                            </td>
                                            @if ($isMoulding)
                                                <td class="px-4 py-4 text-left italic text-slate-500">
                                                    {{ $item->spec ?? '-' }}
                                                </td>
                                                <td class="px-4 py-4 text-center font-mono text-slate-500">
                                                    {{ $item->last_recorded_stock ?? '-' }}
                                                </td>
                                                <td class="px-4 py-4 text-center font-mono text-slate-500">
                                                    {{ $item->usage_per_month ?? '-' }}
                                                </td>
                                            @endif
                                            <td class="px-4 py-4 text-left italic text-slate-400">
                                                {{ $item->remark ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isMoulding ? 7 : 5 }}" class="px-4 py-12 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <i class="bx bx-receipt text-3xl text-slate-200"></i>
                                                    <span
                                                        class="text-xs font-bold text-slate-400 uppercase tracking-widest">No
                                                        entries found for this report.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Digital Signatures Section --}}
                    <div class="border-t border-slate-100 bg-slate-50/40 p-6">
                        @include('partials.workflow-digital-signatures', ['record' => $report])
                    </div>
                </div>

                {{-- Related Documents Section --}}
                <div class="glass-card p-6">
                    @include('partials.file-attachments', [
                        'files' => $report->files,
                        'showDelete' => $isCreator && $isDraft,
                        'title' => 'Supporting Documents',
                    ])
                </div>
            </div>

            {{-- Right Column: Sidepanel --}}
            <div class="space-y-6">
                {{-- Approval Action Card --}}
                @if ($isDraft && $isCreator)
                    <div class="glass-card border-t-4 border-indigo-500 p-8 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-500/5 transition-transform group-hover:scale-110">
                        </div>
                        <h3
                            class="text-sm font-black uppercase tracking-[0.2em] text-slate-800 mb-4 flex items-center gap-2">
                            <i class="bx bx-pen text-indigo-500"></i> Local Draft
                        </h3>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">
                            This report is currently in <span class="text-indigo-600 font-black">Draft</span> mode. It is
                            only visible to you. Review carefully before submitting for official approval.
                        </p>
                        <form action="{{ route('monthly-budget-reports.submit', $report->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-200 transition-all hover:bg-indigo-700 hover:-translate-y-1 active:scale-95">
                                <i class="bx bx-paper-plane text-base"></i> Sign & Submit
                            </button>
                        </form>
                    </div>
                @endif

                @if ($canApprove)
                    <div class="glass-card border-l-4 border-amber-500 p-8 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-amber-500/5 transition-transform group-hover:scale-110">
                        </div>
                        <h3
                            class="text-sm font-black uppercase tracking-[0.2em] text-slate-800 mb-6 flex items-center gap-2">
                            <i class="bx bx-bolt-circle text-amber-500"></i> Review Portal
                        </h3>
                        <div class="space-y-4">
                            <button type="button" @click="$dispatch('open-approve-modal')"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-1 active:scale-95">
                                <i class="bx bx-check-double text-base"></i> Approve Report
                            </button>

                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <button type="button" @click="$dispatch('open-reject-modal')"
                                    class="flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50/30 py-3 text-[10px] font-black uppercase tracking-widest text-rose-600 transition-all hover:bg-rose-50 hover:border-rose-300">
                                    Reject
                                </button>
                                <button type="button" @click="$dispatch('open-return-modal')"
                                    class="flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50/30 py-3 text-[10px] font-black uppercase tracking-widest text-orange-600 transition-all hover:bg-orange-50 hover:border-orange-300">
                                    Return
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Workflow History --}}
                <div class="glass-card p-8 premium-shadow">
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-slate-800 mb-8">
                        <i class="bx bx-history text-indigo-500 text-xl"></i> Approval Trail
                    </h3>
                    <div class="relative px-2">
                        @include('partials.workflow-timeline', ['record' => $report])
                    </div>
                </div>

                {{-- Activities --}}
                <div class="glass-card p-8 premium-shadow">
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-slate-800 mb-8">
                        <i class="bx bx-list-ul text-indigo-500 text-xl"></i> Recent Activities
                    </h3>
                    <div class="space-y-4">
                        @forelse($report->combined_activities->take(5) as $activity)
                            <div class="flex gap-3">
                                <div
                                    class="mt-1 h-2 w-2 rounded-full {{ $activity->event === 'created' ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-700 leading-tight">
                                        {{ ucfirst($activity->description) }}
                                        @if ($activity->causer)
                                            by <span class="text-indigo-600">{{ $activity->causer->name }}</span>
                                        @endif
                                    </p>
                                    <p class="text-[9px] text-slate-400 font-medium">
                                        {{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 text-center py-4 font-bold uppercase tracking-widest">No
                                activities recorded</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @if ($canApprove)
        @push('modals')
            @include('partials.approval-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-reports.approve',
                'title' => 'Approve Budget Report',
                'entityName' => "Report {$report->doc_num}",
                'buttonLabel' => 'Confirm Approval',
            ])
            @include('partials.rejection-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-reports.reject',
                'title' => 'Reject Budget Report',
                'entityName' => "Report {$report->doc_num}",
                'buttonLabel' => 'Confirm Rejection',
            ])
            @include('partials.return-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-reports.return',
                'title' => 'Return Budget Report',
                'entityName' => "Report {$report->doc_num}",
                'buttonLabel' => 'Confirm Return',
            ])
        @endpush
    @endif
@endsection
