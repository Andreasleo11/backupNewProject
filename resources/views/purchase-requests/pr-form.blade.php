@extends('new.layouts.app')

@php
    $authUser = auth()->user();
    $isEdit = isset($purchaseRequest);
    $title = $isEdit ? 'Edit Requisition' : 'New Requisition';
    $action = $isEdit ? route('purchase-requests.update', $purchaseRequest->id) : route('purchase-requests.store');
@endphp

@section('title', $title)

@section('content')

    <div x-data="purchaseRequestForm(
        @js(old('items', $isEdit ? $purchaseRequest->itemDetail : [])),
        '{{ old('from_department', $isEdit ? $purchaseRequest->from_department : $authUser->department?->name) }}',
        '{{ old('to_department', $isEdit ? $purchaseRequest->to_department : '') }}'
    )" x-init="init()">
        
        {{-- TOP BAR --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <a href="{{ $isEdit ? route('purchase-requests.show', $purchaseRequest->id) : route('purchase-requests.index') }}"
                    class="group inline-flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                    <i class="bi bi-arrow-left text-lg transition-transform group-hover:-translate-x-1"></i>
                    {{ $isEdit ? 'Back to Detail' : 'Back to List' }}
                </a>
                <h1 class="text-3xl font-black tracking-tight text-slate-800">
                    {{ $isEdit ? 'Edit' : 'New' }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Requisition</span>
                </h1>
                <p class="text-sm font-medium text-slate-500">
                    {{ $isEdit ? 'Update the details below to modify your request.' : 'Fill in the details below to initiate a new procurement workflow.' }}
                </p>
            </div>

            <div class="text-right">
                <div class="inline-flex items-center gap-3 rounded-2xl bg-white p-2 pr-4 shadow-sm border border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-white font-bold shadow-md">
                        {{ strtoupper(substr($authUser->name, 0, 1)) }}
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-slate-900">{{ $authUser->name }}</p>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $authUser->department->name ?? 'NO DEPT' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($isEdit && $purchaseRequest->workflow_status === 'RETURNED')
            <div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-xl text-orange-500"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-orange-800">Returned for Revision</h3>
                        <p class="mt-1 text-sm text-orange-700">
                            This request was returned by <strong>{{ $purchaseRequest->approvalRequest->steps->where('status', 'RETURNED')->last()?->actedUser?->name ?? 'Approver' }}</strong>.
                        </p>
                        <div class="mt-2 rounded-lg bg-white/60 p-3 text-sm font-medium text-orange-800 border border-orange-100">
                            "{{ $purchaseRequest->approvalRequest->steps->where('status', 'RETURNED')->last()?->return_reason ?? 'No reason provided.' }}"
                        </div>
                        <p class="mt-2 text-xs text-orange-600">
                            Please update the details below and resubmit. The approval process will restart from the beginning.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($isEdit && $purchaseRequest->workflow_status === 'REJECTED')
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <i class="bi bi-x-circle-fill text-xl text-rose-500"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-rose-800">Previously Rejected</h3>
                        <p class="mt-1 text-sm text-rose-700">
                            This request was rejected by <strong>{{ $purchaseRequest->approvalRequest->steps->where('status', 'REJECTED')->last()?->actedUser?->name ?? 'Approver' }}</strong>.
                        </p>
                        @php
                            $rejectionRemarks = $purchaseRequest->approvalRequest->steps->where('status', 'REJECTED')->last()?->remarks;
                        @endphp
                        @if($rejectionRemarks)
                            <div class="mt-2 rounded-lg bg-white/60 p-3 text-sm font-medium text-rose-800 border border-rose-100">
                                "{{ $rejectionRemarks }}"
                            </div>
                        @endif
                        <p class="mt-2 text-xs text-rose-600">
                            You may revise the details below and resubmit. The approval process will restart from Step 1.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ $action }}" method="POST" id="pr-form" class="space-y-6"
              @submit="if (!validateBeforeSubmit()) $event.preventDefault()">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- VALIDATION ERROR SUMMARY --}}
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-exclamation-circle-fill text-rose-500 text-lg mt-0.5 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-bold text-rose-800">Please fix the following errors:</p>
                            <ul class="mt-2 space-y-1 text-sm text-rose-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MAIN FORM CONTAINER --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                
                {{-- LEFT COLUMN: GENERAL INFO --}}
                <div class="space-y-6 xl:col-span-1">
                    {{-- General Card --}}
                    <div class="glass-card p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase tracking-widest mb-6">
                            <i class="bi bi-sliders text-indigo-500"></i> Settings
                        </h3>

                        <div class="space-y-5">

                            {{-- Branch --}}
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Branch</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="branch" value="JAKARTA" class="peer sr-only" {{ old('branch', $isEdit ? $purchaseRequest->branch?->value : 'JAKARTA') === 'JAKARTA' ? 'checked' : '' }}>
                                        <div class="h-full rounded-xl border-2 border-slate-100 bg-white p-3 text-center transition-all hover:border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/30 @error('branch') border-rose-300 @enderror">
                                            <div class="text-2xl mb-1">🏢</div>
                                            <span class="block text-xs font-bold text-slate-700 peer-checked:text-blue-700">Jakarta</span>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity text-blue-600">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="branch" value="KARAWANG" class="peer sr-only" {{ old('branch', $isEdit ? $purchaseRequest->branch?->value : '') === 'KARAWANG' ? 'checked' : '' }}>
                                        <div class="h-full rounded-xl border-2 border-slate-100 bg-white p-3 text-center transition-all hover:border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/30 @error('branch') border-rose-300 @enderror">
                                            <div class="text-2xl mb-1">🏭</div>
                                            <span class="block text-xs font-bold text-slate-700 peer-checked:text-blue-700">Karawang</span>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity text-blue-600">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    </label>
                                </div>
                                @error('branch')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Routing Card --}}
                    <div class="glass-card p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase tracking-widest mb-6">
                            <i class="bi bi-sign-turn-right text-indigo-500"></i> Routing
                        </h3>

                        <div class="space-y-5">
                            {{-- From Dept --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 ml-1">From Department</label>
                                <div class="relative">
                                    <select name="from_department" x-model="from_department" x-init="initSimpleTomSelect($el, 'from')" class="w-full @error('from_department') border-rose-400 @enderror" placeholder="Select Origin" required>
                                        <option value="">Select Department...</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->name }}" {{ old('from_department', $isEdit ? $purchaseRequest->from_department : $authUser->department?->name) === $department->name ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('from_department')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- To Dept --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 ml-1">To Department</label>
                                <div class="relative">
                                    <select name="to_department" x-model="to_department" x-init="initSimpleTomSelect($el, 'to')" class="w-full @error('to_department') border-rose-400 @enderror" placeholder="Select Target" required>
                                        <option value="">Select Target...</option>
                                        @foreach (\App\Enums\ToDepartment::cases() as $dept)
                                            <option value="{{ $dept->value }}" {{ old('to_department', $isEdit ? $purchaseRequest->to_department : '') === $dept->value ? 'selected' : '' }}>{{ $dept->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('to_department')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Import Toggle (Conditional) --}}
                            <div x-show="showLocalImport" x-transition.opacity class="pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-bold text-slate-700">Import Purchase?</label>
                                    <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold">Moulding Only</span>
                                </div>
                                <div class="flex gap-2 p-1 bg-slate-100/50 rounded-lg">
                                    <label class="flex-1 cursor-pointer text-center">
                                        <input type="radio" name="is_import" value="false" class="peer sr-only" :disabled="!showLocalImport" @checked(old('is_import', $isEdit ? ($purchaseRequest->is_import === 0 ? 'false' : 'true') : 'false') !== 'true')>
                                        <span class="block rounded-md py-1.5 text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all">Local</span>
                                    </label>
                                    <label class="flex-1 cursor-pointer text-center">
                                        <input type="radio" name="is_import" value="true" class="peer sr-only" :disabled="!showLocalImport" @checked(old('is_import', $isEdit ? ($purchaseRequest->is_import === 1 ? 'true' : 'false') : 'false') === 'true')>
                                        <span class="block rounded-md py-1.5 text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all">Import</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: DATES, SUPPLIER, ITEMS --}}
                <div class="space-y-6 xl:col-span-2">
                    
                    {{-- Logistics Card --}}
                    <div class="glass-card p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Date PR --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Date of PR</label>
                                <input type="date" name="date_of_pr" value="{{ old('date_of_pr', $isEdit ? $purchaseRequest->date_pr : '') }}" required
                                       class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all @error('date_of_pr') border-rose-400 bg-rose-50/30 @enderror">
                                @error('date_of_pr')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date Required --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Date Required</label>
                                <input type="date" name="date_of_required" value="{{ old('date_of_required', $isEdit ? $purchaseRequest->date_required : '') }}" required
                                       class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all @error('date_of_required') border-rose-400 bg-rose-50/30 @enderror">
                                @error('date_of_required')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Supplier --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Supplier</label>
                                <input type="text" name="supplier" value="{{ old('supplier', $isEdit ? $purchaseRequest->supplier : '') }}" placeholder="Vendor Name" required
                                       class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all @error('supplier') border-rose-400 bg-rose-50/30 @enderror">
                                @error('supplier')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- PIC --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Person In Charge</label>
                                <input type="text" name="pic" value="{{ old('pic', $isEdit ? $purchaseRequest->pic : '') }}" placeholder="Contact Person" required
                                       class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all @error('pic') border-rose-400 bg-rose-50/30 @enderror">
                                @error('pic')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Remark --}}
                        <div class="mt-6 space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Remarks / Notes</label>
                            <textarea name="remark" rows="2" placeholder="Any additional details..."
                                      class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all @error('remark') border-rose-400 bg-rose-50/30 @enderror">{{ old('remark', $isEdit ? $purchaseRequest->remark : '') }}</textarea>
                            @error('remark')
                                <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ITEMS REPEATER --}}
                    <div class="glass-card overflow-hidden">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                            <div>
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase tracking-widest">
                                    <i class="bi bi-box-seam text-indigo-500"></i> Request Items
                                </h3>
                                @error('items')
                                    <p class="text-xs text-rose-600 mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <button type="button" @click="addItem()"
                                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white shadow-lg shadow-slate-200 hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                                <i class="bi bi-plus-lg"></i> Add Item
                            </button>
                        </div>

                        <div class="p-6 bg-white/40">
                            {{-- Items Grid --}}
                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-indigo-200">
                                        <div class="absolute -left-[1px] top-4 bottom-4 w-1 rounded-r-lg bg-slate-200 group-hover:bg-indigo-500 transition-colors"></div>
                                        
                                        {{-- Remove Button --}}
                                        <button type="button" @click="removeItem(index)" :disabled="items.length === 1"
                                                class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full text-slate-300 hover:bg-rose-50 hover:text-rose-500 disabled:opacity-0 transition-all">
                                            <i class="bi bi-x-lg text-xs"></i>
                                        </button>

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            {{-- Item Name --}}
                                            <div class="md:col-span-4 space-y-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Item Name</label>
                                                <input type="text" x-model="item.item_name" :name="'items[' + index + '][item_name]'" x-init="initItemTomSelect($el, index)"
                                                       class="w-full" placeholder="Search Item..." required>
                                            </div>

                                            {{-- Qty --}}
                                            <div class="md:col-span-2 space-y-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Qty</label>
                                                <input type="number" x-model="item.quantity" :name="'items[' + index + '][quantity]'" @input="sanitizeNumber(index, 'quantity')" step="any"
                                                       class="w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-center focus:border-indigo-500 focus:bg-white transition-all" placeholder="0">
                                            </div>

                                            {{-- UoM --}}
                                            <div class="md:col-span-2 space-y-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Unit</label>
                                                <div class="relative">
                                                    <input type="text" x-model="item.uom" :name="'items[' + index + '][uom]'" required
                                                           class="w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-center focus:border-indigo-500 focus:bg-white transition-all" placeholder="Unit">
                                                </div>
                                            </div>

                                            {{-- Price --}}
                                            <div class="md:col-span-4 space-y-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Est. Price</label>
                                                <div class="flex rounded-lg shadow-sm">
                                                    <select x-model="item.currency" :name="'items[' + index + '][currency]'" 
                                                            class="rounded-l-lg border-slate-200 bg-slate-100 px-2 py-2 text-xs font-bold text-slate-600 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <template x-for="cur in currencies" :key="cur">
                                                            <option :value="cur" x-text="cur"></option>
                                                        </template>
                                                    </select>
                                                    <input type="text" x-model="item.price" :name="'items[' + index + '][price]'" @input="sanitizeNumber(index, 'price')"
                                                           class="block w-full rounded-r-lg border-l-0 border-slate-200 px-3 py-2 text-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500" placeholder="0">
                                                </div>
                                            </div>

                                            {{-- Purpose (Full Width) --}}
                                            <div class="md:col-span-12 space-y-1">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Usage / Purpose</label>
                                                <input type="text" x-model="item.purpose" :name="'items[' + index + '][purpose]'"
                                                       class="w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 focus:border-indigo-500 focus:bg-white transition-all" placeholder="Explain why this item is needed...">
                                            </div>
                                            
                                            {{-- Subtotal Display --}}
                                            <div class="md:col-span-12 text-right">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase mr-2">Subtotal:</span>
                                                <span class="text-sm font-black text-slate-800" x-text="formatMoney(itemSubtotal(item), item.currency)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            {{-- Empty State --}}
                            <div x-show="items.length === 0" class="flex flex-col items-center justify-center py-10 text-slate-400">
                                <i class="bi bi-basket text-4xl mb-2"></i>
                                <p class="text-sm">No items added yet.</p>
                            </div>
                        </div>

                        {{-- Footer Totals --}}
                        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                            <div class="flex flex-wrap items-center justify-end gap-4">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Estimated Total:</span>
                                <div class="flex flex-wrap gap-3">
                                    <template x-for="([cur, total], i) in Object.entries(totalsByCurrency())" :key="cur">
                                        <div class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 shadow-sm">
                                            <span class="font-bold text-slate-400 text-xs" x-text="cur"></span>
                                            <span class="font-black text-slate-800 text-lg" x-text="formatMoney(total, cur)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTION BAR --}}
            <div class="sticky bottom-4 z-30 w-full mt-6">
                <div class="glass-card flex flex-col-reverse sm:flex-row sm:items-center justify-between p-4 gap-4 shadow-2xl shadow-indigo-900/10 border-indigo-100">
                    <button type="button" @click="window.history.back()"
                            class="w-full sm:w-auto rounded-xl px-4 py-2.5 sm:py-2 text-sm font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        Cancel
                    </button>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                        {{-- Partial Save: just submit the form normally (submit_action stays empty) --}}
                        <button type="submit"
                                class="inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 w-full sm:w-auto">
                            <i class="bi bi-floppy"></i>
                            {{ $isEdit ? 'Save Changes' : 'Save as Draft' }}
                        </button>

                        @if(!$isEdit || in_array($purchaseRequest->workflow_status ?? 'DRAFT', ['DRAFT', 'RETURNED', 'REJECTED']))
                            {{-- Sign & Submit: opens confirmation modal --}}
                            <button type="button"
                                    @click="$dispatch('open-sign-submit-modal')"
                                    class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:-translate-y-0.5 w-full sm:w-auto">
                                <i class="bi bi-pen"></i>
                                {{ ($isEdit && in_array($purchaseRequest->workflow_status ?? '', ['RETURNED', 'REJECTED'])) ? 'Sign & Resubmit' : 'Sign & Submit' }}
                            </button>
                        @endif
                    </div>

                    {{-- Hidden: set to 'sign_and_submit' by the modal before form submit --}}
                    <input type="hidden" name="submit_action" id="submit_action_input" value="">
                </div>
            </div>
        </form>
    </div>

    {{-- Sign & Submit Modal --}}
    @include('partials.pr-sign-submit-modal', [
        'hasDefaultSignature' => $hasDefaultSignature ?? false,
        'signaturePreviewUrl' => $signaturePreviewUrl ?? null,
        'formId'              => 'pr-form',
        'submitUrl'           => null,
    ])
@endsection

@push('scripts')
    {{-- TomSelect CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    {{-- TomSelect JS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseRequestForm', (oldItems = [], initialFromDept = '', initialToDept = '') => ({
                from_department: initialFromDept || '',
                to_department: initialToDept || '',
                items: [],
                currencies: ['IDR', 'CNY', 'USD'],
                is_draft: '0', // Default

                init() {
                    // Sync Alpine checks with server old input
                    // Logic handled by blade checking radio, but nice to have state here if we want dynamic buttons
                    
                    if (Array.isArray(oldItems) && oldItems.length) {
                        this.items = oldItems.map(i => ({
                            item_name: i.item_name || '',
                            quantity: i.quantity || '',
                            uom: i.uom || '',
                            currency: i.currency || 'IDR',
                            price: i.price || '',
                            purpose: i.purpose || '',
                        }));
                    }

                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },

                initSimpleTomSelect(el, type) {
                    if (!el) return;
                    if (el._ts) return; // already init

                    // Determine the initial value from the Alpine data
                    const initialValue = type === 'from' ? this.from_department : this.to_department;

                    const ts = new TomSelect(el, {
                        plugins: ['dropdown_input'],
                        sortField: { field: 'text', direction: 'asc' },
                        dropdownParent: 'body',
                        // Pre-select the value that was set server-side
                        items: initialValue ? [initialValue] : [],
                    });

                    el._ts = ts;

                    ts.on('change', (value) => {
                        if (type === 'from') this.from_department = value;
                        if (type === 'to') this.to_department = value;
                    });
                },

                initItemTomSelect(el, index) {
                    if (!el) return;
                    if (el._ts) return;

                    const existingValue = this.items[index]?.item_name || '';

                    const tsOptions = {
                        valueField: 'name',
                        labelField: 'name',
                        searchField: 'name',
                        maxItems: 1,
                        create: true,
                        dropdownParent: 'body',
                        placeholder: 'Select or type item...',
                        // Pre-populate with existing value so it shows on load
                        options: existingValue ? [{ name: existingValue }] : [],
                        items: existingValue ? [existingValue] : [],
                        load: (query, callback) => {
                            if (!query.length) return callback();
                            fetch(`/purchase-requests/get-item-names?itemName=${encodeURIComponent(query)}`)
                                .then(res => res.json())
                                .then(data => callback(data))
                                .catch(() => callback());
                        },
                    };

                    const ts = new TomSelect(el, tsOptions);
                    el._ts = ts;

                    ts.on('change', (value) => {
                        if (!this.items[index]) return;
                        this.items[index].item_name = value || '';

                        const opt = ts.options[value];
                        if (opt) {
                            // Update currency if it's still default IDR
                            if (opt.currency && (this.items[index].currency === 'IDR' || !this.items[index].currency)) {
                                this.items[index].currency = opt.currency;
                            }
                            
                            // Update price with latest available
                            const price = opt.latest_price || opt.price;
                            if (price) {
                                this.items[index].price = price.toString();
                            }
                        }
                    });
                },

                get showLocalImport() {
                    return this.from_department === 'MOULDING' && this.to_department === 'Purchasing';
                },

                validateBeforeSubmit() {
                    const errors = [];
                    // Simple validation checks
                    if (this.items.length === 0) errors.push('At least one item is required');
                    
                    this.items.forEach((item, i) => {
                        const num = i + 1;
                        if (!item.item_name) errors.push(`Item #${num} name is missing`);
                        if (!item.quantity || item.quantity <= 0) errors.push(`Item #${num} quantity invalid`);
                        if (!item.uom) errors.push(`Item #${num} UOM is missing`);
                        if (!item.price || item.price < 0) errors.push(`Item #${num} price invalid`);
                        if (!item.purpose) errors.push(`Item #${num} purpose is missing`);
                    });

                    if (errors.length > 0) {
                        alert(errors.join('\n'));
                        return false;
                    }
                    return true;
                },

                addItem() {
                    this.items.push({
                        item_name: '',
                        quantity: '',
                        uom: '',
                        currency: 'IDR',
                        price: '',
                        purpose: '',
                    });
                },

                removeItem(index) {
                    if (this.items.length === 1) return;
                    this.items.splice(index, 1);
                },

                sanitizeNumber(index, field) {
                    if (!this.items[index]) return;
                    // Allow simple number sanitization
                    // this.items[index][field] = String(this.items[index][field]).replace(/[^0-9.]/g, '');
                },

                itemSubtotal(item) {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.price) || 0;
                    return qty * price;
                },

                totalsByCurrency() {
                    const totals = {};
                    this.items.forEach(i => {
                        const cur = i.currency || 'IDR';
                        const sub = this.itemSubtotal(i);
                        if (!totals[cur]) totals[cur] = 0;
                        totals[cur] += sub;
                    });
                    return totals;
                },

                formatMoney(amount, currency) {
                    amount = Number(amount || 0);
                    const formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: currency,
                        minimumFractionDigits: 2
                    });
                    return formatter.format(amount);
                },
            }));
        });
    </script>
@endpush
