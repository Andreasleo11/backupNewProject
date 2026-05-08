<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
            <i class="bi bi-receipt text-emerald-500"></i>
            Invoices & Payments
        </h2>
        @can('manageInvoices', $purchaseOrder)
            <button wire:click="create" class="h-8 px-3 rounded-lg bg-indigo-50 text-indigo-600 flex items-center gap-2 hover:bg-indigo-100 transition-colors text-xs font-bold">
                <i class="bi bi-plus-lg"></i> Add Invoice
            </button>
        @endcan
    </div>

    @php
        $totalInvoiced = collect($invoices)->sum('total');
        $poTotal = $purchaseOrderTotal;
        $remaining = max(0, $poTotal - $totalInvoiced);
        $completionPercentage = $poTotal > 0 ? min(100, ($totalInvoiced / $poTotal) * 100) : 0;
    @endphp

    {{-- Progress Bar --}}
    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
        <div class="flex justify-between items-end mb-2">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoiced Amount</p>
                <p class="text-lg font-black text-slate-900 mt-0.5">
                    {{ number_format($totalInvoiced, 2, '.', ',') }}
                    <span class="text-xs font-bold text-slate-400 uppercase">/ {{ number_format($poTotal, 2, '.', ',') }}</span>
                </p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Remaining</p>
                <p class="text-sm font-bold text-amber-600 mt-0.5">
                    {{ number_format($remaining, 2, '.', ',') }}
                </p>
            </div>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden flex">
            <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $completionPercentage }}%"></div>
        </div>
        <p class="text-[10px] font-bold text-slate-500 mt-2 text-right">{{ number_format($completionPercentage, 1) }}% Completed</p>
    </div>

    {{-- Invoice List --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                <tr>
                    <th class="px-6 py-3">Invoice #</th>
                    <th class="px-6 py-3">Dates</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3">Attachments</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900">{{ $invoice->invoice_number ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase w-12">Inv:</span>
                                <span class="text-slate-700">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase w-12">Pay:</span>
                                <span class="text-slate-700">{{ $invoice->payment_date ? $invoice->payment_date->format('d M Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $invoice->total_currency }}</span>
                            <span class="font-mono font-bold text-slate-800 text-sm ml-1">{{ number_format($invoice->total, 2, '.', ',') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @php $fileCount = $invoice->files->count(); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold">
                                    <i class="bi bi-paperclip"></i> {{ $fileCount }}
                                </span>
                                
                                @can('manageInvoices', $purchaseOrder)
                                    <button @click="$dispatch('open-upload-modal', { docId: 'INV-{{ $invoice->id }}' })" 
                                            class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded text-[10px] font-bold hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100">
                                        Upload
                                    </button>
                                @endcan
                            </div>
                            
                            @if($fileCount > 0)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($invoice->files as $file)
                                        <a href="{{ asset('storage/files/' . $file->name) }}" target="_blank" title="{{ $file->name }}"
                                           class="h-6 w-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-100 hover:text-indigo-600 transition-colors">
                                           <i class="bi bi-file-earmark"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('manageInvoices', $purchaseOrder)
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $invoice->id }})" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-all shadow-sm border border-indigo-100">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete({{ $invoice->id }})" wire:confirm="Are you sure you want to delete this invoice?" class="h-8 w-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-all shadow-sm border border-rose-100">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">
                            No invoices recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">{{ $invoiceId ? 'Edit Invoice' : 'Add Invoice' }}</h3>
                        <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Invoice Number</label>
                            <input type="text" wire:model="invoice_number" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
                            @error('invoice_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Invoice Date</label>
                                <input type="date" wire:model="invoice_date" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
                                @error('invoice_date') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Payment Date (Optional)</label>
                                <input type="date" wire:model="payment_date" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
                                @error('payment_date') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Currency</label>
                                <select wire:model="total_currency" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
                                    <option value="IDR">IDR</option>
                                    <option value="USD">USD</option>
                                    <option value="YUAN">YUAN</option>
                                </select>
                                @error('total_currency') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Total Amount</label>
                                <input type="number" step="0.01" wire:model="total" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
                                @error('total') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save" class="px-6 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                        <i class="bi bi-check-lg" wire:loading.remove wire:target="save"></i>
                        <span wire:loading wire:target="save" class="h-4 w-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        Save Invoice
                    </button>
                </div>
            </div>
        </template>
    @endif
</div>
