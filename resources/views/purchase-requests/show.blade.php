@extends('new.layouts.app')

@section('title', 'Purchase Request Details - ' . $purchaseRequest->doc_num)

@section('content')
    @php
        $user = auth()->user();

        // Flags from controller
        $canAutoApprove = $flags['canAutoApprove'] ?? false;
        $canApprove = $flags['canApprove'] ?? false;
        $canUpload = $flags['canUpload'] ?? false;
        $canEditPr = $flags['canEdit'] ?? false;

        $totalall = (float) ($totals['total'] ?? 0);
        $isThereAnyCurrencyDifference = (bool) ($totals['hasCurrencyDiff'] ?? false);
        $prevCurrency = $totals['currency'] ?? null;

        // Alpine data slots
        $slots = $autographSlots ?? [];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8"
         x-data="prDetailPage(@js($slots), @js($canAutoApprove), @js($purchaseRequest->id), @js(csrf_token()))">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800 backdrop-blur-sm relative z-20">
                <div class="flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-lg"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="$el.parentElement.remove()">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/80 px-4 py-3 text-sm text-rose-800 backdrop-blur-sm relative z-20">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-lg"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" class="text-rose-600 hover:text-rose-800" @click="$el.parentElement.remove()">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>
        @endif

        {{-- HEADER SECTION --}}
        <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                {{-- Back Link --}}
                <a href="{{ (auth()->user()->specification?->name === 'DIRECTOR' && auth()->user()->hasRole('director')) ? route('director.pr.index') : route('purchase-requests.index') }}"
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
                    @include('partials.pr-status-badge', ['pr' => $purchaseRequest])

                    @if($purchaseRequest->status === 'draft' || $purchaseRequest->status === 0)
                         <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                            <i class="bi bi-pencil-square"></i> Draft
                        </span>
                    @endif
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
                @if ($canUpload)
                    <button type="button" @click="openUploadFiles = true"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-indigo-200 hover:text-indigo-600 hover:shadow-md">
                        <i class="bi bi-cloud-upload text-lg text-slate-400 group-hover:text-indigo-500"></i>
                        Upload Files
                    </button>
                    @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
                @endif

                @if ($canEditPr)
                    <button type="button" data-bs-target="#edit-purchase-request-modal-{{ $purchaseRequest->id }}" data-bs-toggle="modal"
                            class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:text-indigo-600">
                        <i class="bi bi-pencil-square text-lg text-slate-400 group-hover:text-indigo-500"></i>
                        Edit Details
                    </button>
                    @include('partials.edit-purchase-request-modal', [
                        'pr' => $purchaseRequest,
                        'details' => $filteredItemDetail,
                    ])
                @endif
                
                {{-- If it's pure draft, show delete --}}
                @if(($purchaseRequest->status === 'draft' || $purchaseRequest->status === 0) && auth()->id() === $purchaseRequest->created_by)
                    <form action="{{ route('purchase-requests.destroy', $purchaseRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="group inline-flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-600 shadow-sm transition-all hover:bg-rose-100 hover:border-rose-200">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                @endif
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
                                    
                                    {{-- DYNAMIC APPROVAL COLUMN LOGIC --}}
                                    @php
                                        $showIsApproveColumn =
                                            ($user->hasRole('DIRECTOR')) ||
                                            ($user->hasRole('VERIFICATOR')) ||
                                            ((($user->department?->name === $purchaseRequest->from_department && $user->hasRole('HEAD')) ||
                                                ($user->hasRole('HEAD') && $purchaseRequest->from_department == 'STORE')) &&
                                                !$purchaseRequest->is_cancel);
                                        
                                        // Moulding logic
                                         if ($purchaseRequest->from_department === 'MOULDING') {
                                                $mouldingApprovalCase =
                                                    ($purchaseRequest->is_import === 1 && ( !auth()->user()->hasRole('DESIGN'))) ||
                                                    (!$purchaseRequest->is_import && ( !auth()->user()->hasRole('DESIGN'))) ||
                                                    ($purchaseRequest->is_import === 0 && (auth()->user()->hasRole('DESIGN'))) ||
                                                    (!$purchaseRequest->is_import && (auth()->user()->hasRole('DESIGN')));

                                                if ($purchaseRequest->to_department === \App\Enums\ToDepartment::MAINTENANCE->value) {
                                                    $mouldingApprovalCase = $mouldingApprovalCase && $purchaseRequest->to_department === \App\Enums\ToDepartment::MAINTENANCE->value;
                                                }
                                                // Only show if case is true
                                                if(!$mouldingApprovalCase) $showIsApproveColumn = false; 
                                         }
                                    @endphp

                                    @if($showIsApproveColumn)
                                        <th class="px-4 py-3 text-center w-32">Approval</th>
                                    @endif

                                    @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
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
                                         $showDeptHeadItemApprove = $user->department?->name === $purchaseRequest->from_department && $user->hasRole('HEAD');
                                        if ($user->hasRole('HEAD') && $purchaseRequest->from_department === 'STORE') {
                                            $showDeptHeadItemApprove = true;
                                        } elseif (
                                            $purchaseRequest->from_department === 'PERSONALIA' &&
                                            ($user->department?->name === 'PERSONALIA' && $user->hasRole('HEAD'))
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
                                        @if($showIsApproveColumn)
                                            <td class="px-4 py-4 text-center align-middle">
                                                 {{-- Logic Reuse: Check user role and item status --}}
                                                 @php
                                                     $canApproveThisItem = false;
                                                     $status = null; // null=pending, 1=approved, 0=rejected
                                                     
                                                     // Determine context
                                                     if ($user->hasRole('DIRECTOR')) {
                                                         $status = $detail->is_approve; // Director sees final status usually? Or specific field? Assuming is_approve based on original
                                                         // Wait, original code: if director, "no extra color". 
                                                         // We'll mimic the logic blocks from original for Buttons vs Badges
                                                     } elseif ($user->hasRole('VERIFICATOR')) {
                                                         $status = $detail->is_approve_by_verificator;
                                                         $canApproveThisItem = auth()->user()->can('approve', $detail); // Standard policy check
                                                     } elseif ($showDeptHeadItemApprove) {
                                                         $status = $detail->is_approve_by_head;
                                                         $canApproveThisItem = auth()->user()->can('approve', $detail);
                                                     } else {
                                                         // Fallback/General
                                                         $status = $detail->is_approve;
                                                         $canApproveThisItem = auth()->user()->can('approve', $detail);
                                                     }
                                                 @endphp

                                                 @if(is_null($status) && $canApproveThisItem)
                                                    {{-- ACTIONS --}}
                                                    <div class="flex items-center justify-center gap-1">
                                                        <form method="POST" action="{{ route('purchase-requests.items.approve', $detail) }}">
                                                            @csrf
                                                            <button type="submit" onclick="return confirm('Approve {{ $detail->item_name }}?')" 
                                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition-all hover:bg-emerald-600 hover:text-white hover:shadow-lg hover:shadow-emerald-200" title="Approve">
                                                                <i class="bi bi-check-lg text-lg"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('purchase-requests.items.reject', $detail) }}">
                                                            @csrf
                                                            <button type="submit" onclick="return confirm('Reject {{ $detail->item_name }}?')"
                                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 transition-all hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-200" title="Reject">
                                                                <i class="bi bi-x-lg text-lg"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                 @else
                                                    {{-- STATUS BADGES --}}
                                                    @if($status === 1)
                                                        <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 p-1.5 text-emerald-600">
                                                            <i class="bi bi-check-lg text-sm"></i>
                                                        </span>
                                                    @elseif($status === 0)
                                                        <span class="inline-flex items-center justify-center rounded-full bg-rose-100 p-1.5 text-rose-600">
                                                            <i class="bi bi-x-lg text-sm"></i>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center justify-center rounded-full bg-amber-100 p-1.5 text-amber-600" title="Waiting for review">
                                                            <i class="bi bi-hourglass-split text-sm"></i>
                                                        </span>
                                                    @endif
                                                 @endif
                                            </td>
                                        @endif

                                        {{-- Received Column --}}
                                        @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
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
                                    <td colspan="{{ $showIsApproveColumn ? 4 : 3 }}" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Total Amount</td>
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
                             <div class="mb-6 rounded-xl {{ isset($itemStats['pending']) && $itemStats['pending'] > 0 ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }} p-4">
                                <div class="flex items-start gap-3">
                                    <i class="bi {{ isset($itemStats['pending']) && $itemStats['pending'] > 0 ? 'bi-exclamation-triangle-fill text-amber-500' : 'bi-check-circle-fill text-emerald-500' }} text-xl"></i>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide opacity-80">Item Review Status</p>
                                        <p class="mt-1 text-sm font-bold">
                                            @if($itemStats['pending'] > 0)
                                                {{ $itemStats['pending'] }} items pending review
                                            @else
                                                All items reviewed
                                            @endif
                                        </p>
                                        <div class="mt-2 flex h-1.5 w-full overflow-hidden rounded-full bg-black/10">
                                             @php
                                                $reviewedPercent = $itemStats['total'] > 0 
                                                    ? (($itemStats['total'] - $itemStats['pending']) / $itemStats['total']) * 100 
                                                    : 0;
                                            @endphp
                                            <div class="h-full {{ isset($itemStats['pending']) && $itemStats['pending'] > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $reviewedPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <form method="POST" action="{{ route('purchase-requests.approve', $purchaseRequest->id) }}"
                                  onsubmit="return confirm('Approve this entire Purchase Request?')">
                                @csrf
                                <button type="submit" 
                                        {{ isset($itemStats['pending']) && $itemStats['pending'] > 0 ? 'disabled' : '' }}
                                        class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                                    Approve Request
                                </button>
                            </form>
                            
                            <button type="button" @click="$dispatch('open-reject-modal')"
                                    class="w-full rounded-xl border border-rose-200 bg-white py-2.5 text-sm font-bold text-rose-600 transition-all hover:bg-rose-50 hover:border-rose-300">
                                Reject Request
                            </button>
                        </div>
                    </div>
                     {{-- Include Reject Modal --}}
                    @include('partials.pr-reject-modal', ['pr' => $purchaseRequest])
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

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Assuming app.js / alpine loaded in layout --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('prDetailPage', (slots, canAutoApprove, prId, csrfToken) => ({
                openUploadFiles: false,
                canAutoApprove: canAutoApprove,
                
                init() {
                    // console.log('PR Detail Page Initialized');
                }
            }));
        });
    </script>
@endpush
