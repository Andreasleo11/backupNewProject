@extends('new.layouts.app')

@section('title', 'Budget Summary Detail')
@section('page-title', 'Summary Detail')
@section('page-subtitle', 'Review consolidated figures, momentum analysis, and approval status.')

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

        .status-pill {
            @apply inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider;
        }
    </style>
@endpush

@section('content')
    @php
        use App\Enums\MonthlyBudgetSummaryStatus as SummaryStatus;

        $authUser = Auth::user();

        $statusEnum =
            $report->status instanceof SummaryStatus ? $report->status : SummaryStatus::tryFrom((int) $report->status);

        $isCreator = optional($report->user)->id === $authUser->id;

        // Date formatting
        $reportDate = \Carbon\Carbon::parse($report->report_date);
        $monthYear = $reportDate->format('F Y');
        $createdAt = \Carbon\Carbon::parse($report->created_at);
        $formattedCreatedAt = $createdAt->format('d/m/Y (H:i:s)');

        // Grouping logic for summary view
        $groupedDetailsForView = collect($report->details)
            ->groupBy('name')
            ->map(function ($items, $name) {
                return [
                    'name' => $name,
                    'items' => $items->toArray(),
                ];
            })
            ->values()
            ->toArray();

        // Gate edit/hapus item
        $canEditItems =
            ($report->isDraft() && $isCreator) ||
            match ($statusEnum) {
                SummaryStatus::WAITING_CREATOR => $isCreator,
                SummaryStatus::WAITING_GM => (int) $authUser->is_gm === 1,
                SummaryStatus::WAITING_DEPT_HEAD => (int) $authUser->is_head === 1 &&
                    $authUser->department?->name === 'MOULDING',
                default => false,
            };
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
                                <li><a href="{{ route('monthly-budget-summary.index') }}"
                                        class="hover:text-indigo-600 transition-colors">Budget Summaries</a></li>
                                <li><i class="bx bx-chevron-right"></i></li>
                                <li class="text-slate-600">Detail</li>
                            </ol>
                        </nav>

                        <div class="flex items-center gap-3">
                            <div
                                class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                                <i class="bx bx-file-blank text-white text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                                    Budget Summary Analysis
                                    @if ($report->is_moulding)
                                        <span
                                            class="bg-indigo-100 text-indigo-700 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest ring-1 ring-indigo-200">
                                            Moulding
                                        </span>
                                    @endif
                                </h2>
                                <p class="text-sm text-slate-500 font-medium mt-0.5">
                                    Consolidated report for <span
                                        class="text-indigo-600 font-bold">{{ $monthYear }}</span> • Doc: <span
                                        class="text-slate-800 font-bold">{{ $report->doc_num }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                        {{-- Use unified status badge --}}
                        @include('partials.workflow-status-badge', ['record' => $report])

                        {{-- Upload / Refresh untuk user tertentu --}} @if ($authUser->email === 'nur@daijo.co.id')
                            <div
                                class="flex items-center bg-white/50 backdrop-blur-md rounded-xl p-1 shadow-sm border border-slate-200/60 transition-all hover:shadow-md">
                                <button type="button"
                                    class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors"
                                    x-data @click="$dispatch('open-modal', { id: 'upload-files-modal' })">
                                    <i class="bx bx-upload text-lg mr-2"></i> Upload
                                </button>
                                <div class="w-px h-4 bg-slate-200"></div>
                                <form action="{{ route('monthly-budget-summary.refresh', $report->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors">
                                        <i class="bx bx-refresh text-lg mr-2"></i> Refresh
                                    </button>
                                </form>
                            </div>
                            @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])
                        @endif

                        <a href="{{ route('monthly-budget-summary.export-pdf', $report->id) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-95">
                            <i class="bx bxs-file-pdf text-base"></i>
                            Export PDF
                        </a>
                    </div>
                </div>

                {{-- Spotlight Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-2">
                    <div class="glass-card p-6 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-500/5 transition-transform group-hover:scale-150">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mb-1">Consolidated Total
                        </p>
                        <h4 class="text-2xl font-black text-slate-800">@currency($report->total_amount)</h4>
                        <div class="mt-4 flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-600 ring-1 ring-emerald-100">
                                <i class="bx bx-trending-up"></i> +{{ number_format($report->mom['pct'] ?? 0, 1) }}%
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">vs last month</span>
                        </div>
                    </div>

                    <div class="glass-card p-6 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-purple-500/5 transition-transform group-hover:scale-150">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-purple-500 mb-1">Departments</p>
                        <h4 class="text-2xl font-black text-slate-800">
                            {{ collect($report->details)->pluck('dept_no')->unique()->count() }} <span
                                class="text-sm text-slate-400 font-bold uppercase tracking-widest">Active</span></h4>
                        <div class="mt-4 flex items-center gap-2 text-slate-400">
                            <i class="bx bx-buildings text-sm"></i>
                            <span class="text-[10px] font-bold">Consolidated from approved reports</span>
                        </div>
                    </div>

                    <div class="glass-card p-6 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-500/5 transition-transform group-hover:scale-150">
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 mb-1">Total Items</p>
                        <h4 class="text-2xl font-black text-slate-800">{{ count($report->details) }} <span
                                class="text-sm text-slate-400 font-bold uppercase tracking-widest">Entries</span></h4>
                        <div class="mt-4 flex items-center gap-2 text-slate-400">
                            <i class="bx bx-list-check text-sm"></i>
                            <span class="text-[10px] font-bold">Line items identified across depts</span>
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
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 rounded-tl-xl">
                                            #</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Item Name</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Dept</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Qty</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            UoM</th>
                                        @if ($report->is_moulding)
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
                                            class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Supplier</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-right font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 whitespace-nowrap">
                                            Unit Cost</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-right font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 whitespace-nowrap">
                                            Total</th>
                                        <th
                                            class="sticky top-0 px-4 py-4 text-left font-black uppercase tracking-widest text-slate-400 border-b border-slate-800">
                                            Remark</th>
                                        @if ($canEditItems)
                                            <th
                                                class="sticky top-0 px-4 py-4 text-center font-black uppercase tracking-widest text-slate-400 border-b border-slate-800 rounded-tr-xl">
                                                Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @php
                                        $rowIndex = 0;
                                        $grandTotal = 0;
                                    @endphp

                                    @foreach ($groupedDetailsForView as $index => $group)
                                        @php
                                            $rowspanCount = count($group['items']);
                                        @endphp

                                        @foreach ($group['items'] as $itemIndex => $item)
                                            @php
                                                $totalCost = $item['quantity'] * $item['cost_per_unit'];
                                                $grandTotal += $totalCost;
                                            @endphp <tr
                                                class="group hover:bg-indigo-50/40 transition-colors border-b border-slate-100">
                                                {{-- # + Name (rowspan untuk group) --}}
                                                @if ($itemIndex === 0)
                                                    <td rowspan="{{ $rowspanCount }}"
                                                        class="px-4 py-4 text-center align-top font-black text-slate-400 border-r border-slate-50">
                                                        {{ str_pad(++$rowIndex, 2, '0', STR_PAD_LEFT) }}
                                                    </td>
                                                    <td rowspan="{{ $rowspanCount }}"
                                                        class="px-4 py-4 text-left align-top font-black text-slate-800 border-r border-slate-50 group-hover:text-indigo-600 transition-colors">
                                                        <div class="flex items-center gap-2">
                                                            <i class="bx bx-package text-indigo-400 text-sm"></i>
                                                            {{ $group['name'] }}
                                                        </div>
                                                    </td>
                                                @endif

                                                <td class="px-4 py-4 text-center">
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-600 ring-1 ring-slate-200">
                                                        {{ $item['dept_no'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 text-center font-bold text-slate-700">
                                                    {{ number_format($item['quantity']) }}
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">
                                                    {{ $item['uom'] }}
                                                </td>

                                                @if ($report->is_moulding)
                                                    <td class="px-4 py-4 text-left italic text-slate-500 font-medium">
                                                        {{ $item['spec'] ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-4 text-center font-mono text-slate-500">
                                                        {{ $item['last_recorded_stock'] ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-4 text-center font-mono text-slate-500">
                                                        {{ $item['usage_per_month'] ?? '-' }}
                                                    </td>
                                                @endif

                                                <td class="px-4 py-4 text-left text-slate-500 font-bold italic">
                                                    {{ $item['supplier'] ?? '-' }}
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-right font-mono font-bold text-slate-500 group-hover:text-slate-800 transition-colors">
                                                    @currency($item['cost_per_unit'])
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-right font-mono font-black text-indigo-600 whitespace-nowrap bg-indigo-50/20">
                                                    @currency($totalCost)
                                                </td>

                                                <td class="px-4 py-4 text-left align-top max-w-xs leading-relaxed">
                                                    <div class="text-slate-500 font-medium break-words">
                                                        {{ $item['remark'] }}
                                                    </div>
                                                </td>

                                                @if ($canEditItems)
                                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                                        <div class="flex items-center justify-center gap-2">
                                                            @include('partials.edit-monthly-budget-report-summary-detail')

                                                            <button type="button"
                                                                class="h-8 w-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 hover:-translate-y-0.5 shadow-sm transition-all active:scale-95 flex items-center justify-center"
                                                                @click="$dispatch('open-modal', { id: 'edit-monthly-budget-report-summary-detail-{{ $item['id'] }}' })">
                                                                <i class='bx bx-edit'></i>
                                                            </button>

                                                            @include('partials.delete-confirmation-modal', [
                                                                'title' => 'Delete item',
                                                                'body' => 'Are you sure want to delete this item?',
                                                                'id' => $item['id'],
                                                                'route' => 'monthly-budget-summary-detail.destroy',
                                                                'iconOnly' => true,
                                                                'push' => false,
                                                            ])
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach

                                    @if (empty($groupedDetailsForView))
                                        <tr>
                                            <td colspan="13" class="px-4 py-6 text-center text-sm text-slate-500">
                                                No data
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- Grand total --}}
                                    <tr class="bg-slate-900 shadow-2xl">
                                        <td colspan="{{ $report->is_moulding ? 10 : 7 }}"
                                            class="px-6 py-6 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-400">
                                            Grand Total
                                        </td>
                                        <td
                                            class="px-6 py-6 text-right text-base font-black text-indigo-400 whitespace-nowrap">
                                            @currency($grandTotal)
                                        </td>
                                        <td colspan="{{ $canEditItems ? 1 : 1 }}" class="rounded-br-xl"></td>
                                    </tr>
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
                        'showDelete' =>
                            $report->status === 'DRAFT' ||
                            $report->status === 'WAITING_CREATOR' ||
                            (isset($statusEnum) && $statusEnum === SummaryStatus::WAITING_CREATOR),
                        'title' => 'Related Documents',
                    ])
                </div>
            </div>

            {{-- Right Column: Sidepanel --}}
            <div class="space-y-6">
                {{-- Approval Action Card --}}
                @if ($report->isDraft() && $isCreator)
                    <div class="glass-card border-t-4 border-indigo-500 p-8 premium-shadow relative overflow-hidden group">
                        <div
                            class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-500/5 transition-transform group-hover:scale-110">
                        </div>
                        <h3
                            class="text-sm font-black uppercase tracking-[0.2em] text-slate-800 mb-4 flex items-center gap-2">
                            <i class="bx bx-pen text-indigo-500"></i> Local Draft
                        </h3>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">
                            This summary is currently in <span class="text-indigo-600 font-black">Draft</span> mode. You
                            can edit line items, adjust costs, or delete entries before officially submitting for approval.
                        </p>
                        <form action="{{ route('monthly-budget-summary.submit', $report->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-200 transition-all hover:bg-indigo-700 hover:-translate-y-1 active:scale-95">
                                <i class="bx bx-paper-plane text-base"></i> Sign & Start Approval
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
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @if ($canApprove)
        @push('modals')
            @include('partials.approval-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-summary.save-autograph',
                'title' => 'Approve Budget Summary',
                'entityName' => 'Budget Summary Report',
                'buttonLabel' => 'Confirm Approval',
            ])
            @include('partials.rejection-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-summary.reject',
                'title' => 'Reject Budget Summary',
                'entityName' => 'Budget Summary Report',
                'buttonLabel' => 'Confirm Rejection',
            ])
            @include('partials.return-modal', [
                'id' => $report->id,
                'route' => 'monthly-budget-summary.return',
                'title' => 'Return for Revision',
                'entityName' => 'Budget Summary Report',
                'buttonLabel' => 'Confirm Return',
            ])
        @endpush
    @endif
@endsection
