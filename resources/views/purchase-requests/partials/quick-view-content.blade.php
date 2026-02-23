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
                @include('partials.pr-status-badge', ['pr' => $pr])
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
        @if(count($totals) > 0)
            <div class="mt-3 flex flex-wrap gap-2 justify-end">
                @foreach($totals as $currency => $amount)
                    <div class="bg-indigo-50 border border-indigo-100 rounded px-3 py-1.5 text-right">
                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block leading-none mb-0.5">Total {{ $currency }}</span>
                        <span class="text-sm font-bold text-indigo-700 leading-none">{{ number_format((float) $amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($pr->remark)
    <div class="mt-4 bg-amber-50 rounded p-3 border border-amber-100">
        <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wide mb-1">Remarks</p>
        <p class="text-sm text-amber-900">{{ $pr->remark }}</p>
    </div>
    @endif
</div>
