@extends('new.layouts.app')

@section('title', 'Budget Report Details')
@section('page-title', 'Report Details')
@section('page-subtitle', 'Comprehensive view of items, approvals, and workflow history.')

@section('content')
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
    @endphp

    <div class="space-y-6">

        {{-- Main Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Details --}}
            <div class="lg:col-span-2 space-y-6">

        {{-- Report card --}}
        <section aria-label="report">
            <div class="mt-4">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/70">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">
                                Monthly Budget Report
                            </h2>
                            <p class="text-xs text-slate-500">
                                Detail report & approval status
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            @if ($authUser->id === $report->creator_id && (int)$report->is_cancel === 0)
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
                        </div>
                    </div>

                    <div class="px-4 py-5 space-y-5 relative {{ $report->is_cancel ? 'opacity-75 grayscale bg-slate-50/50 pointer-events-none select-none' : '' }}">
                        {{-- Cancelled Watermark --}}
                        @if($report->is_cancel)
                            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none overflow-hidden z-0">
                                <span class="text-[12rem] font-black uppercase tracking-[2rem] -rotate-[35deg] whitespace-nowrap">
                                    Cancelled
                                </span>
                            </div>
                        @endif

                        {{-- Header info --}}
                        <div class="text-center space-y-2 relative z-10">
                            <h1 class="text-lg font-bold text-slate-900">
                                Monthly Budget Report
                            </h1>

                            @php
                                $reportDate = \Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');
                            @endphp

                            <div class="text-xs text-slate-600 space-y-1">
                                <div>
                                    From Department :
                                    <span class="font-semibold">
                                        {{ $report->department->name }} ({{ $report->dept_no }})
                                    </span>
                                </div>
                                <div>
                                    Created By :
                                    <span class="font-semibold">{{ $report->user->name }}</span>
                                </div>
                                <div>
                                    Report date :
                                    <span class="font-semibold">
                                        {{ $report->report_date }} ({{ $monthYear }})
                                    </span>
                                </div>
                                <div class="pt-2 flex justify-center">
                                    {{-- Use unified status badge or custom draft badge --}}
                                    @if($report->isDraft())
                                        <div class="relative inline-flex">
                                            <span class="flex h-3 w-3 absolute -top-1 -right-1">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                            </span>
                                            <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-widest border border-amber-200 shadow-sm">
                                                Draft Mode
                                            </span>
                                        </div>
                                    @else
                                        @include('partials.workflow-status-badge', ['record' => $report])
                                    @endif
                                </div>
                            </div>
                        </div>

                    {{-- Table --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm ring-1 ring-black/[0.02] relative z-10">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-slate-50/80 border-b border-slate-200">
                                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                            <th class="px-4 py-3 text-left">Item Description</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-4 py-3 text-left">Spec</th>
                                            @endif
                                            <th class="px-4 py-3 text-center">UoM</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-4 py-3 text-right">Stock</th>
                                                <th class="px-4 py-3 text-right">Usage</th>
                                            @endif
                                            <th class="px-4 py-3 text-right">Qty</th>
                                            <th class="px-4 py-3 text-left">Remark</th>
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
                @include('partials.pr-digital-signatures', ['purchaseRequest' => $report])
            </div>

            {{-- Cancelled Banner (Outside the dimmed area) --}}
            @if($report->is_cancel)
                <div class="px-4 pb-6 mt-[-1rem]">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 flex items-start gap-4 shadow-xl shadow-rose-900/5 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-200/40 rounded-full blur-2xl group-hover:bg-rose-200/60 transition-all duration-700"></div>
                        <div class="absolute -left-4 -bottom-4 w-20 h-20 bg-rose-200/20 rounded-full blur-xl"></div>
                        
                        <div class="h-14 w-14 rounded-2xl bg-rose-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-rose-200 animate-pulse-slow">
                            <i class="bx bx-x-circle text-4xl"></i>
                        </div>
                        
                        <div class="space-y-1 relative flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-black text-rose-950 uppercase tracking-widest flex items-center gap-2">
                                    Report Permanently Cancelled
                                </h4>
                                <span class="px-2.5 py-1 rounded-lg bg-rose-600 text-[10px] font-black text-white uppercase tracking-tighter shadow-sm">
                                    Final State
                                </span>
                            </div>
                            
                            <div class="bg-white/80 backdrop-blur-md rounded-xl p-4 border border-rose-100 mt-4 shadow-sm group-hover:bg-white transition-colors">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-rose-500"></div>
                                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.15em]">Cancellation Statement</p>
                                </div>
                                <p class="text-sm font-bold text-rose-900 leading-relaxed italic pr-4">
                                    "{{ $report->cancel_reason ?: 'No formal reason was specified for this cancellation.' }}"
                                </p>
                            </div>

                            <div class="flex items-center gap-4 mt-4">
                                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-tighter flex items-center gap-1.5">
                                    <i class="bx bx-shield-x text-xs"></i>
                                    Workflow Terminated
                                </p>
                                <div class="h-1 w-1 rounded-full bg-rose-200"></div>
                                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-tighter flex items-center gap-1.5">
                                    <i class="bx bx-lock-alt text-xs"></i>
                                    Read Only Mode
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
        </div>

            {{-- Right Column: Actions & Timeline --}}
            <div class="space-y-6">
                {{-- Draft Action Card --}}
                @if ($report->isDraft() && $authUser->id === $report->creator_id && !$report->is_cancel)
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-200 shadow-xl shadow-amber-900/5 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-400/10 rounded-full blur-2xl group-hover:bg-amber-400/20 transition-all duration-700"></div>
                        
                        <div class="relative">
                            <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-amber-900 mb-2">
                                <i class="bx bx-edit-alt text-xl"></i> Draft Actions
                            </h3>
                            <p class="text-xs text-amber-700 font-medium leading-relaxed mb-6">
                                This report is still in draft. You can make further changes or submit it for the official approval workflow.
                            </p>
                            
                            <div class="space-y-3">
                                <form action="{{ route('monthly-budget-reports.submit', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-95">
                                        <i class="bx bx-send text-lg"></i>
                                        <span>Sign & Submit</span>
                                    </button>
                                </form>

                                <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                   class="w-full flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-white/50 py-2.5 text-sm font-bold text-amber-800 backdrop-blur-sm transition-all hover:bg-white active:scale-95">
                                    <i class="bx bx-pencil text-lg"></i>
                                    <span>Continue Editing</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Approval Action Card --}}
                @if ($canApprove && !$report->is_cancel)
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-emerald-100 p-6 relative overflow-hidden group">
                         <div class="absolute -right-2 -top-2 w-16 h-16 bg-emerald-500/5 rounded-full blur-xl"></div>
                        
                        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-emerald-900 mb-4">
                            <i class="bx bx-check-shield text-xl"></i> Action Required
                        </h3>
                        <div class="space-y-3">
                            <button type="button" @click="$dispatch('open-approve-modal')"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:scale-[1.02] active:scale-95">
                                <i class="bx bx-check-circle text-lg"></i>
                                <span>Approve Report</span>
                            </button>
                            
                            <button type="button" @click="$dispatch('open-reject-modal')"
                                    class="w-full rounded-xl border border-rose-100 bg-white/50 py-2.5 text-sm font-bold text-rose-600 backdrop-blur-sm transition-all hover:bg-rose-50 hover:border-rose-200">
                                Reject
                            </button>

                            <button type="button" @click="$dispatch('open-return-modal')"
                                    class="w-full rounded-xl border border-orange-100 bg-white/50 py-2.5 text-sm font-bold text-orange-600 backdrop-blur-sm transition-all hover:bg-orange-50 hover:border-orange-200">
                                Return for Revision
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Workflow History --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">
                        <i class="bx bx-history text-indigo-500 text-lg"></i> Workflow History
                    </h3>
                    @if($report->isDraft())
                        <div class="py-10 text-center space-y-3">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto">
                                <i class="bx bxs-traffic-cone text-slate-300 text-2xl"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">
                                Workflow has not <br> started yet
                            </p>
                        </div>
                    @else
                        @include('partials.pr-approval-timeline', ['pr' => $report])
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

@endsection
