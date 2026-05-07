<div class="bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-2xl shadow-sm"
     x-data="poForm({
         total: @entangle('total').live,
         currency: @entangle('currency'),
         pdfFileName: @js($purchaseOrder->filename ? basename($purchaseOrder->filename) : null),
         isSubmitting: false,
         showUpload: false
     })">
    
    <form wire:submit="save" class="p-8 space-y-6">
        {{-- General Error --}}
        @if($errors->has('general'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ $errors->first('general') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form Grid --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {{-- PO Number --}}
            <div>
                <label for="po_number_edit" class="block text-sm font-medium text-gray-700">
                    PO Number <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="number"
                            wire:model.blur="po_number"
                            id="po_number_edit"
                            class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('po_number') border-red-300 @enderror">
                    @error('po_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Vendor Name --}}
            <div>
                <label for="vendor_name_edit" class="block text-sm font-medium text-gray-700">
                    Vendor Name <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="text"
                            wire:model.blur="vendor_name"
                            id="vendor_name_edit"
                            list="vendors-list-edit"
                            placeholder="Start typing to search..."
                            class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('vendor_name') border-red-300 @enderror">
                    <datalist id="vendors-list-edit">
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor }}">
                        @endforeach
                    </datalist>
                    @error('vendor_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Currency --}}
            <div>
                <label for="currency_edit" class="block text-sm font-medium text-gray-700">
                    Currency <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <select wire:model.live="currency"
                            id="currency_edit"
                            class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('currency') border-red-300 @enderror">
                        <option value="IDR" {{ $currency === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                        <option value="USD" {{ $currency === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ $currency === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="SGD" {{ $currency === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Total Amount --}}
            <div>
                <label for="total_edit" class="block text-sm font-medium text-gray-700">
                    Total Amount <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm" x-text="currency"></span>
                    </div>
                    <input type="text"
                            x-model="totalFormatted"
                        @input="updateTotalFormatted($event.target.value)"
                            id="total_edit"
                            placeholder="0"
                            class="pl-12 py-2.5 block w-full bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('total') border-red-300 @enderror">
                    @error('total')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Category --}}
            <div>
                <label for="purchase_order_category_id_edit" class="block text-sm font-medium text-gray-700">
                    Category <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <select wire:model.blur="purchase_order_category_id"
                            id="purchase_order_category_id_edit"
                            class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('purchase_order_category_id') border-red-300 @enderror">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}" {{ $purchase_order_category_id == $category['id'] ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Document Management --}}
        <div class="col-span-full border-t border-slate-100 pt-6">
            <label class="block text-sm font-semibold text-slate-700 mb-4">
                Purchase Order Document
            </label>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Current Document Card --}}
                @if($purchaseOrder && $purchaseOrder->filename)
                    <div class="relative group overflow-hidden bg-slate-50 border border-slate-200 rounded-2xl transition-all hover:border-indigo-200 hover:bg-indigo-50/30 p-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white rounded-xl shadow-sm border border-slate-100">
                                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-900 truncate">
                                    {{ basename($purchaseOrder->filename) }}
                                </p>
                                <p class="text-xs text-slate-500">Current active document</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ Storage::url($purchaseOrder->filename) }}" target="_blank" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors" title="View Document">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Upload/Change Action --}}
                <div class="relative min-h-[88px]">
                    <div x-show="!showUpload && !@js($pdf_file)" class="h-full">
                        <button type="button" @click="showUpload = true" class="w-full h-full p-4 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center gap-3 text-slate-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all group">
                            <svg class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span class="text-sm font-black uppercase tracking-wider">Replace Document</span>
                        </button>
                    </div>

                    <div x-show="showUpload || @js($pdf_file)" x-cloak class="h-full">
                        <div class="p-4 border-2 border-indigo-100 bg-indigo-50/20 rounded-2xl">
                            <div wire:loading.remove wire:target="pdf_file">
                                @if($pdf_file)
                                    <div class="flex items-center gap-4">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-black text-slate-900 truncate" x-text="pdfFileName"></p>
                                            <p class="text-xs text-green-600 font-bold">New document ready</p>
                                        </div>
                                        <button type="button" @click="removeFile(); showUpload = false" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between gap-4">
                                        <label for="pdf_file_edit" class="flex-1 cursor-pointer flex items-center gap-3">
                                            <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-100">
                                                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-slate-600">Select new PDF...</span>
                                            <input id="pdf_file_edit" name="pdf_file" type="file" accept=".pdf" wire:model="pdf_file" @change="handleFileSelect($event)" x-ref="pdfFileInput" class="sr-only">
                                        </label>
                                        <button type="button" @click="showUpload = false" class="text-xs font-black text-slate-400 hover:text-slate-600 uppercase tracking-tight">Cancel</button>
                                    </div>
                                @endif
                            </div>

                            {{-- Uploading State --}}
                            <div wire:loading wire:target="pdf_file" class="flex items-center gap-4">
                                <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-bold text-indigo-600 animate-pulse">Uploading document...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @error('pdf_file')
                <p class="mt-2 text-sm text-red-600 font-bold flex items-center gap-1">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="submit"
                    wire:loading.attr="disabled"
                    x-bind:disabled="isSubmitting"
                    @click="submitForm()"
                    class="inline-flex items-center px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md hover:bg-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>Update Purchase Order</span>
                <span wire:loading>
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Updating...
                </span>
            </button>
        </div>
    </form>
   
    {{-- Alpine.js TALL Stack Integration --}}
    <script>
        function poForm(data) {
            return {
                ...data,
                totalFormatted: '',

                init() {
                    // Initial format
                    this.totalFormatted = this.formatTotal(this.total);

                    // Watch for changes from Livewire
                    this.$watch('total', (val) => {
                        this.totalFormatted = this.formatTotal(val);
                    });

                    // Listen for form reset events from Livewire
                    this.$wire.on('formReset', () => {
                        this.resetFormState();
                    });
                },

                formatTotal(value) {
                    if (value === null || value === undefined || value === '') return '';
                    let cleanValue = value.toString().replace(/,/g, '');
                    const parts = cleanValue.split('.');
                    if (parts.length > 2) {
                        parts.splice(2);
                    }
                    parts[0] = parts[0].replace(/\D/g, '');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    return parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
                },

                updateTotalFormatted(value) {
                    this.totalFormatted = this.formatTotal(value);
                    this.total = this.totalFormatted.replace(/,/g, '');
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    this.pdfFileName = file ? file.name : null;
                },

                removeFile() {
                    this.pdfFileName = null;
                    if (this.$refs.pdfFileInput) {
                        this.$refs.pdfFileInput.value = '';
                    }
                    this.$wire.clearPdfFile();
                },

                submitForm() {
                    this.isSubmitting = true;
                    this.$wire.call('save').then(() => {
                        this.resetFormState();
                    }).catch(() => {
                        this.isSubmitting = false;
                    });
                },

                resetFormState() {
                    this.isSubmitting = false;
                    this.pdfFileName = @js($purchaseOrder->filename ? basename($purchaseOrder->filename) : null);
                }
            }
        }
    </script>
</div>
