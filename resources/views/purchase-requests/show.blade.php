@extends('new.layouts.app')

@section('content')
    @php
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Build autograph slots as a simple array for Alpine
        $autographSlots = collect(range(1, 7))->map(function ($i) use ($purchaseRequest) {
            return [
                'slot' => $i,
                'image' => $purchaseRequest->{'autograph_' . $i} ?? null,
                'user_name' => $purchaseRequest->{'autograph_user_' . $i} ?? null,
            ];
        });

        $canAutoApprove =
            $user->is_gm ||
            $user->specification->name === 'PURCHASER' ||
            $purchaseRequest->from_department === 'MOULDING';

        // We will reuse your existing PHP logic for totals & currency
        $totalall = 0;
        $isThereAnyCurrencyDifference = false;
        $prevCurrency = null;
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-6 lg:py-8" x-data="prDetailPage(@js($autographSlots), @js($canAutoApprove), @js($purchaseRequest->id), @js(csrf_token()))">

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
                    <div class="inline-flex items-center">
                        @include('partials.pr-status-badge', ['pr' => $purchaseRequest])
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
                    @if ($user->id == $userCreatedBy->id || $user->specification->name === 'PURCHASER' || $user->is_head === 1)
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                            data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                            <i class='bx bx-upload text-sm'></i>
                            <span>Upload</span>
                        </button>

                        {{-- existing partial (convert to Tailwind later if needed) --}}
                        @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
                    @endif

                    {{-- Edit button --}}
                    @php
                        // You can move this complex condition into a helper like canEditPr($user, $purchaseRequest)
                        $canEditPr =
                            ($purchaseRequest->user_id_create === $user->id && $purchaseRequest->status === 1) ||
                            ($purchaseRequest->status === 1 && $user->is_head) ||
                            ($purchaseRequest->status === 6 && $user->specification->name === 'PURCHASER') ||
                            (($purchaseRequest->status === 2 &&
                                $user->department->name == 'PERSONALIA' &&
                                $user->is_head === 1) ||
                                ($purchaseRequest->status === 7 && $user->is_gm));
                    @endphp

                    @if ($canEditPr)
                        @include('partials.edit-purchase-request-modal', [
                            'pr' => $purchaseRequest,
                            'details' => $filteredItemDetail,
                        ])

                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                            data-bs-target="#edit-purchase-request-modal-{{ $purchaseRequest->id }}" data-bs-toggle="modal">
                            <i class='bx bx-edit text-sm'></i>
                            <span>Edit</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- REJECT MODAL (EXISTING PARTIAL) --}}
        <div class="mb-4">
            @include('partials.reject-pr-confirmation', $purchaseRequest)
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

                                <div class="px-4 py-3 text-xs sm:text-sm">
                                    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500 mb-1">Remark
                                    </dt>
                                    <dd>
                                        <div
                                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-800 text-xs sm:text-sm whitespace-pre-wrap">{{ $purchaseRequest->remark }}</div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>
                <div class="mt-4">
                    @include('approval._pr-approval-panel', [
                        'approval' => $approval,
                        'purchaseRequest' => $purchaseRequest,
                        'canApprove' => $canApprove ?? false,
                    ])

                </div>

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
                                            $user->specification->name === 'DIRECTOR' ||
                                            $user->specification->name == 'VERIFICATOR' ||
                                            ((($user->department->name === $purchaseRequest->from_department &&
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
                                                        $user->specification->name !== 'DESIGN') ||
                                                    (!$purchaseRequest->is_import &&
                                                        $user->specification->name !== 'DESIGN') ||
                                                    ($purchaseRequest->is_import === 0 &&
                                                        $user->specification->name === 'DESIGN') ||
                                                    (!$purchaseRequest->is_import &&
                                                        $user->specification->name === 'DESIGN');

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
                                        // Detect currency difference
                                        if (!isset($prevCurrency)) {
                                            $prevCurrency = $detail->currency;
                                        } elseif ($prevCurrency != $detail->currency) {
                                            $isThereAnyCurrencyDifference = true;
                                        }

                                        // Map approval status to row colors (similar to original)
                                        $rowClass = '';
                                        if ($detail->is_approve === 1) {
                                            $rowClass = 'bg-emerald-50';
                                        } elseif ($detail->is_approve === 0) {
                                            $rowClass = 'bg-rose-50 line-through text-slate-400';
                                        } elseif (is_null($detail->is_approve)) {
                                            if ($user->specification->name === 'DIRECTOR') {
                                                // no extra color, director sees final only
                                            } elseif ($detail->is_approve_by_verificator === 1) {
                                                $rowClass = 'bg-emerald-50';
                                            } elseif ($detail->is_approve_by_verificator === 0) {
                                                $rowClass = 'bg-rose-50 line-through text-slate-400';
                                            } elseif ($user->specification->name === 'VERIFICATOR') {
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

                                        // ORIGINAL TOTAL LOGIC (kept as-is)
                                        if ($purchaseRequest->status === 6 || $purchaseRequest->status === 7) {
                                            if (!is_null($detail->is_approve_by_head)) {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $subtotal;
                                                }
                                            } else {
                                                $totalall += $subtotal;
                                            }
                                        } elseif ($purchaseRequest->status === 2) {
                                            if (!is_null($detail->is_approve_by_verificator)) {
                                                if ($detail->is_approve_by_verificator) {
                                                    $totalall += $subtotal;
                                                }
                                            } else {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $subtotal;
                                                }
                                            }
                                        } elseif ($purchaseRequest->status === 3) {
                                            if (!is_null($detail->is_approve)) {
                                                if ($detail->is_approve) {
                                                    $totalall += $subtotal;
                                                }
                                            } else {
                                                if (
                                                    $purchaseRequest->type === 'office' ||
                                                    ($purchaseRequest->to_department->value === 'COMPUTER' &&
                                                        $purchaseRequest->type === 'factory')
                                                ) {
                                                    if ($detail->is_approve_by_verificator) {
                                                        $totalall += $subtotal;
                                                    }
                                                } elseif ($detail->is_approve_by_gm) {
                                                    $totalall += $subtotal;
                                                }
                                            }
                                        } elseif ($purchaseRequest->status === 4) {
                                            if ($detail->is_approve) {
                                                $totalall += $subtotal;
                                            }
                                        } elseif ($purchaseRequest->status === 1) {
                                            if (!is_null($detail->is_approve_by_head)) {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $subtotal;
                                                }
                                            } else {
                                                $totalall += $subtotal;
                                            }
                                        } else {
                                            $totalall += 0;
                                        }

                                        // Dept head item approval visibility logic
                                        $showDeptHeadItemApprove =
                                            $user->department->name === $purchaseRequest->from_department &&
                                            $user->is_head == 1;
                                        if ($user->is_head == 1 && $purchaseRequest->from_department === 'STORE') {
                                            $showDeptHeadItemApprove = true;
                                        } elseif (
                                            $purchaseRequest->from_department === 'PERSONALIA' &&
                                            ($user->department->name === 'PERSONALIA' && $user->is_head === 1)
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
                                            @if ($user->specification->name === 'DIRECTOR' && $purchaseRequest->status === 3)
                                                <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                    @if ($detail->is_approve === null)
                                                        <div class="inline-flex gap-1">
                                                            <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                                                                class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                Reject
                                                            </a>
                                                            <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                                                                class="inline-flex items-center rounded-md bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                                                                Approve
                                                            </a>
                                                        </div>
                                                    @else
                                                        {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->specification->name == 'VERIFICATOR' && $purchaseRequest->status === 2)
                                                <td class="whitespace-nowrap px-2 py-2 text-center align-middle">
                                                    @if ($detail->is_approve_by_verificator === null)
                                                        <div class="inline-flex gap-1">
                                                            <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                                class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                Reject
                                                            </a>
                                                            <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
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
                                                                <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                    Reject
                                                                </a>
                                                                <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
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
                                                                <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
                                                                    Reject
                                                                </a>
                                                                <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
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

                {{-- AUTOGRAPHS (SIGNATURES) --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Approval Signatures
                        </h2>
                        <p class="mt-1 text-[11px] text-slate-500">
                            Klik <span class="font-semibold">Sign</span> untuk menambahkan tanda tangan digital Anda.
                        </p>
                    </div>
                    <div class="px-4 py-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <template x-for="slot in slots" :key="slot.slot">
                                <div class="flex flex-col rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                                            Position <span x-text="slot.slot"></span>
                                        </span>
                                        <span class="text-[10px] uppercase tracking-wide"
                                            :class="slot.image ? 'text-emerald-600' : 'text-slate-400'">
                                            <span x-text="slot.image ? 'Signed' : 'Pending'"></span>
                                        </span>
                                    </div>

                                    <div
                                        class="mt-2 flex h-20 items-center justify-center overflow-hidden rounded-lg border border-dashed border-slate-300 bg-white">
                                        <img x-show="slot.image" :src="`/autographs/${slot.image}`" alt=""
                                            class="max-h-full">
                                        <span x-show="!slot.image" class="text-[11px] text-slate-400">
                                            No signature
                                        </span>
                                    </div>

                                    <p x-show="slot.user_name" class="mt-2 truncate text-xs font-medium text-slate-700"
                                        x-text="slot.user_name"></p>

                                    <button type="button" x-show="!slot.image && canSign"
                                        @click="addAutograph(slot.slot)"
                                        class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-black">
                                        Sign as {{ $user->name }}
                                    </button>
                                </div>
                            </template>
                        </div>
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
                                ($user->specification->name === 'PURCHASER' && $purchaseRequest->status === 6),
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
                slots: initialSlots ?? [],
                canAutoApprove: !!canAutoApprove,
                prId,
                csrfToken,
                // if you want to restrict who can sign which slot, add logic here
                get canSign() {
                    return true;
                },
                async addAutograph(section) {
                    // signed image assumed to be saved as "<username>.png" in /public
                    const username = @js(auth()->check() ? auth()->user()->name : null);
                    if (!username) return;

                    const imagePath = username + '.png';
                    const fullUrl = '{{ asset(':path') }}'.replace(':path', imagePath);

                    try {
                        // Save signature path
                        await fetch(`/save-signature-path/${this.prId}/${section}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                imagePath: fullUrl
                            }),
                        });

                        // Update local state so UI reflects immediately
                        const idx = this.slots.findIndex(s => s.slot === section);
                        if (idx !== -1) {
                            this.slots[idx].image = imagePath;
                            this.slots[idx].user_name = username;
                        }

                        // Auto-approve all details if allowed (same as your original logic)
                        if (this.canAutoApprove) {
                            await fetch(`/approveAllDetailItems/${this.prId}/GM`);
                        }

                        // Reload to sync server state (status, etc.)
                        window.location.reload();
                    } catch (e) {
                        console.error(e);
                        alert('Failed to sign. Please try again.');
                    }
                },
            }));
        });
    </script>
@endpush
