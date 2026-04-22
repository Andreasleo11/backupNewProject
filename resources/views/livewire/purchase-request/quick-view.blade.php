<div x-data="{ open: false }" @open-quick-view-drawer.window="open = true" @close-quick-view-modal.window="open = false"
    x-show="open" class="fixed inset-0 z-[1100]" x-cloak>

    {{-- BACKDROP --}}
    <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity"></div>

    {{-- SIDE DRAWER --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div x-show="open" x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full" class="relative w-screen max-w-2xl" @click.away="open = false">

            <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl border-l border-slate-200">
                <div
                    class="sticky top-0 z-20 bg-white/90 backdrop-blur-md px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="bx bx-show-alt text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-tight">Quick Requisition
                                Review</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Live Preview
                                Engine</p>
                        </div>
                    </div>
                    <button @click="open = false"
                        class="h-9 w-9 rounded-xl hover:bg-slate-50 text-slate-400 transition-colors flex items-center justify-center">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>

                <div class="relative flex-1 p-6 custom-scrollbar">
                    @if ($isLoading)
                        <div class="flex flex-col items-center justify-center h-64 text-center">
                            <div
                                class="h-16 w-16 border-4 border-slate-100 border-t-indigo-600 rounded-full animate-spin mb-4">
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Initializing
                                Preview...</p>
                        </div>
                    @elseif($prId && isset($pr))
                        {{-- EXISTING CONTENT START --}}
                        <div class="space-y-6">
                            {{-- Status Banner --}}
                            <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                                <div>
                                    <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Document Number</h6>
                                    <div class="font-mono text-lg font-bold text-slate-900 tracking-tight">
                                        {{ $pr->doc_num }}</div>
                                </div>
                                <div class="text-right">
                                    <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        State</h6>
                                    @include('partials.workflow-status-badge', ['record' => $pr])
                                </div>
                            </div>

                            {{-- Info Cards --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-sm">
                                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">
                                        Requestor Origin</p>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold text-slate-700 flex items-center gap-2"><i
                                                class="bx bx-user opacity-40"></i>{{ $pr->createdBy->name ?? 'Unknown' }}
                                        </p>
                                        <p class="text-xs font-medium text-slate-500 flex items-center gap-2"><i
                                                class="bx bx-building-house opacity-40"></i>{{ $pr->from_department }}
                                            ({{ $pr->branch->value ?? $pr->branch }})</p>
                                    </div>
                                </div>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-sm">
                                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2">
                                        Timeline & Destination</p>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold text-slate-700 flex items-center gap-2"><i
                                                class="bx bx-calendar opacity-40"></i>{{ \Carbon\Carbon::parse($pr->date_pr)->format('d M Y') }}
                                        </p>
                                        <p class="text-xs font-medium text-slate-500 flex items-center gap-2"><i
                                                class="bx bx-right-arrow-alt opacity-40"></i>To:
                                            {{ $pr->to_department->value ?? $pr->to_department }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Items Listing --}}
                            <div>
                                <div class="flex items-center justify-between mb-3 px-1">
                                    <h6 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                        Permitted Line Items ({{ $filteredItemDetail->count() }})</h6>
                                    <span
                                        class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-tighter">Read
                                        Only View</span>
                                </div>
                                <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                    Item Description</th>
                                                <th
                                                    class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                                    Qty</th>
                                                <th
                                                    class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right whitespace-nowrap">
                                                    Price/Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50 bg-white">
                                            @forelse($filteredItemDetail as $item)
                                                <tr class="hover:bg-indigo-50/20 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <p
                                                            class="text-xs font-bold text-slate-800 line-clamp-2 leading-snug">
                                                            {{ $item->item_name }}</p>
                                                        @if ($item->purpose)
                                                            <p
                                                                class="text-[10px] text-slate-400 mt-1 italic line-clamp-1">
                                                                For: {{ $item->purpose }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span
                                                            class="text-xs font-black text-slate-700">{{ (float) $item->quantity }}</span>
                                                        <span
                                                            class="text-[9px] font-bold text-slate-400 block">{{ $item->uom }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="text-[10px] text-slate-400 font-bold mb-0.5">
                                                            {{ $item->currency }}
                                                            @if($flags['canViewPrices'])
                                                                {{ number_format((float) $item->price, 2) }}
                                                            @else
                                                                <span class="text-slate-400">***</span>
                                                            @endif</div>
                                                        <div class="text-xs font-black text-slate-900">
                                                            @if($flags['canViewPrices'])
                                                                {{ number_format((float) $item->price * (float) $item->quantity, 2) }}
                                                            @else
                                                                <span class="text-slate-400">***</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3"
                                                        class="px-4 py-12 text-center text-xs text-slate-400 italic">No
                                                        details available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-slate-900 text-white">
                                            <tr>
                                                <td colspan="2"
                                                    class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-indigo-200">
                                                    Total Validated Value</td>
                                                <td class="px-4 py-3 text-right">
                                                    <span
                                                        class="text-[10px] font-bold text-indigo-400 mr-1">{{ $totals['currency'] ?? '' }}</span>
                                                    <span
                                                        @if($flags['canViewPrices'])
                                                            class="text-sm font-black">{{ number_format((float) $totals['total'], 2) }}</span>
                                                        @else
                                                            <span class="text-sm font-black text-slate-400">***</span>
                                                        @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            @if ($pr->remark)
                                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 flex gap-4">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-amber-200 flex items-center justify-center text-amber-700 shrink-0">
                                        <i class="bx bxs-quote-alt-left text-lg"></i></div>
                                    <div>
                                        <p
                                            class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1 leading-none">
                                            Global Remark</p>
                                        <p class="text-xs text-amber-900 font-medium italic">{{ $pr->remark }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- ACTIONS --}}
                            @if ($flags['canApprove'] ?? false)
                                <div class="pt-6 border-t border-slate-100">
                                    @if ($showRejectInput)
                                        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-100 animate-fade-in">
                                            <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-3">
                                                Rejection Justification</h4>
                                            <textarea wire:model="rejectReason" rows="3" placeholder="Explain why this request is being rejected..."
                                                class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm focus:border-rose-400 focus:ring-rose-400 placeholder-rose-300"></textarea>
                                            @error('rejectReason')
                                                <p class="text-[10px] font-bold text-rose-600 mt-2 ml-1">
                                                    {{ $message }}</p>
                                            @enderror
                                            <div class="flex gap-2 mt-4">
                                                <button wire:click="submitReject"
                                                    class="flex-1 bg-rose-600 text-white py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-700 transition-colors">Submit
                                                    Rejection</button>
                                                <button wire:click="toggleRejectInput"
                                                    class="px-4 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-bold uppercase transition-colors hover:bg-rose-100">Cancel</button>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="flex items-center justify-between gap-4 p-4 bg-slate-900 rounded-[2rem] shadow-xl">
                                            <button @click="open = false"
                                                class="text-[10px] font-black text-slate-500 uppercase tracking-widest hover:text-white transition-colors px-4">Skip</button>
                                            <div class="flex items-center gap-2">
                                                <button wire:click="toggleRejectInput"
                                                    class="h-11 px-6 rounded-2xl border border-slate-700 text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95">Reject</button>
                                                <button wire:click="approve"
                                                    class="h-11 px-8 rounded-2xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 transition-all hover:-translate-y-1 active:translate-y-0">Approve
                                                    PR</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        {{-- EXISTING CONTENT END --}}
                    @endif
                </div>

                <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Press ESC to dismiss
                        preview</p>
                    <a href="{{ $prId ? route('purchase-requests.show', $prId) : '#' }}"
                        class="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-1 group">
                        Full Detail <i
                            class="bx bx-right-arrow-alt transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
