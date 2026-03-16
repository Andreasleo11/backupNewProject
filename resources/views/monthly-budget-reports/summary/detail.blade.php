@extends('new.layouts.app')

@section('title', 'Budget Summary Detail')
@section('page-title', 'Summary Detail')
@section('page-subtitle', 'Review consolidated figures, momentum analysis, and approval status.')

@push('head')
    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #e5e7eb; /* Tailwind slate-200-ish */
        }

        .merged-row {
            font-style: italic;
            color: #9ca3af; /* slate-400 */
        }
    </style>
@endpush

@section('content')
    @php
        use App\Enums\MonthlyBudgetSummaryStatus as SummaryStatus;

        $authUser = Auth::user();

        $statusEnum = $report->status instanceof SummaryStatus
            ? $report->status
            : SummaryStatus::tryFrom((int) $report->status);

        $isCreator = optional($report->user)->id === $authUser->id;

        // Gate edit/hapus item
        $canEditItems = match ($statusEnum) {
            SummaryStatus::WAITING_CREATOR   => $isCreator,
            SummaryStatus::WAITING_GM        => (int) $authUser->is_gm === 1,
            SummaryStatus::WAITING_DEPT_HEAD => (int) $authUser->is_head === 1
                                                && $authUser->department?->name === 'MOULDING',
            default                          => false,
        };
    @endphp

    <div class="space-y-6">

        {{-- Main Layout Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Data --}}
            <div class="lg:col-span-2 space-y-8">

        {{-- Header + Actions --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold text-slate-900 flex items-center gap-2">
                    Monthly Budget Summary Report
                    @if ($report->is_moulding)
                        <span
                            class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-medium text-indigo-700 ring-1 ring-indigo-100">
                            Moulding
                        </span>
                    @endif
                </h2>

                <div class="mt-1 text-xs sm:text-sm text-slate-500 space-x-1">
                    <span>Doc:</span>
                    <span class="font-semibold text-slate-700">{{ $report->doc_num }}</span>
                    <span>•</span>
                    <span>Month:</span>
                    <span class="font-semibold text-slate-700">{{ $monthYear }}</span>
                    <span>•</span>
                    <span>Created:</span>
                    <span class="font-semibold text-slate-700">{{ $formattedCreatedAt }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                {{-- Use unified status badge --}}
                @include('partials.pr-status-badge', ['pr' => $report])

                {{-- Upload / Refresh untuk user tertentu --}}
                @if ($authUser->email === 'nur@daijo.co.id')
                    <button type="button"
                            class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            x-data
                            @click="$dispatch('open-modal', { id: 'upload-files-modal' })"
                            data-modal-target="upload-files-modal">
                        <i class="bx bx-upload text-base mr-1"></i>
                        Upload
                    </button>

                    {{-- Modal upload (silakan pastikan partial ini juga sudah Tailwind/Alpine) --}}
                    @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])

                    <form action="{{ route('monthly-budget-summary.refresh', $report->id) }}"
                          method="POST" class="inline-flex">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                title="Refresh Newly Approved Departments">
                            <i class="bx bx-refresh text-base mr-1"></i>
                            Refresh
                        </button>
                    </form>
                @endif

                <a href="{{ route('monthly.budget.summary.report.export-pdf', $report->id) }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <i class="bx bxs-file-pdf text-base mr-1"></i>
                    Download PDF
                </a>
            </div>
        </div>


        {{-- Tabel Summary --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-100">
            <div class="p-3 sm:p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs sm:text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-2 py-2 text-center">#</th>
                                <th class="px-2 py-2 text-left">Name</th>
                                <th class="px-2 py-2 text-center">Dept</th>
                                <th class="px-2 py-2 text-center">Quantity</th>
                                <th class="px-2 py-2 text-center">UoM</th>
                                @if ($report->is_moulding)
                                    <th class="px-2 py-2 text-left">Spec</th>
                                    <th class="px-2 py-2 text-center">Last Recorded Stock</th>
                                    <th class="px-2 py-2 text-center">Usage Per Month</th>
                                @endif
                                <th class="px-2 py-2 text-left">Supplier</th>
                                <th class="px-2 py-2 text-right">Cost Per Unit</th>
                                <th class="px-2 py-2 text-right">Total Cost</th>
                                <th class="px-2 py-2 text-left">Remark</th>
                                @if ($canEditItems)
                                    <th class="px-2 py-2 text-center">Action</th>
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
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        {{-- # + Name (rowspan untuk group) --}}
                                        @if ($itemIndex === 0)
                                            <td rowspan="{{ $rowspanCount }}"
                                                class="px-2 py-2 text-center align-top text-slate-500">
                                                {{ ++$rowIndex }}
                                            </td>
                                            <td rowspan="{{ $rowspanCount }}"
                                                class="px-2 py-2 text-left align-top font-medium text-slate-800">
                                                {{ $group['name'] }}
                                            </td>
                                        @endif

                                        <td class="px-2 py-2 text-center whitespace-nowrap">
                                            {{ $item['dept_no'] }}
                                        </td>
                                        <td class="px-2 py-2 text-center whitespace-nowrap">
                                            {{ $item['quantity'] }}
                                        </td>
                                        <td class="px-2 py-2 text-center whitespace-nowrap">
                                            {{ $item['uom'] }}
                                        </td>

                                        @if ($report->is_moulding)
                                            <td class="px-2 py-2 text-left">
                                                {{ $item['spec'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                {{ $item['last_recorded_stock'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                {{ $item['usage_per_month'] ?? '-' }}
                                            </td>
                                        @endif

                                        <td class="px-2 py-2 text-left whitespace-nowrap">
                                            {{ $item['supplier'] ?? '-' }}
                                        </td>
                                        <td class="px-2 py-2 text-right whitespace-nowrap">
                                            @currency($item['cost_per_unit'])
                                        </td>
                                        <td class="px-2 py-2 text-right whitespace-nowrap font-medium">
                                            @currency($totalCost)
                                        </td>

                                        <td class="px-2 py-2 text-left align-top max-w-xs">
                                            <div class="text-xs sm:text-sm text-slate-700 break-words">
                                                {{ $item['remark'] }}
                                            </div>
                                        </td>

                                        @if ($canEditItems)
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @include('partials.edit-monthly-budget-report-summary-detail')
                                                    
                                                    <button type="button"
                                                        class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all active:scale-95"
                                                        @click="$dispatch('open-modal', { id: 'edit-monthly-budget-report-summary-detail-{{ $item['id'] }}' })">
                                                        <i class='bx bx-edit text-sm'></i>
                                                    </button>

                                                    @include('partials.delete-confirmation-modal', [
                                                        'title' => 'Delete item',
                                                        'body' => 'Are you sure want to delete this item?',
                                                        'id' => $item['id'],
                                                        'route' => 'monthly.budget.report.summary.detail.destroy',
                                                        'iconOnly' => true,
                                                        'push' => false
                                                    ])
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach

                            @if (empty($groupedDetailsForView))
                                <tr>
                                    <td colspan="13"
                                        class="px-4 py-6 text-center text-sm text-slate-500">
                                        No data
                                    </td>
                                </tr>
                            @endif

                            {{-- Grand total --}}
                            <tr class="border-t border-slate-200 bg-slate-50/70">
                                <td colspan="{{ $report->is_moulding ? 10 : 7 }}"
                                    class="px-2 py-2 text-right text-xs sm:text-sm font-semibold text-slate-700">
                                    Total
                                </td>
                                <td class="px-2 py-2 text-right font-bold text-slate-900 whitespace-nowrap">
                                    @currency($grandTotal)
                                </td>
                                <td colspan="{{ $canEditItems ? 2 : 1 }}"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Digital Signatures Section --}}
            @include('partials.pr-digital-signatures', ['purchaseRequest' => $report])
        </div>

        {{-- Uploaded files section --}}
        <div class="glass-card p-6">
            <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">
                <i class="bx bx-paperclip text-indigo-500 text-lg"></i> Attachments
            </h3>
            @include('partials.uploaded-section', [
                'showDeleteButton' => $report->status === 'DRAFT' || $report->status === 'WAITING_CREATOR',
                'files' => $report->files,
            ])
        </div>
    </div>

    {{-- Right Column: Sidepanel --}}
    <div class="space-y-6">
        {{-- Approval Action Card --}}
        @if ($canApprove)
            <div class="glass-card border-l-4 border-l-amber-500 p-6 shadow-lg">
                <h3 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-4">
                    Action Required
                </h3>
                <div class="space-y-3">
                    <button type="button" @click="$dispatch('open-approve-modal')"
                            class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-0.5">
                        Approve Summary
                    </button>
                    
                    <button type="button" @click="$dispatch('open-reject-modal')"
                            class="w-full rounded-xl border border-rose-200 bg-white py-2.5 text-sm font-bold text-rose-600 transition-all hover:bg-rose-50 hover:border-rose-300">
                        Reject Summary
                    </button>

                    <button type="button" @click="$dispatch('open-return-modal')"
                            class="w-full rounded-xl border border-orange-200 bg-white py-2.5 text-sm font-bold text-orange-600 transition-all hover:bg-orange-50 hover:border-orange-300">
                        Return for Revision
                    </button>
                </div>
            </div>
        @endif

        {{-- Workflow History --}}
        <div class="glass-card p-6">
            <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">
                <i class="bx bx-history text-indigo-500 text-lg"></i> Workflow History
            </h3>
            @include('partials.pr-approval-timeline', ['pr' => $report])
        </div>
    </div>
</div>
    </div>

    {{-- Modals --}}
    @if ($canApprove)
        @include('partials.pr-approve-modal', ['pr' => $report])
        @include('partials.pr-reject-modal', ['pr' => $report])
        @include('partials.pr-return-modal', ['pr' => $report])
    @endif
@endsection
