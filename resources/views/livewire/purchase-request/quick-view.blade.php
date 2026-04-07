<div class="p-5">
    {{-- Status Banner --}}
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200">
        <div>
            <h6 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Doc Num</h6>
            <div class="font-mono text-lg font-medium text-slate-800">{{ $pr->doc_num }}</div>
        </div>
        <div class="text-right">
            <h6 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status</h6>
            <div>
                @include('partials.workflow-status-badge', ['record' => $pr])
            </div>
            @if($pr->workflow_status === 'IN_REVIEW')
                <div class="text-[10px] text-slate-500 mt-1 uppercase tracking-wider">
                    Waiting at: <span class="font-semibold text-slate-700">{{ $pr->workflow_step }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-3 rounded-lg border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Submitting Details</p>
            <p class="text-sm text-slate-700"><i class="bi bi-person text-slate-400 mr-2"></i>{{ $pr->createdBy->name ?? 'Unknown' }}</p>
            <p class="text-sm text-slate-700"><i class="bi bi-building text-slate-400 mr-2"></i>{{ $pr->from_department }}</p>
            <p class="text-sm text-slate-700"><i class="bi bi-geo-alt text-slate-400 mr-2"></i>{{ $pr->branch->value ?? $pr->branch }}</p>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Target Details</p>
            <p class="text-sm text-slate-700"><i class="bi bi-calendar-event text-slate-400 mr-2"></i><strong>Req:</strong> {{ \Carbon\Carbon::parse($pr->date_pr)->format('d M Y') }}</p>
            <p class="text-sm text-slate-700"><i class="bi bi-arrow-right-circle text-slate-400 mr-2"></i><strong>To:</strong> {{ $pr->to_department->value ?? $pr->to_department }}</p>
            @if($pr->supplier)
                <p class="text-sm text-slate-700"><i class="bi bi-shop text-slate-400 mr-2"></i>{{ $pr->supplier }}</p>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div>
        <h6 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Requested Items ({{ $filteredItemDetail->count() }})</h6>
        <div class="bg-white border text-left border-slate-200 rounded-lg overflow-hidden shrink-0">
            <table class="min-w-full divide-y divide-slate-200 w-full mb-0 text-left">
                <thead class="bg-slate-50 text-left">
                    <tr class="text-left">
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item Name</th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Qty/UOM</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Price/Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($filteredItemDetail as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2">
                                <span class="text-sm font-medium text-slate-900 line-clamp-2 w-full max-w-[200px]" title="{{ $item->item_name }}">{{ $item->item_name }}</span>
                                @if($item->purpose)
                                    <span class="text-xs text-slate-500 block mt-0.5 line-clamp-1" title="{{ $item->purpose }}">Use: {{ $item->purpose }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span class="text-sm font-semibold text-slate-700">{{ (float) $item->quantity }}</span>
                                <span class="text-xs text-slate-500">{{ $item->uom }}</span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="text-xs text-slate-500">{{ $item->currency }} {{ number_format((float) $item->price, 2) }}</div>
                                <div class="text-sm font-medium text-slate-900">{{ number_format((float) $item->price * (float) $item->quantity, 2) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-sm text-slate-500 italic">No items found or permitted.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        @if(isset($totals['total']) && $totals['total'] > 0)
            <div class="mt-3 flex flex-wrap gap-2 justify-end">
                <div class="bg-indigo-50 border border-indigo-100 rounded px-3 py-1.5 text-right">
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block leading-none mb-0.5">Approved Total {{ $totals['currency'] ?? '' }}</span>
                    <span class="text-sm font-bold text-indigo-700 leading-none">{{ number_format((float) $totals['total'], 2) }}</span>
                    @if(isset($totals['hasCurrencyDiff']) && $totals['hasCurrencyDiff'])
                        <span class="mt-1 block text-[9px] font-bold text-amber-600 uppercase tracking-widest">*Mixed Currencies</span>
                    @endif
                </div>
            </div>
        @else
            <div class="mt-3 flex flex-wrap gap-2 justify-end">
                <div class="bg-slate-50 border border-slate-200 rounded px-3 py-1.5 text-right opacity-80">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none mb-0.5" title="Amount is zero because no items have been approved yet.">Approved Total {{ $filteredItemDetail->first()?->currency ?? '' }}</span>
                    <span class="text-sm font-bold text-slate-700 leading-none">0.00</span>
                </div>
            </div>
        @endif
    </div>

    @if($pr->remark)
    <div class="mt-4 bg-amber-50 rounded p-3 border border-amber-100">
        <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wide mb-1">Remarks</p>
        <p class="text-sm text-amber-900">{{ $pr->remark }}</p>
    </div>
    @endif

    @php
        $canApprove = $flags['canApprove'] ?? false;
        $prId       = $pr->id;
    @endphp

    @if($canApprove)
        <div class="mt-6 pt-5 border-t border-slate-200 flex flex-col gap-4 bg-slate-50/50 -mx-5 -mb-5 p-5 rounded-b-xl">
            @if($showRejectInput)
                <div class="w-full animate-fade-in">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Reject Reason <span class="text-rose-500">*</span></label>
                    <textarea wire:model="rejectReason" rows="2" placeholder="Required for rejection..." class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm focus:border-rose-400 focus:ring-rose-400"></textarea>
                    @error('rejectReason') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    <div class="flex gap-2 mt-2 justify-end">
                        <button type="button" wire:click="toggleRejectInput" class="px-3 py-1.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                        <button type="button" wire:click="submitReject" class="px-4 py-1.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-colors">Confirm Reject</button>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3 animate-fade-in">
                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                        <i class="bi bi-info-circle text-indigo-400"></i>
                        Approving will auto-approve all pending items.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                wire:click="toggleRejectInput"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-5 py-2.5 text-sm font-bold text-rose-600 shadow-sm hover:bg-rose-50 hover:border-rose-300 transition-all">
                            <i class="bi bi-x-circle"></i>
                            Reject
                        </button>

                        <button type="button" 
                                wire:click="approve"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-200 hover:bg-emerald-700 transition-all hover:-translate-y-0.5">
                            <i class="bx bx-check-double text-lg"></i>
                            Approve
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
