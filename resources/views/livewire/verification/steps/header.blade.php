<div class="bg-white rounded-xl border border-slate-300 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
            <i class="bi bi-file-earmark-text mr-1.5"></i> Report General Information
        </h2>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Receive Date --}}
            <div>
                <label for="fld-form-rec-date" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Receive Date <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-lg shadow-sm">
                    <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-slate-350 bg-slate-55 text-slate-400 text-sm">
                        <i class="bi bi-calendar-event"></i>
                    </span>
                    <input id="fld-form-rec-date" type="date"
                        class="w-full rounded-r-lg border-slate-350 text-slate-900 bg-slate-55/50 text-sm py-2.5 px-3.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('form.rec_date') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                        wire:model.live.debounce.300ms="form.rec_date" 
                        autocomplete="off">
                </div>
                @error('form.rec_date')
                    <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Verify Date --}}
            <div>
                <label for="fld-form-verify-date" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Verify Date <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-lg shadow-sm">
                    <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-slate-350 bg-slate-55 text-slate-400 text-sm">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                    <input id="fld-form-verify-date" type="date"
                        class="w-full rounded-none border-slate-355 text-slate-900 bg-slate-55/50 text-sm py-2.5 px-3.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('form.verify_date') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                        min="{{ $form['rec_date'] ?? '' }}" 
                        wire:model.live.debounce.300ms="form.verify_date"
                        autocomplete="off">
                    <button type="button" 
                        class="inline-flex items-center justify-center font-bold rounded-r-lg border border-l-0 border-slate-350 bg-white text-slate-700 hover:bg-slate-50 px-4 text-sm focus:outline-none focus:ring-4 focus:ring-blue-50/50 transition-all"
                        wire:click="$set('form.verify_date', @js($form['rec_date'] ?? ''))">
                        Copy
                    </button>
                </div>
                @error('form.verify_date')
                    <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                    </div>
                @enderror
                <div class="text-xs text-slate-400 mt-1">Must be on or after Receive Date.</div>
            </div>

            {{-- Customer --}}
            <div>
                <label for="fld-form-customer" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Customer <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-lg shadow-sm">
                    <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-slate-350 bg-slate-55 text-slate-400 text-sm">
                        <i class="bi bi-building"></i>
                    </span>
                    <input id="fld-form-customer" type="text" list="customer-list"
                        class="w-full rounded-r-lg border-slate-350 text-slate-900 bg-slate-55/50 text-sm py-2.5 px-3.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('form.customer') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                        placeholder="Select or type Customer..."
                        wire:model.live.debounce.300ms="form.customer"
                        autocomplete="off">
                    <datalist id="customer-list">
                        @foreach ($customers as $cust)
                            <option value="{{ $cust->name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                @error('form.customer')
                    <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Invoice Number --}}
            <div>
                <label for="fld-form-invoice" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Invoice Number <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-lg shadow-sm">
                    <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-slate-350 bg-slate-55 text-slate-400 text-sm">
                        <i class="bi bi-receipt"></i>
                    </span>
                    <input id="fld-form-invoice" type="text"
                        class="w-full rounded-r-lg border-slate-350 text-slate-900 bg-slate-55/50 text-sm py-2.5 px-3.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('form.invoice_number') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                        placeholder="Invoice #"
                        wire:model.live.debounce.300ms="form.invoice_number" 
                        autocomplete="off">
                </div>
                @error('form.invoice_number')
                    <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>
</div>
