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

    {{-- Currency Info & Progress Bar --}}
    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
        {{-- Currency Status Indicator --}}
        @if($currencyInfo['has_mismatches'] || $currencyInfo['mixed_currencies'])
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-amber-700">Currency Mismatch Detected</span>
                    <span class="text-[10px] text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">
                        {{ $currencyInfo['invoice_currencies']->count() }} currencies used
                    </span>
                </div>
                <button wire:click="$set('showCurrencyDetails', !$wire.showCurrencyDetails)"
                        class="text-xs text-amber-600 hover:text-amber-800 font-medium flex items-center gap-1">
                    <span x-text="$wire.showCurrencyDetails ? 'Hide Details' : 'Show Details'"></span>
                    <i class="bi" :class="$wire.showCurrencyDetails ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>

            {{-- Expandable Currency Details --}}
            <div x-show="$wire.showCurrencyDetails"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-96"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 max-h-96"
                 x-transition:leave-end="opacity-0 max-h-0"
                 class="mb-4 p-3 bg-amber-50/50 border border-amber-200 rounded-lg overflow-hidden">
                <div class="text-xs text-amber-700">
                    <p class="mb-2">
                        <strong>PO Currency:</strong> {{ $currencyInfo['po_currency'] }}
                    </p>
                    <p class="mb-2">
                        <strong>Invoice Currencies:</strong> {{ $currencyInfo['invoice_currencies']->join(', ') }}
                    </p>
                    <p class="text-amber-600 italic text-[11px]">Progress calculation only includes matching currency invoices.</p>
                </div>
            </div>
        @endif

        <div class="flex justify-between items-end mb-2">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoiced Amount ({{ $currencyInfo['po_currency'] }})</p>
                <p class="text-lg font-black text-slate-900 mt-0.5">
                    {{ number_format($currencyInfo['total_invoiced_matching'], 2, '.', ',') }}
                    <span class="text-xs font-bold text-slate-400 uppercase">/ {{ number_format($currencyInfo['po_total'], 2, '.', ',') }}</span>
                </p>
                @if($currencyInfo['has_mismatched_invoices'])
                    <p class="text-xs font-bold text-amber-600 mt-1">
                        + {{ number_format($currencyInfo['total_invoiced_mismatched'], 2, '.', ',') }} in other currencies (excluded from progress)
                    </p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Remaining</p>
                <p class="text-sm font-bold text-amber-600 mt-0.5">
                    {{ number_format($currencyInfo['remaining'], 2, '.', ',') }}
                </p>
                @if($currencyInfo['has_mismatched_invoices'])
                    <p class="text-[10px] font-bold text-amber-500 mt-1">
                        Progress calculation excludes mismatched currencies
                    </p>
                @endif
            </div>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden flex">
            <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $currencyInfo['completion_percentage'] }}%"></div>
        </div>
        <p class="text-[10px] font-bold text-slate-500 mt-2 text-right">{{ number_format($currencyInfo['completion_percentage'], 1) }}% Completed</p>
    </div>

    {{-- Invoice List --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                <tr>
                    <th class="px-6 py-3">Invoice #</th>
                    <th class="px-6 py-3">Dates</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3">Currency</th>
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
                             <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format($invoice->total, 2, '.', ',') }}</span>
                         </td>
                          <td class="px-6 py-4">
                              <div class="flex items-center gap-1">
                                  <span class="text-xs font-bold text-slate-700">{{ $invoice->total_currency }}</span>
                                  @if($invoice->total_currency !== $purchaseOrder->currency)
                                      <i class="bi bi-exclamation-triangle-fill text-amber-500 text-xs" title="Currency differs from PO"></i>
                                  @else
                                      <i class="bi bi-check-circle-fill text-emerald-500 text-xs" title="Matches PO currency"></i>
                                  @endif
                              </div>
                          </td>
                          <td class="px-6 py-4">
                              @php $fileCount = $invoice->files->count(); @endphp
                              <div class="flex items-center gap-2">
                                  @if($fileCount > 0)
                                      <button wire:click="openAttachmentModal('{{ $invoice->id }}')"
                                              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 text-xs font-bold transition-colors">
                                          <i class="bi bi-paperclip"></i>
                                          {{ $fileCount }} file{{ $fileCount !== 1 ? 's' : '' }}
                                      </button>
                                  @else
                                      <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold">
                                          <i class="bi bi-paperclip"></i>
                                          No files
                                      </span>
                                  @endif

                                  @can('manageInvoices', $purchaseOrder)
                                      <button @click="$dispatch('open-upload-modal', { docId: 'INV-{{ $invoice->id }}' })"
                                              class="p-1.5 bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100">
                                          <i class="bi bi-plus-lg text-sm"></i>
                                      </button>
                                  @endcan
                              </div>
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
                         <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">
                             No invoices recorded yet.
                         </td>
                     </tr>
                 @endforelse
            </tbody>
        </table>
    </div>

    {{-- Attachment Modal --}}
    @if($attachmentModalInvoice)
        <template x-teleport="body">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">
                            Attachments for Invoice #{{ $attachmentModalInvoice->invoice_number ?? 'N/A' }}
                        </h3>
                        <button wire:click="closeAttachmentModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <div class="p-6 max-h-96 overflow-y-auto">
                        @php $modalFileCount = $attachmentModalInvoice->files->count(); @endphp
                        @if($modalFileCount > 0)
                            <div class="space-y-3">
                                <div class="text-sm text-slate-600 mb-4">
                                    {{ $modalFileCount }} file{{ $modalFileCount !== 1 ? 's' : '' }} attached
                                </div>
                                @foreach($attachmentModalInvoice->files as $file)
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                                <i class="bi bi-file-earmark text-lg"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-900 truncate" title="{{ $file->name }}">
                                                    {{ $file->name }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ number_format($file->size / 1024, 1) }} KB • Uploaded {{ $file->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/files/' . $file->name) }}"
                                               target="_blank"
                                               class="p-2 text-slate-400 hover:text-indigo-600 transition-colors rounded-lg hover:bg-white"
                                               title="Download">
                                                <i class="bi bi-download text-lg"></i>
                                            </a>
                                             @can('manageInvoices', $purchaseOrder)
                                                 <button wire:click="deleteFile({{ $file->id }})"
                                                         wire:confirm="Are you sure you want to delete '{{ addslashes($file->name) }}'?"
                                                         class="p-2 text-slate-400 hover:text-rose-600 transition-colors rounded-lg hover:bg-white"
                                                         title="Delete file">
                                                     <i class="bi bi-trash text-lg"></i>
                                                 </button>
                                             @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-slate-400">
                                <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                    <i class="bi bi-file-earmark-x text-3xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-slate-600 mb-2">No Files Attached</h4>
                                <p class="text-sm text-slate-500">This invoice doesn't have any attached files yet.</p>
                            </div>
                        @endif
                    </div>

                    @can('manageInvoices', $purchaseOrder)
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                            <button wire:click="closeAttachmentModal"
                                    class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                                Close
                            </button>
                        </div>
                    @endcan
                </div>
            </div>
        </template>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
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
                                <select wire:model.live="total_currency" class="w-full px-2 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-2.5 shadow-sm transition-all">
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

                        {{-- Currency Mismatch Warning --}}
                        @if($currencyMismatchWarning)
                            <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-amber-600 text-sm flex-shrink-0"></i>
                                <p class="text-xs text-amber-700 flex-1">
                                    Currency mismatch: <strong>{{ $total_currency }}</strong> vs PO <strong>{{ $purchaseOrder->currency }}</strong>.
                                    Exchange rates required.
                                </p>
                            </div>
                        @endif
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
            </div>
        </template>
    @endif
</div>
