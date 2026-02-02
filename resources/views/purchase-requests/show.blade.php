@extends('new.layouts.app')

@section('content')
    @php
        $user = auth()->user();

        // from controller/usecase now
        $canAutoApprove = $flags['canAutoApprove'] ?? false;
        $canApprove = $flags['canApprove'] ?? false;
        $canUpload = $flags['canUpload'] ?? false;
        $canEditPr = $flags['canEdit'] ?? false;

        $totalall = (float) ($totals['total'] ?? 0);
        $isThereAnyCurrencyDifference = (bool) ($totals['hasCurrencyDiff'] ?? false);
        $prevCurrency = $totals['currency'] ?? null;

        // for alpine
        $slots = $autographSlots ?? [];
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-6 lg:py-8" x-data="prDetailPage(@js($slots), @js($canAutoApprove), @js($purchaseRequest->id), @js(csrf_token()))">

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
            <div
                class="mb-4 flex items-start justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                <span>{{ session('success') }}</span>
                <button type="button" class="ml-3 text-emerald-700 hover:text-emerald-900"
                    @click="$el.parentElement.remove()">
                    ×
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 flex items-start justify-between rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-3 text-rose-700 hover:text-rose-900" @click="$el.parentElement.remove()">
                    ×
                </button>
            </div>
        @endif

        {{-- TOP BAR --}}
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <a href="{{ auth()->user()->specification->name === 'DIRECTOR' ? route('director.pr.index') : route('purchase-requests.index') }}"
                    class="inline-flex items-center text-xs font-medium text-slate-400 hover:text-slate-600">
                    ‹ Back to list
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
                        Purchase Requisition
                    </h1>

                    {{-- STATUS BADGE (tailwind wrapper around existing partial) --}}
                    <div class="inline-flex items-center gap-2">
                        @include('partials.pr-status-badge', ['pr' => $purchaseRequest])
                        
                        {{-- Draft Indicator --}}
                        @if($purchaseRequest->status === 'draft' || $purchaseRequest->status === 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                <i class='bx bx-edit-alt text-sm'></i>
                                DRAFT
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-slate-500 sm:text-sm">
                    Doc No: <span class="font-medium text-slate-700">{{ $purchaseRequest->doc_num }}</span>
                    • From <span class="font-medium text-slate-700">{{ $purchaseRequest->from_department }}
                        ({{ $fromDeptNo }})</span>
                    • To <span class="font-medium text-slate-700">{{ $purchaseRequest->to_department }}</span>
                </p>
                <p class="text-xs text-slate-400">
                    Created by <span class="font-medium text-slate-600">{{ $userCreatedBy->name }}</span>
                </p>
            </div>

            {{-- RIGHT SUMMARY / ACTIONS --}}
            <div class="flex flex-col items-end gap-2 text-xs sm:text-sm">
                {{-- Quick total info (single or multi currency) --}}
                <div class="text-right">
                    {{-- We'll compute totals in the table section; here just a label --}}
                    <p class="text-[11px] text-slate-400">
                        Total amount shown in table below
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    {{-- Upload button --}}
                    @if ($canUpload)
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                            @click="openUploadFiles = true">
                            <i class='bx bx-upload text-sm'></i>
                            <span>Upload</span>
                        </button>
                        @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
                    @endif

                    {{-- Edit button --}}
                    @if ($canEditPr)
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                            data-bs-target="#edit-purchase-request-modal-{{ $purchaseRequest->id }}" data-bs-toggle="modal">
                            <i class='bx bx-edit text-sm'></i>
                            <span>Edit</span>
                        </button>
                        @include('partials.edit-purchase-request-modal', [
                            'pr' => $purchaseRequest,
                            'details' => $filteredItemDetail,
                        ])
                    @endif
                    
                    {{-- Approve/Reject buttons --}}
                    @if ($canApprove)
                        <div class="flex gap-2">
                            {{-- Approve Button --}}
                            <form method="POST" action="{{ route('purchase-requests.approve', $purchaseRequest->id) }}"
                                  onsubmit="return confirm('Are you sure you want to approve this Purchase Request?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                                    <i class='bx bx-check-circle text-sm'></i>
                                    <span>Approve</span>
                                </button>
                            </form>
                            
                            {{-- Reject Button --}}
                            <button type="button"
                                    @click="$dispatch('open-reject-modal')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                                <i class='bx bx-x-circle text-sm'></i>
                                <span>Reject</span>
                            </button>
                        </div>
                        
                        {{-- Include Reject Modal --}}
                        @include('partials.pr-reject-modal', ['pr' => $purchaseRequest])
                    @endif
                </div>
            </div>
        </div>

        {{-- MAIN GRID LAYOUT --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- LEFT: PR INFO + ITEMS --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- PR HEADER INFO CARD --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Purchase Request Details
                        </h2>
                    </div>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                            <dl class="divide-y divide-slate-100">
                                <div class="grid grid-cols-1 gap-2 px-4 py-3 text-xs sm:grid-cols-2 sm:text-sm">
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Date PR
                                        </dt>
                                        <dd class="mt-0.5 text-slate-800">
                                            @formatDate($purchaseRequest->date_pr)
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Date
                                            Required</dt>
                                        <dd class="mt-0.5 text-slate-800">
                                            @formatDate($purchaseRequest->date_required)
                                        </dd>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 px-4 py-3 text-xs sm:grid-cols-2 sm:text-sm">
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Branch
                                        </dt>
                                        <dd class="mt-0.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold
                                                  {{ $purchaseRequest->branch === 'JAKARTA' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                <i class='bx bx-buildings text-sm'></i>
                                                {{ $purchaseRequest->branch }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Type</dt>
                                        <dd class="mt-0.5">
                                            @if($purchaseRequest->from_department === 'MOULDING' && $purchaseRequest->to_department->value === 'PURCHASING')
                                                @if($purchaseRequest->is_import === true || $purchaseRequest->is_import === 1)
                                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">
                                                        <i class='bx bx-world text-sm'></i>
                                                        Import
                                                    </span>
                                                @elseif($purchaseRequest->is_import === false || $purchaseRequest->is_import === 0)
                                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">
                                                        <i class='bx bx-home text-sm'></i>
                                                        Local
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-xs">Not specified</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 text-xs">N/A</span>
                                            @endif
                                        </dd>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 px-4 py-3 text-xs sm:grid-cols-2 sm:text-sm">
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Supplier
                                        </dt>
                                        <dd class="mt-0.5 text-slate-800">
                                            {{ $purchaseRequest->supplier }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">PIC</dt>
                                        <dd class="mt-0.5 text-slate-800">
                                            {{ $purchaseRequest->pic }}
                                        </dd>
                                    </div>
                                </div>

                                {{-- PO Number (if available) --}}
                                @if($purchaseRequest->po_number)
                                    <div class="px-4 py-3 text-xs sm:text-sm">
                                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500 mb-1">PO Number
                                        </dt>
                                        <dd>
                                            <div class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-slate-800">
                                                <i class='bx bx-receipt text-lg text-indigo-600'></i>
                                                <span class="font-semibold text-indigo-900">{{ $purchaseRequest->po_number }}</span>
                                            </div>
                                        </dd>
                                    </div>
                                @endif

                                <div class="px-4 py-3 text-xs sm:text-sm">
                                    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500 mb-1">Remark
                                    </dt>
                                    <dd>
                                        <div
                                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-800 text-xs sm:text-sm whitespace-pre-wrap">{{ $purchaseRequest->remark }}
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>
                
                {{-- APPROVAL TIMELINE CARD --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Approval Workflow
                        </h2>
                    </div>
                    @include('partials.pr-approval-timeline', ['pr' => $purchaseRequest])
                </section>

                {{-- ITEMS TABLE CARD --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-6">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Items</h2>
                            <p class="text-[11px] text-slate-500">
                                Status warna membantu membaca hasil approve per item.
                            </p>
                        </div>
                        <div class="hidden items-center gap-2 text-[11px] text-slate-400 sm:flex">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-3 w-3 rounded-full bg-emerald-100 border border-emerald-300"></span>
                                Approved
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-3 w-3 rounded-full bg-rose-100 border border-rose-300"></span>
                                Rejected
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="text-xs line-through text-slate-400">ABC</span>
                                Not counted in total
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto px-2 pb-4 pt-2 sm:px-4">
                        <table class="min-w-full border-collapse text-[11px] sm:text-xs md:text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                        No
                                    </th>
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-left align-middle">
                                        Item Name
                                    </th>
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                        Qty
                                    </th>
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                        UoM
                                    </th>
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-left align-middle">
                                        Purpose
                                    </th>
                                    <th colspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                        Unit Price
                                    </th>
                                    <th rowspan="2"
                                        class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-right align-middle">
                                        Subtotal
                                    </th>

                                    {{-- Show "Is Approve" column depending on roles/status like original --}}
                                    @php
                                        $showIsApproveColumn =
                                            $user->specification?->name === 'DIRECTOR' ||
                                            $user->specification?->name == 'VERIFICATOR' ||
                                            ((($user->department?->name === $purchaseRequest->from_department &&
                                                $user->is_head == 1) ||
                                                ($user->is_head == 1 &&
                                                    $purchaseRequest->from_department == 'STORE')) &&
                                                !$purchaseRequest->is_cancel);
                                    @endphp

                                    @if ($showIsApproveColumn)
                                        @if ($purchaseRequest->from_department === 'MOULDING')
                                            @php
                                                // Keep same logic to decide visibility
                                                $mouldingApprovalCase = false;
                                                $mouldingApprovalCase =
                                                    ($purchaseRequest->is_import === 1 &&
                                                        $user->specification?->name !== 'DESIGN') ||
                                                    (!$purchaseRequest->is_import &&
                                                        $user->specification?->name !== 'DESIGN') ||
                                                    ($purchaseRequest->is_import === 0 &&
                                                        $user->specification?->name === 'DESIGN') ||
                                                    (!$purchaseRequest->is_import &&
                                                        $user->specification?->name === 'DESIGN');

                                                if (
                                                    $purchaseRequest->to_department ===
                                                    \App\Enums\ToDepartment::MAINTENANCE->value
                                                ) {
                                                    $mouldingApprovalCase =
                                                        $mouldingApprovalCase &&
                                                        $purchaseRequest->to_department ===
                                                            \App\Enums\ToDepartment::MAINTENANCE->value;
                                                }
                                            @endphp
                                            <th rowspan="2"
                                                class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle {{ $mouldingApprovalCase ? '' : 'hidden' }}">
                                                Is Approve
                                            </th>
                                        @else
                                            <th rowspan="2"
                                                class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                                Is Approve
                                            </th>
                                        @endif
                                    @endif

                                    {{-- Received columns when status = 4 and createdBy --}}
                                    @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
                                        <th rowspan="2"
                                            class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                            Received Qty
                                        </th>
                                        <th rowspan="2"
                                            class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center align-middle">
                                            Action
                                        </th>
                                    @endif
                                </tr>
                                <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                                    <th class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center">
                                        Before
                                    </th>
                                    <th class="whitespace-nowrap border-b border-slate-200 px-2 py-2 text-center">
                                        Current
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($filteredItemDetail as $detail)
                                    @php
                                        // Map approval status to row colors (similar to original)
                                        $rowClass = '';
                                        if ($detail->is_approve === 1) {
                                            $rowClass = 'bg-emerald-50';
                                        } elseif ($detail->is_approve === 0) {
                                            $rowClass = 'bg-rose-50 line-through text-slate-400';
                                        } elseif (is_null($detail->is_approve)) {
                                            if ($user->specification?->name === 'DIRECTOR') {
                                                // no extra color, director sees final only
                                            } elseif ($detail->is_approve_by_verificator === 1) {
                                                $rowClass = 'bg-emerald-50';
                                            } elseif ($detail->is_approve_by_verificator === 0) {
                                                $rowClass = 'bg-rose-50 line-through text-slate-400';
                                            } elseif ($user->specification?->name === 'VERIFICATOR') {
                                                // no color
                                            } else {
                                                if ($detail->is_approve_by_head === 1) {
                                                    $rowClass = 'bg-emerald-50';
                                                } elseif ($detail->is_approve_by_head === 0) {
                                                    $rowClass = 'bg-rose-50 line-through text-slate-400';
                                                }
                                            }
                                        }

                                        $subtotal = $detail->quantity * $detail->price;

                                        // Dept head item approval visibility logic
                                        $showDeptHeadItemApprove =
                                            $user->department?->name === $purchaseRequest->from_department &&
                                            $user->is_head == 1;
                                        if ($user->is_head == 1 && $purchaseRequest->from_department === 'STORE') {
                                            $showDeptHeadItemApprove = true;
                                        } elseif (
                                            $purchaseRequest->from_department === 'PERSONALIA' &&
                                            ($user->department?->name === 'PERSONALIA' && $user->is_head === 1)
                                        ) {
                                            $showDeptHeadItemApprove = true;
                                        }

                                        // Received cell color
                                        $receivedTdColor = '';
                                        if ($detail->quantity > 1 && $detail->quantity && $detail->is_approve === 1) {
                                            if ($detail->received_quantity === $detail->quantity) {
                                                $receivedTdColor = 'bg-emerald-50';
                                            } else {
                                                $receivedTdColor = 'bg-amber-50';
                                            }
                                        }
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="whitespace-nowrap px-2 py-2 text-left align-middle">
                                            {{ $detail->item_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                            {{ $detail->quantity }}
                                        </td>
                                        <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                            {{ $detail->uom }}
                                        </td>
                                        <td class="px-2 py-2 text-left align-middle">
                                            {{ $detail->purpose }}
                                        </td>

                                        {{-- Unit price before --}}
                                        <td class="whitespace-nowrap px-2 py-2 text-right align-middle">
                                            @if ($detail->master)
                                                @if ($detail->currency === 'USD')
                                                    @currencyUSD($detail->master->price)
                                                @elseif($detail->currency === 'CNY')
                                                    @currencyCNY($detail->master->price)
                                                @else
                                                    @currency($detail->master->price)
                                                @endif
                                            @else
                                                <span class="text-slate-400">N/A</span>
                                            @endif
                                        </td>

                                        {{-- Unit price current --}}
                                        <td class="whitespace-nowrap px-2 py-2 text-right align-middle">
                                            @if ($detail->currency === 'USD')
                                                @currencyUSD($detail->price)
                                            @elseif($detail->currency === 'CNY')
                                                @currencyCNY($detail->price)
                                            @else
                                                @currency($detail->price)
                                            @endif
                                        </td>

                                        {{-- Subtotal --}}
                                        <td class="whitespace-nowrap px-2 py-2 text-right align-middle">
                                            @if ($detail->currency === 'USD')
                                                @currencyUSD($subtotal)
                                            @elseif($detail->currency === 'CNY')
                                                @currencyCNY($subtotal)
                                            @else
                                                @currency($subtotal)
                                            @endif
                                        </td>

                                        {{-- Approve / reject per item --}}
                                        @if (!$purchaseRequest->is_cancel && $showIsApproveColumn)
                                            @if ($user->specification?->name === 'DIRECTOR' && $purchaseRequest->status === 3)
                                                <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                    @if ($detail->is_approve === null)
                                                        <div class="inline-flex gap-1">
                                                            <a href="{{ route('purchase-requests.items.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                                                                class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                Reject
                                                            </a>
                                                            <a href="{{ route('purchase-requests.items.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                                                                class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                                Approve
                                                            </a>
                                                        </div>
                                                    @else
                                                        {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->specification?->name == 'VERIFICATOR' && $purchaseRequest->status === 2)
                                                <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                    @if ($detail->is_approve_by_verificator === null)
                                                        <div class="inline-flex gap-1">
                                                            <a href="{{ route('purchase-requests.items.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                                class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                Reject
                                                            </a>
                                                            <a href="{{ route('purchase-requests.items.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                                class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                                Approve
                                                            </a>
                                                        </div>
                                                    @else
                                                        {{ $detail->is_approve_by_verificator == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($showDeptHeadItemApprove && $purchaseRequest->status === 1)
                                                @if ($purchaseRequest->from_department === 'MOULDING')
                                                    <td
                                                        class="whitespace-nowrap px-2 py-2 text-center align-middle {{ $mouldingApprovalCase ? '' : 'hidden' }}">
                                                        @if ($detail->is_approve_by_head === null)
                                                            <div class="inline-flex gap-1">
                                                                <a href="{{ route('purchase-requests.items.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                    Reject
                                                                </a>
                                                                <a href="{{ route('purchase-requests.items.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                                    Approve
                                                                </a>
                                                            </div>
                                                        @else
                                                            {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                        @if ($detail->is_approve_by_head === null)
                                                            <div class="inline-flex gap-1">
                                                                <a href="{{ route('purchase-requests.items.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                    Reject
                                                                </a>
                                                                <a href="{{ route('purchase-requests.items.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                                    Approve
                                                                </a>
                                                            </div>
                                                        @else
                                                            {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                                                        @endif
                                                    </td>
                                                @endif
                                            @endif
                                        @endif

                                        {{-- Received Qty + Action (status 4, creator only) --}}
                                        @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
                                            <td
                                                class="whitespace-nowrap px-2 py-2 text-center align-middle {{ $receivedTdColor }}">
                                                {{ $detail->received_quantity }} of {{ $detail->quantity }}
                                                @if ($detail->received_quantity === $detail->quantity && $detail->is_approve === 1)
                                                    <span
                                                        class="ml-1 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-800">
                                                        Complete
                                                    </span>
                                                @elseif($detail->received_quantity > 0 && $detail->is_approve === 1)
                                                    <span
                                                        class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800">
                                                        Partial
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                @include('partials.edit-purchase-request-received-modal')
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-indigo-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-40"
                                                    data-bs-target="#edit-purchase-request-received-{{ $detail->id }}"
                                                    data-bs-toggle="modal"
                                                    {{ $detail->is_approve !== 1 ? 'disabled' : '' }}>
                                                    Edit
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-2 py-4 text-center text-xs text-slate-400 sm:text-sm">
                                            No Data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 text-xs text-slate-700 sm:text-sm">
                                    <td colspan="7" class="px-2 py-2 text-right font-semibold">
                                        Total
                                    </td>
                                    <td class="px-2 py-2 font-semibold">
                                        @if (!$isThereAnyCurrencyDifference)
                                            @if ($prevCurrency === 'USD')
                                                @currencyUSD($totalall ?? 0)
                                            @elseif($prevCurrency === 'CNY')
                                                @currencyCNY($totalall ?? 0)
                                            @else
                                                @currency($totalall ?? 0)
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800">
                                                There is currency difference!
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            </div>

            {{-- RIGHT: STATUS / AUTOGRAPHS / ATTACHMENTS --}}
            <div class="space-y-6">
                {{-- STATUS SUMMARY CARD (light) --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Status Summary
                        </h2>
                    </div>
                    <div class="px-4 py-4 space-y-3 text-xs sm:text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Branch</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                {{ $purchaseRequest->branch ?? '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">PR No</span>
                            <span class="font-medium text-slate-800">{{ $purchaseRequest->pr_no ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Created By</span>
                            <span class="font-medium text-slate-800">{{ $userCreatedBy->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Draft</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                                    {{ $purchaseRequest->is_draft ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $purchaseRequest->is_draft ? 'Yes (Draft)' : 'No (Final)' }}
                            </span>
                        </div>
                    </div>
                </section>

                {{-- APPROVAL SUMMARY (no duplicate signatures) --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Approval Summary
                        </h2>
                        <p class="mt-1 text-[11px] text-slate-500">
                            Ringkasan status approval (detail ada di panel kiri).
                        </p>
                    </div>

                    <div class="px-4 py-4 space-y-3 text-xs sm:text-sm">
                        @if (!$approval)
                            <div class="text-xs text-slate-500">
                                Belum ada workflow approval untuk PR ini.
                            </div>
                        @else
                            @php
                                $steps = $approval->steps->sortBy('sequence');
                                $currentStep = $steps->firstWhere('sequence', (int) $approval->current_step);
                                $currentStatus = $approval->status;
                                $pendingCount = $steps->where('status', 'PENDING')->count();
                                $approvedCount = $steps->where('status', 'APPROVED')->count();
                                $rejectedCount = $steps->where('status', 'REJECTED')->count();

                                // Label current approver (role/user)
                                $currentApprover = null;
                                if ($currentStep) {
                                    if ($currentStep->approver_type === 'role') {
                                        $role = \Spatie\Permission\Models\Role::find($currentStep->approver_id);
                                        $currentApprover = $role?->name ?? 'Unknown role';
                                    } else {
                                        $u = \App\Infrastructure\Persistence\Eloquent\Models\User::find(
                                            $currentStep->approver_id,
                                        );
                                        $currentApprover = $u?->name ?? 'User #' . $currentStep->approver_id;
                                    }
                                }

                                $map = [
                                    'pr-dept-head-office' => 'Dept Head (Office)',
                                    'pr-dept-head-factory' => 'Dept Head (Factory)',
                                    'pr-head-design' => 'Head Design',
                                    'pr-gm-factory' => 'General Manager',
                                    'pr-verificator-personalia' => 'Verificator Personalia',
                                    'pr-verificator-computer' => 'Verificator Computer',
                                    'pr-purchaser' => 'Purchaser',
                                    'pr-director' => 'Director',
                                ];
                                $prettyApprover =
                                    $currentApprover && isset($map[$currentApprover])
                                        ? $map[$currentApprover]
                                        : $currentApprover;

                                $statusPill =
                                    $currentStatus === 'APPROVED'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : ($currentStatus === 'REJECTED'
                                            ? 'bg-rose-100 text-rose-800'
                                            : ($currentStatus === 'IN_REVIEW'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-slate-100 text-slate-700'));
                            @endphp

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Workflow Status</span>
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusPill }}">
                                    {{ $approval->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Current Step</span>
                                <span class="font-medium text-slate-800">
                                    {{ $currentStep ? 'Step ' . $currentStep->sequence : '-' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Waiting For</span>
                                <span class="font-medium text-slate-800 text-right">
                                    {{ $prettyApprover ?? '-' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2">
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-2 text-center">
                                    <div class="text-[11px] text-slate-500">Approved</div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $approvedCount }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-2 text-center">
                                    <div class="text-[11px] text-slate-500">Pending</div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $pendingCount }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-2 text-center">
                                    <div class="text-[11px] text-slate-500">Rejected</div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $rejectedCount }}</div>
                                </div>
                            </div>

                            <div class="pt-3 text-[11px] text-slate-400">
                                Detail step + signature bisa dilihat di panel “Approval Workflow” sebelah kiri.
                            </div>
                        @endif
                    </div>
                </section>


                {{-- UPLOADED FILES --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Attachments
                        </h2>
                        <p class="mt-1 text-[11px] text-slate-500">
                            File pendukung PR (quotation, gambar, dsb).
                        </p>
                    </div>
                    <div class="px-4 py-4">
                        {{-- existing partial, wrap in Tailwind container --}}
                        @include('partials.uploaded-section', [
                            'showDeleteButton' =>
                                ($user->id === $userCreatedBy->id && $purchaseRequest->status === 1) ||
                                ($user->specification?->name === 'PURCHASER' && $purchaseRequest->status === 6),
                        ])
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('prDetailPage', (initialSlots, canAutoApprove, prId, csrfToken) => ({
                // Upload modal control
                openUploadFiles: false,
                
                // Legacy slots data (kept for backward compatibility with any remaining references)
                slots: initialSlots ?? [],
                canAutoApprove: !!canAutoApprove,
                prId,
                csrfToken,
            }));
        });
    </script>
@endpush
