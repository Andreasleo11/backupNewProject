<div>
    <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6 max-w-[1600px] mx-auto">
        {{-- Header --}}
        <header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $purchaseOrder->po_number }}
                        </h1>
                        @include('partials.po-status', ['po' => $purchaseOrder])
                        
                        @if($purchaseOrder->workflow_status === 'IN_REVIEW' && $purchaseOrder->current_approver)
                            <div class="flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-700 rounded-full border border-amber-100 text-xs font-bold animate-pulse">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                Currently with: {{ $purchaseOrder->current_approver }}
                            </div>
                        @endif
                    </div>
                    <nav class="mt-3" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <li><a href="{{ route('po.index') }}" class="hover:text-indigo-600 transition-colors">Purchase Orders</a></li>
                            <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                            <li class="text-slate-600">Purchase Order Detail</li>
                        </ol>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('po.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                        <i class="bi bi-arrow-left"></i>
                        Back to List
                    </a>
                    <a href="{{ route('po.download', $purchaseOrder->id) }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98]">
                        <i class="bi bi-cloud-arrow-down-fill text-lg"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Main Content: Timeline & PDF --}}
            <div class="lg:col-span-8 space-y-6">
                
                @role('super-admin')
                    {{-- Activity Feed --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden" x-data="{ showHistory: false }">
                        <div @click="showHistory = !showHistory" class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between cursor-pointer group/header hover:bg-slate-100/80 transition-all">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-clock-history text-indigo-500"></i>
                                Activity History
                            </h2>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-bold text-slate-400 group-hover/header:text-slate-600 transition-colors" x-show="!showHistory">Click to expand audit trail</span>
                                <i class="bi bi-chevron-down text-slate-400 transition-transform duration-300" :class="showHistory ? 'rotate-180' : ''"></i>
                            </div>
                        </div>
                        <div class="p-6 border-t border-slate-50" x-show="showHistory" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                            <div class="relative space-y-6 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-indigo-100 before:via-slate-100 before:to-transparent">
                                @forelse($activities as $activity)
                                    <div class="relative flex items-start group">
                                        <div class="absolute left-0 flex h-10 w-10 items-center justify-center rounded-2xl bg-white ring-4 ring-slate-50 transition-all group-hover:scale-110 group-hover:shadow-md">
                                            <i class="bi {{ $activity->icon }} text-{{ $activity->color }}-500 text-lg"></i>
                                        </div>
                                        <div class="ml-16">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                                                <span class="text-sm font-black text-slate-800">{{ $activity->label }}</span>
                                                <time class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter flex items-center gap-1">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ \Carbon\Carbon::parse($activity->date)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                                </time>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-1">
                                                Performed by <span class="font-bold text-slate-900">{{ $activity->user }}</span>
                                            </p>
                                            @if(isset($activity->remarks) && $activity->remarks)
                                                <div class="mt-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 leading-relaxed italic shadow-inner">
                                                    "{{ $activity->remarks }}"
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-10">
                                        <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="bi bi-inbox text-slate-300 text-2xl"></i>
                                        </div>
                                        <p class="text-slate-400 text-sm font-medium">No activity history found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endrole

                {{-- PDF View --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill text-rose-500"></i>
                            Original Document
                        </h2>
                    </div>
                    <div class="p-2 bg-slate-800">
                        <iframe src="{{ asset('storage/pdfs/' . $purchaseOrder->filename) }}#toolbar=0"
                            class="w-full h-[900px] rounded-2xl shadow-2xl" frameborder="0"></iframe>
                    </div>
                </div>

                {{-- Revisions --}}
                @if ($purchaseOrder->status === 4 || $purchaseOrder->revision_count > 0 || count($revisions) > 0)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-layers-half text-amber-500"></i>
                                Version History
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/80 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">PO Number</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3 text-right">Amount</th>
                                        <th class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($revisions as $rev)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-3 font-bold text-slate-900">{{ $rev->po_number }}</td>
                                            <td class="px-6 py-3">@include('partials.po-status', ['po' => $rev])</td>
                                            <td class="px-6 py-3 text-right font-mono font-bold text-slate-700">
                                                {{ number_format($rev->total, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-3">
                                                <a href="{{ route('po.view', $rev->id) }}" class="inline-flex items-center gap-1 text-indigo-600 font-bold hover:gap-2 transition-all">
                                                    View <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar: Summary & Actions --}}
            <aside class="lg:col-span-4 space-y-6">
                
                {{-- Rejection Status Card --}}
                @if($purchaseOrder->getStatusEnum()->label() === 'Rejected')
                    @php
                        $latestRejection = $purchaseOrder->approvalRequest?->actions
                            ?->where('to_status', 'REJECTED')
                            ->sortByDesc('created_at')
                            ->first();
                    @endphp
                    
                    @if($latestRejection && $latestRejection->remarks)
                        <div class="bg-white rounded-3xl shadow-sm border-2 border-rose-100 overflow-hidden">
                            <div class="bg-rose-50 px-6 py-4 border-b border-rose-100 flex items-center gap-3">
                                <div class="h-8 w-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-lg shadow-rose-200">
                                    <i class="bi bi-exclamation-octagon"></i>
                                </div>
                                <h3 class="text-xs font-black text-rose-600 uppercase tracking-widest">Rejection Details</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-sm font-bold text-slate-800 leading-relaxed italic">
                                    "{{ $latestRejection->remarks }}"
                                </p>
                                <div class="mt-4 flex items-center gap-3 pt-4 border-t border-slate-50">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Rejected By</p>
                                        <p class="text-[11px] font-bold text-slate-700 mt-1">{{ $latestRejection->causer->name ?? 'System' }}</p>
                                    </div>
                                    <div class="ml-auto text-right">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Date</p>
                                        <p class="text-[11px] font-bold text-slate-500 mt-1">{{ $latestRejection->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Quick Actions Card --}}
                @if ($purchaseOrder->workflow_status === 'IN_REVIEW' && $director)
                    <div class="bg-indigo-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group {{ $loading ? 'opacity-90' : '' }}" 
                         x-data="{ showSignConfirm: false, showReject: false }">
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        
                        @if($loading)
                            <div class="relative z-10 py-4 flex flex-col items-center justify-center text-center">
                                <div class="h-12 w-12 border-4 border-white/20 border-t-white rounded-full animate-spin mb-4"></div>
                                <h3 class="text-xl font-black mb-1">Processing Request</h3>
                                <p class="text-sm text-indigo-100 italic">Please wait while we secure the document and update the workflow...</p>
                            </div>
                        @else
                            <h3 class="text-xl font-black mb-1.5 flex items-center gap-3 relative z-10">
                                <i class="bi bi-shield-check"></i>
                                Quick Actions
                            </h3>
                            <p class="text-xs text-indigo-100 mb-6 font-medium tracking-wide relative z-10">Take direct action on this purchase order</p>

                            <div class="space-y-3 relative z-10">
                                {{-- Primary Action: Approve --}}
                                <div x-show="!showSignConfirm && !showReject" x-transition>
                                    <button @click="showSignConfirm = true"
                                            class="w-full bg-white text-indigo-600 py-3 rounded-2xl font-black text-sm hover:bg-indigo-50 transition-all active:scale-[0.98] shadow-lg shadow-indigo-900/20 flex items-center justify-center gap-2">
                                        <i class="bi bi-pen-fill"></i>
                                        Sign & Approve
                                    </button>
                                    <button @click="showReject = true"
                                            class="w-full mt-3 bg-indigo-500/30 text-white py-2.5 rounded-xl font-bold text-xs hover:bg-indigo-500/50 transition-all flex items-center justify-center gap-2 border border-white/10">
                                        <i class="bi bi-x-lg"></i>
                                        Reject Order
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Sign Modal --}}
                        <template x-teleport="body">
                            <div x-show="showSignConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
                                <div @click.outside="showSignConfirm=false" class="relative w-full max-w-md rounded-[2.5rem] bg-white shadow-2xl p-8 space-y-6">
                                    <div class="h-16 w-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mx-auto">
                                        <i class="bi bi-vector-pen text-3xl"></i>
                                    </div>
                                    <div class="text-center space-y-2 text-slate-900">
                                        <h3 class="text-2xl font-black">Authorize Purchase</h3>
                                        <p class="text-sm text-slate-500 leading-relaxed">
                                            Your digital signature will be applied to the official PDF. This action is irreversible and legally binding.
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <button wire:click="approve" @click="showSignConfirm=false" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-lg hover:bg-indigo-700 transition-all">
                                            Apply Digital Signature
                                        </button>
                                        <button @click="showSignConfirm=false" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                            Maybe later
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Reject Modal --}}
                        <template x-teleport="body">
                            <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-on:close-reject-modal.window="showReject = false">
                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
                                <div @click.outside="showReject=false" class="relative w-full max-w-md rounded-[2.5rem] bg-white shadow-2xl p-8 space-y-6 text-center text-slate-900">
                                    <div class="h-16 w-16 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 mx-auto">
                                        <i class="bi bi-x-octagon text-3xl"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <h3 class="text-2xl font-black">Reject Order</h3>
                                        <p class="text-sm text-slate-500">Provide a reason to help the requester improve this PO.</p>
                                    </div>
                                    <textarea wire:model="reason" rows="4" class="w-full rounded-2xl border-slate-200 focus:border-rose-500 focus:ring-rose-500 text-sm placeholder:text-slate-300" placeholder="e.g., Price mismatch with quotation..."></textarea>
                                    <div class="flex flex-col gap-3">
                                        <button wire:click="reject" :disabled="!reason || $wire.loading" class="w-full py-4 bg-rose-600 text-white rounded-2xl font-black shadow-lg hover:bg-rose-700 transition-all disabled:opacity-50">
                                            Confirm Rejection
                                        </button>
                                        <button @click="showReject=false" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif

                {{-- Financial & Info Card --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden divide-y divide-slate-50">
                    <div class="p-6 text-slate-900">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <i class="bi bi-wallet2 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Valuation</p>
                                <p class="text-xl font-black text-slate-900 mt-0.5">
                                    <span class="text-xs font-bold text-slate-300 uppercase mr-1">{{ $purchaseOrder->currency }}</span>
                                    {{ number_format($purchaseOrder->total, 2, '.', ',') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Vendor Profile</label>
                                <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $purchaseOrder->vendor_name }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Department Category</label>
                                <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $purchaseOrder->category->name ?? 'General Procurement' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/30 grid grid-cols-2 gap-4 text-slate-900">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inv. Date</label>
                            <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $purchaseOrder->invoice_date ? $purchaseOrder->invoice_date->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pymt. Date</label>
                            <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $purchaseOrder->tanggal_pembayaran ? \Carbon\Carbon::parse($purchaseOrder->tanggal_pembayaran)->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Related Files --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Attachments</h2>
                        @if (Auth::id() == $purchaseOrder->creator_id || Auth::user()->hasRole('purchaser'))
                            <button @click="$dispatch('open-upload-modal')" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        @endif
                    </div>
                    <div class="p-6">
                        @include('partials.file-attachments', [
                            'files' => $files,
                            'showDelete' => Auth::id() === $purchaseOrder->creator_id || Auth::user()->hasRole('purchaser'),
                            'title' => ''
                        ])
                    </div>
                </div>

                {{-- Requester Info Footer --}}
                <div class="p-6 bg-slate-900 rounded-3xl text-white flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-lg">
                        {{ substr($purchaseOrder->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Originator</p>
                        <p class="text-sm font-bold truncate">{{ $purchaseOrder->user->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Dept</p>
                        <p class="text-xs font-bold text-indigo-300">{{ $purchaseOrder->user->department->name ?? 'N/A' }}</p>
                    </div>
                </div>

            </aside>
        </div>
    </div>

    @push('modals')
        @include('partials.upload-files-modal', ['doc_id' => $purchaseOrder->po_number])
    @endpush
</div>
