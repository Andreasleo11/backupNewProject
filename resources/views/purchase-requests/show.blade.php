@extends('new.layouts.app')

@section('title', 'Purchase Request Details - ' . $purchaseRequest->doc_num)

@section('content')
    @php
        $canApprove = $flags['canApprove'] ?? false;
        $canUpload = $flags['canUpload'] ?? false;
        $canEditPr = $flags['canEdit'] ?? false;
        $canSignAndSubmit = $flags['canSignAndSubmit'] ?? false;
        $hasDefaultSig = $flags['hasDefaultSignature'] ?? false;
        $totalall = (float) ($totals['total'] ?? 0);
        $isThereAnyCurrencyDifference = (bool) ($totals['hasCurrencyDiff'] ?? false);
        $prevCurrency = $totals['currency'] ?? null;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">

        {{-- HEADER SECTION --}}
        <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                {{-- Back Link --}}
                <a href="{{ route('purchase-requests.index') }}"
                   class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 transition-colors hover:text-indigo-600">
                    <i class="bi bi-arrow-left text-lg transition-transform group-hover:-translate-x-1"></i>
                    Back to List
                </a>

                {{-- Title & Doc Num --}}
                <div class="flex flex-wrap items-center gap-4">
                    <h1 class="text-4xl font-black tracking-tight text-slate-800">
                        {{ $purchaseRequest->doc_num }}
                    </h1>
                    
                    {{-- Status Badge --}}
                    @include('partials.workflow-status-badge', ['record' => $purchaseRequest])

                    <!-- @if($purchaseRequest->status === 'draft' || $purchaseRequest->status === 0)
                         <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                            <i class="bi bi-pencil-square"></i> Draft
                        </span>
                    @endif -->
                </div>

                {{-- Metadata Strip --}}
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700">
                            {{ substr($userCreatedBy->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="font-medium text-slate-700">{{ $userCreatedBy->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                    <div>
                        From <span class="font-bold text-slate-700">{{ $purchaseRequest->from_department }}</span>
                        <span class="text-slate-400">({{ $fromDeptNo ?? '-' }})</span>
                    </div>
                    <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                    <div>
                        To <span class="font-bold text-slate-700">{{ $purchaseRequest->to_department }}</span>
                    </div>
                </div>
            </div>

            {{-- Top Actions --}}
            <div class="flex flex-wrap items-center gap-3">
                @if(auth()->user()->hasRole('super-admin'))
                    <button type="button" @click="$dispatch('open-audit-drawer')"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:text-indigo-600"
                            title="View Audit Log">
                        <i class="bi bi-clock-history text-lg text-slate-400 group-hover:text-indigo-500"></i>
                        <span class="hidden sm:inline">History</span>
                    </button>
                @endif

                @if ($canUpload)
                    <button type="button" @click="$dispatch('open-upload-modal')"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-indigo-200 hover:text-indigo-600 hover:shadow-md">
                        <i class="bi bi-cloud-upload text-lg text-slate-400 group-hover:text-indigo-500"></i>
                        Upload Files
                    </button>
                @endif

                @if ($canEditPr)
                    <a href="{{ route('purchase-requests.edit', $purchaseRequest->id) }}"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:text-indigo-600">
                        <i class="bi bi-pencil-square text-lg text-slate-400 group-hover:text-indigo-500"></i>
                        Edit Details
                    </a>
                @endif

                @if ($canSignAndSubmit)
                    <button type="button"
                            @click="$dispatch('open-sign-submit-modal')"
                            class="group inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-indigo-200 transition-all hover:bg-indigo-700 hover:-translate-y-0.5">
                        <i class="bi bi-pen text-lg"></i>
                        Sign & Submit
                    </button>
                @endif
                
                <!-- {{-- If it's pure draft, show delete --}}
                @if(($purchaseRequest->status === 'draft' || $purchaseRequest->status === 0) && auth()->id() === $purchaseRequest->created_by)
                    <form action="{{ route('purchase-requests.destroy', $purchaseRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="group inline-flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-600 shadow-sm transition-all hover:bg-rose-100 hover:border-rose-200">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                @endif -->
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            
            {{-- LEFT COLUMN: CONTENT --}}
            <div class="space-y-8 lg:col-span-2">
                
                {{-- 1. General Info Card --}}
                <div class="glass-card overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                         <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800">
                            <i class="bi bi-info-circle text-indigo-500"></i> General Information
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <div class="p-6 space-y-6">
                            {{-- Dates --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Date PR</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        {{ $purchaseRequest->date_pr ? \Carbon\Carbon::parse($purchaseRequest->date_pr)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Date Required</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        {{ $purchaseRequest->date_required ? \Carbon\Carbon::parse($purchaseRequest->date_required)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Branch & Type --}}
                             <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Branch</p>
                                    <div class="mt-1">
                                         <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold {{ $purchaseRequest->branch === 'JAKARTA' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            <i class="bi bi-buildings"></i> {{ $purchaseRequest->branch }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Type</p>
                                    <div class="mt-1">
                                        @if($purchaseRequest->from_department === 'MOULDING' && $purchaseRequest->to_department->value === 'PURCHASING')
                                            @if($purchaseRequest->is_import === true || $purchaseRequest->is_import === 1)
                                                 <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-bold text-amber-800">
                                                    <i class="bi bi-globe-americas"></i> Import
                                                </span>
                                            @elseif($purchaseRequest->is_import === false || $purchaseRequest->is_import === 0)
                                                 <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-2.5 py-1.5 text-xs font-bold text-emerald-800">
                                                    <i class="bi bi-house-door"></i> Local
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">Regular</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Supplier & PIC --}}
                             <div class="space-y-4">
                                <div>
                                     <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Supplier</p>
                                     <p class="mt-1 text-sm font-semibold text-slate-800 truncate" title="{{ $purchaseRequest->supplier }}">
                                         {{ $purchaseRequest->supplier ?? '-' }}
                                     </p>
                                </div>
                                <div>
                                     <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">PIC</p>
                                     <p class="mt-1 text-sm font-semibold text-slate-800">
                                         {{ $purchaseRequest->pic ?? '-' }}
                                     </p>
                                </div>
                            </div>

                            @if($purchaseRequest->po_number)
                                <div class="pt-4 border-t border-slate-100">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">PO Number</p>
                                    <div class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-bold text-indigo-700 border border-indigo-100">
                                        <i class="bi bi-receipt"></i> {{ $purchaseRequest->po_number }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($purchaseRequest->remark)
                        <div class="border-t border-slate-100 bg-slate-50/30 p-6">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-2">Remarks</p>
                            <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600 italic shadow-sm">
                                "{{ $purchaseRequest->remark }}"
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. Items Table Card --}}
                <div class="glass-card overflow-hidden">
                     <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800">
                            <i class="bi bi-box-seam text-indigo-500"></i> Request Items
                        </h3>
                        
                        {{-- Legend --}}
                        <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Approved
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span> Rejected
                            </div>
                             <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Pending
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                    <th class="px-4 py-3 text-center w-10">No</th>
                                    <th class="px-4 py-3">Item Details</th>
                                    <th class="px-4 py-3 text-center">Qty / UoM</th>
                                    <th class="px-4 py-3 text-right">Unit Price</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    
                                    {{-- APPROVAL COLUMN (Always Visible) --}}
                                    <th class="px-4 py-3 text-center w-48">Approvals</th>

                                    @if ($purchaseRequest->status === 4 && auth()->user()->id === $purchaseRequest->createdBy->id)
                                         <th class="px-4 py-3 text-center">Received</th>
                                         <th class="px-4 py-3 text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($filteredItemDetail as $detail)
                                    @php
                                        // Row coloring logic
                                        $rowClass = 'group hover:bg-slate-50/50 transition-colors';
                                        
                                        // Calc subtotal
                                        $subtotal = $detail->quantity * $detail->price;
                                        
                                        // Logic for 'Dept Head Item Approve'
                                        $showDeptHeadItemApprove = auth()->user()->department?->name === $purchaseRequest->from_department && auth()->user()->hasRole('HEAD');
                                        if (auth()->user()->hasRole('HEAD') && $purchaseRequest->from_department === 'STORE') {
                                            $showDeptHeadItemApprove = true;
                                        } elseif (
                                            $purchaseRequest->from_department === 'PERSONALIA' &&
                                            (auth()->user()->department?->name === 'PERSONALIA' && auth()->user()->hasRole('HEAD'))
                                        ) {
                                            $showDeptHeadItemApprove = true;
                                        }
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td class="px-4 py-4 text-center text-xs font-bold text-slate-500">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-bold text-slate-800">{{ $detail->item_name }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $detail->purpose }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                                {{ $detail->quantity }} <span class="mx-1 text-slate-300">|</span> {{ $detail->uom }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex flex-col items-end">
                                                 <span class="text-sm font-medium text-slate-700">
                                                    @if ($detail->currency === 'USD')
                                                        @currencyUSD($detail->price)
                                                    @elseif($detail->currency === 'CNY')
                                                        @currencyCNY($detail->price)
                                                    @else
                                                        @currency($detail->price)
                                                    @endif
                                                </span>
                                                @if($detail->master && $detail->master->price != $detail->price)
                                                    <span class="text-[10px] text-slate-400 line-through">
                                                        {{ number_format($detail->master->price, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-right font-bold text-slate-800">
                                            @if ($detail->currency === 'USD')
                                                @currencyUSD($subtotal)
                                            @elseif($detail->currency === 'CNY')
                                                @currencyCNY($subtotal)
                                            @else
                                                @currency($subtotal)
                                            @endif
                                        </td>

                                        {{-- APPROVAL ACTIONS / STATUS CELLS --}}
                                        <td class="px-4 py-4 text-center align-middle">
                                            <div class="flex flex-col items-center gap-2">
                                                
                                                {{-- Status Indicators (Dynamic based on Workflow) --}}
                                                <div class="flex items-center gap-1 text-[10px] uppercase font-bold tracking-wider text-slate-400">
                                                    @php
                                                        $steps = $approval?->steps->sortBy('sequence')->filter(fn($s) => !is_null($s->item_approver_type));
                                                    @endphp

                                                    @if($steps && $steps->isNotEmpty())
                                                        @foreach($steps as $step)
                                                            @php
                                                                $type = $step->item_approver_type;
                                                                $label = match($type) {
                                                                    'head' => 'Head',
                                                                    'gm' => 'GM',
                                                                    'verificator' => 'Verif',
                                                                    'director' => 'Dir',
                                                                    default => $type
                                                                };
                                                                $column = match($type) {
                                                                    'head' => 'is_approve_by_head',
                                                                    'gm' => 'is_approve_by_gm',
                                                                    'verificator' => 'is_approve_by_verificator',
                                                                    'director' => 'is_approve',
                                                                    default => null
                                                                };
                                                            @endphp

                                                            @if($column)
                                                                <div class="flex flex-col items-center gap-0.5" title="{{ $step->approver_label }}">
                                                                    <span class="text-[9px]">{{ $label }}</span>
                                                                    @if($detail->$column === 1)
                                                                        <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                                                    @elseif($detail->$column === 0)
                                                                        <i class="bi bi-x-circle-fill text-rose-500 text-base"></i>
                                                                    @else
                                                                        <i class="bi bi-circle text-slate-300 text-base"></i>
                                                                    @endif
                                                                </div>

                                                                @if(!$loop->last)
                                                                    <div class="h-px w-2 bg-slate-200 mt-3"></div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        {{-- Fallback for legacy data (No Workflow) --}}
                                                        {{-- HEAD --}}
                                                        <div class="flex flex-col items-center gap-0.5" title="Department Head">
                                                            <span class="text-[9px]">Head</span>
                                                            @if($detail->is_approve_by_head === 1)
                                                                <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                                            @elseif($detail->is_approve_by_head === 0)
                                                                <i class="bi bi-x-circle-fill text-rose-500 text-base"></i>
                                                            @else
                                                                <i class="bi bi-circle text-slate-300 text-base"></i>
                                                            @endif
                                                        </div>

                                                        @if(isset($detail->is_approve_by_gm))
                                                            <div class="h-px w-2 bg-slate-200 mt-3"></div>
                                                            <div class="flex flex-col items-center gap-0.5" title="General Manager">
                                                                <span class="text-[9px]">GM</span>
                                                                @if($detail->is_approve_by_gm === 1)
                                                                    <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                                                @elseif($detail->is_approve_by_gm === 0)
                                                                    <i class="bi bi-x-circle-fill text-rose-500 text-base"></i>
                                                                @else
                                                                    <i class="bi bi-circle text-slate-300 text-base"></i>
                                                                @endif
                                                            </div>
                                                        @endif
        
                                                        <div class="h-px w-2 bg-slate-200 mt-3"></div>
        
                                                        {{-- VERIFICATOR --}}
                                                        <div class="flex flex-col items-center gap-0.5" title="Verificator">
                                                            <span class="text-[9px]">Verif</span>
                                                            @if($detail->is_approve_by_verificator === 1)
                                                                <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                                            @elseif($detail->is_approve_by_verificator === 0)
                                                                <i class="bi bi-x-circle-fill text-rose-500 text-base"></i>
                                                            @else
                                                                <i class="bi bi-circle text-slate-300 text-base"></i>
                                                            @endif
                                                        </div>
        
                                                        <div class="h-px w-2 bg-slate-200 mt-3"></div>
        
                                                        {{-- DIRECTOR --}}
                                                        <div class="flex flex-col items-center gap-0.5" title="Director">
                                                            <span class="text-[9px]">Dir</span>
                                                            @if($detail->is_approve === 1)
                                                                <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                                                            @elseif($detail->is_approve === 0)
                                                                <i class="bi bi-x-circle-fill text-rose-500 text-base"></i>
                                                            @else
                                                                <i class="bi bi-circle text-slate-300 text-base"></i>
                                                            @endif
                                                        </div>
                                                        
                                                    @endif
                                                </div>

                                                {{-- ACTION BUTTONS --}}
                                                @can('approve', $detail)
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <form method="POST" action="{{ route('purchase-requests.items.approve', $detail) }}">
                                                            @csrf
                                                            <button type="submit"  onclick="return confirm('Approve {{ $detail->item_name }}?')" 
                                                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Approve">
                                                                <i class="bi bi-check-lg text-sm"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('purchase-requests.items.reject', $detail) }}">
                                                            @csrf
                                                            <button type="submit" onclick="return confirm('Reject {{ $detail->item_name }}?')" 
                                                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Reject">
                                                                <i class="bi bi-x-lg text-sm"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endcan

                                            </div>
                                        </td>

                                        {{-- Received Column --}}
                                        @if ($purchaseRequest->status === 4 && auth()->user()->id === $purchaseRequest->createdBy->id)
                                             <td class="px-4 py-4 text-center">
                                                {{ $detail->received_quantity }} / {{ $detail->quantity }}
                                             </td>
                                             <td class="px-4 py-4 text-center">
                                                 <button class="text-xs font-bold text-indigo-600 hover:underline"
                                                         data-bs-target="#edit-purchase-request-received-{{ $detail->id }}"
                                                         data-bs-toggle="modal">
                                                     Update
                                                 </button>
                                                  @include('partials.edit-purchase-request-received-modal')
                                             </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                                    <i class="bi bi-basket text-xl"></i>
                                                </div>
                                                <p class="mt-2 text-sm font-medium text-slate-500">No items found in this request.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-50/50">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Total Amount</td>
                                    <td class="px-6 py-4 text-right text-base font-black text-slate-800">
                                        {{ number_format($totalall, 2) }}
                                        @if($isThereAnyCurrencyDifference)
                                            <span class="block text-[10px] font-normal text-amber-600">*Mixed Currencies</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-400 ml-1">{{ $prevCurrency }}</span>
                                        @endif
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    {{-- DIGITAL SIGNATURES FOOTER --}}
                    @include('partials.pr-digital-signatures', ['purchaseRequest' => $purchaseRequest])

                </div>

            </div>

            {{-- RIGHT COLUMN: SIDER --}}
            <div class="space-y-6">
                
                {{-- APPROVAL WIDGET (If User Can Approve) --}}
                @if ($canApprove)
                    @php
                        // Recalculate item stats for the widget
                        $currentStep = $approval?->steps->firstWhere('sequence', $approval->current_step);
                        $approverType = null;
                        $itemStats = null;
                        
                        if ($currentStep) {
                            $approverType = $currentStep->item_approver_type;
                            
                            if ($approverType) {
                                $itemStats = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService::class)
                                    ->getItemStats($purchaseRequest, $approverType);
                            }
                        }
                    @endphp

                    <div class="glass-card border-l-4 {{ isset($itemStats['pending']) && $itemStats['pending'] > 0 ? 'border-l-amber-500' : 'border-l-emerald-500' }} p-6 shadow-lg">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-4">
                            Action Required
                        </h3>
                        @if($itemStats)
                             @php
                                $hasPending = $itemStats['pending'] > 0;
                                $hasApproved = $itemStats['approved'] > 0;
                                $allRejected = !$hasPending && !$hasApproved;
                                
                                $cardColor = $hasPending ? 'bg-amber-50 text-amber-800' : ($allRejected ? 'bg-rose-50 text-rose-800' : 'bg-emerald-50 text-emerald-800');
                                $icon = $hasPending ? 'bi-exclamation-triangle-fill text-amber-500' : ($allRejected ? 'bi-x-circle-fill text-rose-500' : 'bi-check-circle-fill text-emerald-500');
                             @endphp

                             <div class="mb-6 rounded-xl {{ $cardColor }} p-4">
                                <div class="flex items-start gap-3">
                                    <i class="bi {{ $icon }} text-xl"></i>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide opacity-80">Item Review Status</p>
                                        <p class="mt-1 text-sm font-bold">
                                            @if($hasPending)
                                                {{ $itemStats['pending'] }} items pending review
                                            @elseif($allRejected)
                                                All items rejected (Cannot Approve PR)
                                            @else
                                                All items reviewed ({{ $itemStats['approved'] }} approved)
                                            @endif
                                        </p>
                                        <div class="mt-2 flex h-1.5 w-full overflow-hidden rounded-full bg-black/10">
                                             @php
                                                $reviewedPercent = $itemStats['total'] > 0 
                                                    ? (($itemStats['total'] - $itemStats['pending']) / $itemStats['total']) * 100 
                                                    : 0;
                                                $barColor = $hasPending ? 'bg-amber-500' : ($allRejected ? 'bg-rose-500' : 'bg-emerald-500');
                                            @endphp
                                            <div class="h-full {{ $barColor }}" style="width: {{ $reviewedPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <button type="button" 
                                    @click="$dispatch('open-approve-modal')"
                                    {{ (($itemStats['pending'] ?? 0) > 0 || ($itemStats['approved'] ?? 0) === 0) ? 'disabled' : '' }}
                                    class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                                Approve Request
                            </button>
                            
                            <button type="button" @click="$dispatch('open-reject-modal')"
                                    class="w-full rounded-xl border border-rose-200 bg-white py-2.5 text-sm font-bold text-rose-600 transition-all hover:bg-rose-50 hover:border-rose-300">
                                Reject Request
                            </button>

                            <button type="button" @click="$dispatch('open-return-modal')"
                                    class="w-full rounded-xl border border-orange-200 bg-white py-2.5 text-sm font-bold text-orange-600 transition-all hover:bg-orange-50 hover:border-orange-300">
                                Return for Revision
                            </button>
                        </div>
                    </div>
                @endif

                {{-- APPROVAL TIMELINE --}}
                <div class="glass-card p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">
                        <i class="bi bi-clock-history text-indigo-500"></i> Workflow History
                    </h3>
                    
                    {{-- Using existing partial but wrapped in our container --}}
                    <div class="relative">
                        @include('partials.pr-approval-timeline', ['pr' => $purchaseRequest])
                    </div>
                </div>

                {{-- RELATED FILES --}}
                <div class="glass-card p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">
                        <i class="bi bi-paperclip text-indigo-500"></i> Related Documents
                    </h3>

                    @if($purchaseRequest->files->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($purchaseRequest->files as $file)
                                <div class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition-colors hover:border-indigo-100 hover:bg-slate-50">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        {{-- File Icon --}}
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-white shadow-sm ring-1 ring-slate-100">
                                            @if(Str::contains($file->mime_type, 'image'))
                                                <i class="bi bi-file-earmark-image text-lg text-purple-500"></i>
                                            @elseif(Str::contains($file->mime_type, 'pdf'))
                                                <i class="bi bi-file-earmark-pdf text-lg text-rose-500"></i>
                                            @elseif(Str::contains($file->mime_type, 'spreadsheet') || Str::contains($file->mime_type, 'excel'))
                                                <i class="bi bi-file-earmark-spreadsheet text-lg text-emerald-500"></i>
                                            @else
                                                <i class="bi bi-file-earmark-text text-lg text-slate-500"></i>
                                            @endif
                                        </div>
                                        
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-700" title="{{ $file->name }}">
                                                {{ $file->name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400">
                                                {{ number_format($file->size / 1024, 2) }} KB
                                            </p>
                                        </div>
                                    </div>

                                    <a href="{{ asset('storage/files/' . $file->name) }}" 
                                       target="_blank"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-all hover:bg-indigo-50 hover:text-indigo-600"
                                       title="Download / View">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if($canUpload) {{-- Using same permission as upload --}}
                                        <form action="{{ route('file.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Delete this file?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-400 transition-all hover:bg-rose-50 hover:text-rose-600" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <i class="bi bi-folder2-open text-3xl text-slate-200 mb-2"></i>
                            <p class="text-xs font-medium text-slate-400">No documents attached.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>

    {{-- AUDIT LOG DRAWER --}}
    @if(auth()->user()->hasRole('super-admin'))
        @push('modals')
            <div x-data="{ open: false }" 
                 @open-audit-drawer.window="open = true"
                  x-init="$watch('open', value => {
                    if (value) {
                        document.body.classList.add('overflow-hidden');
                    } else {
                        document.body.classList.remove('overflow-hidden');
                    }
                })"
                 x-show="open" 
                 style="display: none;"
                 class="relative z-[100]" 
                 aria-labelledby="slide-over-title" 
                 role="dialog" 
                 aria-modal="true">
                
                <div x-show="open" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                            <div x-show="open" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl" @click.outside="open = false">
                                    <div class="bg-indigo-700 px-4 py-6 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">Audit Log History</h2>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button type="button" @click="open = false" class="relative rounded-md bg-indigo-700 text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                                    <span class="absolute -inset-2.5"></span>
                                                    <span class="sr-only">Close panel</span>
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm text-indigo-300">Detailed record of all system events.</p>
                                        </div>
                                    </div>
                                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                        <ul role="list" class="-mb-8">
                                            @forelse($purchaseRequest->combined_activities as $activity)
                                                <li>
                                                    <div class="relative pb-8">
                                                        @if(!$loop->last)
                                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                                        @endif
                                                        <div class="relative flex space-x-3">
                                                            <div>
                                                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 ring-8 ring-white">
                                                                    <i class="bi bi-activity text-slate-500"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex flex-col min-w-0 flex-1 justify-between gap-2">
                                                                <div>
                                                                    <p class="text-xs text-slate-500">
                                                                        <span class="font-medium text-slate-900">{{ $activity->causer->name ?? 'System' }}</span>
                                                                        {{ $activity->description }}
                                                                    </p>
                                                                    @if($activity->properties->has('attributes'))
                                                                        <div class="mt-2 rounded-lg bg-slate-50 p-2 text-[10px] text-slate-600 font-mono overflow-auto border border-slate-100">
                                                                            @foreach($activity->properties['attributes'] as $key => $val)
                                                                                @if($key !== 'updated_at')
                                                                                    <div class="flex gap-2">
                                                                                        <span class="font-bold text-slate-700">{{ $key }}:</span>
                                                                                        <span class="truncate">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="whitespace-nowrap text-right text-[10px] text-slate-400">
                                                                    <time datetime="{{ $activity->created_at }}">{{ $activity->created_at->format('M d, H:i') }}</time>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="py-4 text-center text-sm text-slate-500">No activity recorded.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endpush
    @endif

    {{-- MODALS (Moved to root to fix z-index/overlay issues) --}}
    @if ($canApprove)
        @push('modals')
            @include('partials.approval-modal', [
                'id' => $purchaseRequest->id,
                'route' => 'purchase-requests.approve',
                'title' => 'Approve Purchase Request',
                'entityName' => 'Purchase Request',
                'buttonLabel' => 'Confirm Approval'
            ])
            @include('partials.rejection-modal', [
                'id' => $purchaseRequest->id,
                'route' => 'purchase-requests.reject',
                'title' => 'Reject Purchase Request',
                'entityName' => 'Purchase Request',
                'buttonLabel' => 'Confirm Rejection'
            ])
            @include('partials.return-modal', [
                'id' => $purchaseRequest->id,
                'route' => 'purchase-requests.return',
                'title' => 'Return for Revision',
                'entityName' => 'Purchase Request',
                'buttonLabel' => 'Confirm Return'
            ])
        @endpush
    @endif

    @if ($canSignAndSubmit)
        @push('modals')
            @include('partials.pr-sign-submit-modal', [
                'hasDefaultSignature' => $hasDefaultSig,
                'signaturePreviewUrl' => $signaturePreviewUrl ?? null,
                'submitUrl'           => route('purchase-requests.sign-and-submit', $purchaseRequest->id),
                'formId'              => null,
            ])
        @endpush
    @endif

    @if($canUpload)
        @push('modals')
            @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
        @endpush
    @endif
@endsection