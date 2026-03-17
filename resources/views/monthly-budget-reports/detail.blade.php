@extends('new.layouts.app')

@section('title', 'Budget Report Details')
@section('page-title', 'Report Details')
@section('page-subtitle', 'Comprehensive view of items, approvals, and workflow history.')

@section('content')
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        
        $canUpload = ($authUser->id === $report->creator_id || $authUser->hasRole('super-admin')) && $report->workflow_status !== 'CANCELED';
        $isCancelled = $report->workflow_status === 'CANCELED';

        $reportDate = \Carbon\Carbon::parse($report->report_date);
        $monthYear = $reportDate->format('F Y');
    @endphp

    <div class="space-y-6">

        {{-- Main Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Details --}}
            <div class="lg:col-span-2 space-y-6">

        {{-- Report card --}}
        <section aria-label="report">
            <div>
                <div class="glass-card premium-shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4 bg-white/50 backdrop-blur-md">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-[0.15em]">
                                Monthly Budget Report
                            </h2>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">
                                Reference #{{ $report->doc_num }}
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            @if ($authUser->id === $report->creator_id && !$isCancelled)
                                @if ($report->isDraft())
                                    <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2
                                              text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95">
                                        <i class="bx bx-edit text-[1rem]"></i>
                                        <span>Edit</span>
                                    </a>

                                    @include('partials.delete-confirmation-modal', [
                                        'id' => $report->id,
                                        'route' => 'monthly-budget-reports.delete',
                                        'title' => 'Delete report confirmation',
                                        'body' => "Are you sure want to delete this report with id <strong>$report->id</strong>?",
                                        'buttonLabel' => 'Delete',
                                        'push' => false
                                    ])
                                @else
                                    @include('partials.cancel-modal', [
                                        'id' => $report->id,
                                        'route' => 'monthly-budget-reports.cancel',
                                        'title' => 'Cancel Budget Report',
                                        'entityName' => 'Monthly Budget Report',
                                        'buttonLabel' => 'Cancel Report',
                                        'triggerClass' => 'inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-xs font-bold text-rose-700 shadow-sm transition-all hover:bg-rose-50 hover:border-rose-300 active:scale-95'
                                    ])
                                @endif
                            @endif

                            @if($canUpload)
                                <button type="button"
                                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800 hover:-translate-y-0.5 active:scale-95"
                                        x-data
                                        @click="$dispatch('open-upload-modal')">
                                    <i class="bx bx-upload text-[1rem]"></i>
                                    <span>Upload Files</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="px-4 py-5 space-y-5 relative {{ $isCancelled ? 'opacity-75 grayscale bg-slate-50/50 pointer-events-none select-none' : '' }}">
                        {{-- Cancelled Watermark --}}
                        @if($isCancelled)
                            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none overflow-hidden z-0">
                                <span class="text-[12rem] font-black uppercase tracking-[2rem] -rotate-[35deg] whitespace-nowrap">
                                    Cancelled
                                </span>
                            </div>
                        @endif

                        {{-- Header info --}}
                        <div class="text-center space-y-3 relative z-10 pb-4">
                            <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">
                                {{ $monthYear }}
                            </h1>

                            <div class="flex flex-wrap items-center justify-center gap-6 text-[11px] text-slate-500 font-bold uppercase tracking-widest">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50">
                                    <i class="bx bx-buildings text-lg text-slate-400"></i>
                                    <span>Dept: <span class="text-slate-900">{{ $report->department->name }}</span></span>
                                </div>
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50">
                                    <i class="bx bx-user text-lg text-slate-400"></i>
                                    <span>Creator: <span class="text-slate-900">{{ $report->user->name }}</span></span>
                                </div>
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50">
                                    <i class="bx bx-calendar text-lg text-slate-400"></i>
                                    <span>Issued: <span class="text-slate-900">{{ $report->report_date }}</span></span>
                                </div>
                            </div>

                            <div class="pt-4 flex justify-center">
                                {{-- Use unified status badge or custom draft badge --}}
                                @if($report->isDraft())
                                    <div class="relative inline-flex">
                                        <span class="flex h-3 w-3 absolute -top-1 -right-1">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]"></span>
                                        </span>
                                        <span class="px-4 py-1.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-[0.2em] border border-amber-200 shadow-sm transition-all hover:bg-amber-100">
                                            Draft Mode
                                        </span>
                                    </div>
                                @else
                                    <div class="transform hover:scale-105 transition-transform duration-300">
                                        @include('partials.workflow-status-badge', ['record' => $report])
                                    </div>
                                @endif
                            </div>
                        </div>

                    {{-- Table --}}
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xl ring-1 ring-black/[0.02] relative z-10 premium-shadow mx-2 mb-2">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-slate-50/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-20">
                                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">
                                            <th class="px-5 py-4 text-left">Item Description</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-5 py-4 text-left">Spec</th>
                                            @endif
                                            <th class="px-5 py-4 text-center">UoM</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-5 py-4 text-right">Stock</th>
                                                <th class="px-5 py-4 text-right">Usage</th>
                                            @endif
                                            <th class="px-5 py-4 text-right">Qty</th>
                                            <th class="px-5 py-4 text-left">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($report->details as $detail)
                                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                                <td class="px-4 py-2.5 text-xs text-slate-900 font-medium">
                                                    {{ $detail->name }}
                                                </td>

                                                @if ($report->dept_no == 363)
                                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                                        {{ $detail->spec ?? '-' }}
                                                    </td>
                                                @endif

                                                <td class="px-4 py-2.5 text-xs text-center text-slate-600">
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">
                                                        {{ $detail->uom }}
                                                    </span>
                                                </td>

                                                @if ($report->dept_no == 363)
                                                    <td class="px-4 py-2.5 text-xs text-right text-slate-600">
                                                        {{ number_format((float)$detail->last_recorded_stock) }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-xs text-right text-slate-600 font-medium">
                                                        {{ $detail->usage_per_month }}
                                                    </td>
                                                @endif

                                                <td class="px-4 py-2.5 text-xs text-right text-indigo-700 font-bold">
                                                    {{ number_format((float)$detail->quantity) }}
                                                </td>
                                                <td class="px-4 py-2.5 text-xs text-slate-500 italic">
                                                    {{ $detail->remark ?: '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $report->dept_no == 363 ? '7' : '4' }}"
                                                    class="px-4 py-8 text-center text-xs text-slate-400 font-medium">
                                                    <i class="bx bx-info-circle text-lg mb-1 block opacity-20"></i>
                                                    No items found in this report.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>

                {{-- Digital Signatures Section --}}
                <div class="px-4 py-2">
                    @include('partials.pr-digital-signatures', ['purchaseRequest' => $report])
                </div>
            </div>

            {{-- Cancelled Banner (Outside the dimmed area) --}}
            @if($isCancelled)
                <div class="px-4 pb-6 mt-[-1rem]">
                    <div class="glass-card p-6 flex items-start gap-4 premium-shadow border-rose-200 bg-rose-50/30 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all duration-700"></div>
                        
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 flex items-center justify-center text-white shrink-0 shadow-lg shadow-rose-200 animate-pulse-slow">
                            <i class="bx bx-x-circle text-4xl"></i>
                        </div>
                        
                        <div class="space-y-1 relative flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-black text-rose-950 uppercase tracking-[0.2em] flex items-center gap-2">
                                    Report Permanently Cancelled
                                </h4>
                                <span class="px-2.5 py-1 rounded-lg bg-rose-600 text-[10px] font-black text-white uppercase tracking-tighter shadow-sm">
                                    Final State
                                </span>
                            </div>
                            
                            <div class="bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-rose-100 mt-4 shadow-sm group-hover:bg-white/90 transition-colors">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-rose-500"></div>
                                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-[0.2em]">Cancellation Reason</p>
                                </div>
                                <p class="text-sm font-bold text-rose-900 leading-relaxed italic pr-4">
                                    "{{ $report->cancellation_reason ?: 'No formal reason was specified.' }}"
                                </p>
                            </div>

                            <div class="flex items-center gap-4 mt-4">
                                <p class="text-[9px] font-black text-rose-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <i class="bx bx-shield-x text-sm"></i>
                                    Workflow Terminated
                                </p>
                                <div class="h-1 w-1 rounded-full bg-rose-200"></div>
                                <p class="text-[9px] font-black text-rose-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <i class="bx bx-lock-alt text-sm"></i>
                                    Read Only
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Related Documents Section --}}
    <div class="glass-card premium-shadow p-6 bg-white/50 backdrop-blur-md">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.15em] mb-4 flex items-center gap-2">
            <i class="bx bx-paperclip text-indigo-500"></i>
            Related Documents
        </h3>
        @include('partials.file-attachments', [
            'files' => $report->files,
            'showDelete' => $canUpload,
            'title' => ''
        ])
    </div>
</div>

            {{-- Right Column: Actions & Timeline --}}
            <div class="space-y-6">
                {{-- Draft Action Card --}}
                @if ($report->isDraft() && $authUser->id === $report->creator_id && !$isCancelled)
                    <div class="glass-card premium-shadow p-6 bg-gradient-to-br from-amber-500 to-orange-600 relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:bg-white/30 transition-all duration-700"></div>
                        
                        <div class="relative z-10">
                            <h3 class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.25em] text-white/90 mb-3">
                                <i class="bx bx-edit-alt text-xl"></i> Draft Actions
                            </h3>
                            <p class="text-xs text-white/80 font-bold leading-relaxed mb-6">
                                This report is still in draft. Review all items before starting the official approval process.
                            </p>
                            
                            <div class="space-y-3">
                                <form action="{{ route('monthly-budget-reports.submit', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-white py-3.5 text-xs font-black text-orange-600 uppercase tracking-widest shadow-xl transition-all hover:bg-slate-50 hover:scale-[1.02] hover:-translate-y-0.5 active:scale-95 group">
                                        <i class="bx bx-send text-lg group-hover:translate-x-1 transition-transform"></i>
                                        <span>Sign & Submit</span>
                                    </button>
                                </form>

                                <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                   class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 py-3 text-xs font-black text-white uppercase tracking-widest backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                                    <i class="bx bx-pencil text-lg"></i>
                                    <span>Edit Details</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Approval Action Card --}}
                @if ($canApprove && !$isCancelled)
                    <div class="glass-card premium-shadow p-6 bg-gradient-to-br from-indigo-600 to-violet-700 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-700"></div>
                        
                        <h3 class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.25em] text-white/90 mb-4">
                            <i class="bx bx-check-shield text-xl"></i> Action Required
                        </h3>
                        <div class="space-y-3 relative z-10">
                            <button type="button" @click="$dispatch('open-approve-modal')"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-500 py-4 text-xs font-black text-white uppercase tracking-[0.15em] shadow-xl shadow-emerald-900/20 transition-all hover:bg-emerald-400 hover:scale-[1.02] hover:-translate-y-0.5 active:scale-95">
                                <i class="bx bx-check-circle text-xl animate-pulse-slow"></i>
                                <span>Approve Report</span>
                            </button>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="$dispatch('open-reject-modal')"
                                        class="flex items-center justify-center rounded-xl border border-white/20 bg-white/10 py-3 text-[10px] font-black text-white uppercase tracking-widest backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                                    Reject
                                </button>

                                <button type="button" @click="$dispatch('open-return-modal')"
                                        class="flex items-center justify-center rounded-xl border border-white/20 bg-white/10 py-3 text-[10px] font-black text-white uppercase tracking-widest backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                                    Return
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Workflow History --}}
                <div class="glass-card premium-shadow p-6 bg-white/50 backdrop-blur-md">
                    <h3 class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.2em] text-slate-800 mb-6 font-primary">
                        <i class="bx bx-history text-indigo-500 text-lg"></i> Workflow History
                    </h3>
                    @if($report->isDraft())
                        <div class="py-12 text-center space-y-4">
                            <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto rotate-12 group-hover:rotate-0 transition-transform duration-500">
                                <i class="bx bxs-traffic-cone text-slate-300 text-3xl"></i>
                            </div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] leading-relaxed">
                                Workflow has not <br> started yet
                            </p>
                        </div>
                    @else
                        @include('partials.workflow-timeline', ['record' => $report])
                    @endif
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
                'entityName' => 'Monthly Budget Report',
                'buttonLabel' => 'Confirm Approval'
            ])
            @include('partials.rejection-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-reports.reject',
                'title' => 'Reject Budget Report',
                'entityName' => 'Monthly Budget Report',
                'buttonLabel' => 'Confirm Rejection'
            ])
            @include('partials.return-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-reports.return',
                'title' => 'Return for Revision',
                'entityName' => 'Monthly Budget Report',
                'buttonLabel' => 'Confirm Return'
            ])
        @endpush
    @endif

    @if ($canUpload)
        @push('modals')
            @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])
        @endpush
    @endif
@endsection
