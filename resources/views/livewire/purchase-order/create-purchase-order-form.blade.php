<div>
    @include('partials.alert-success-error')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest">
                <li class="inline-flex items-center">
                    <a href="{{ route('po.dashboard') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-slate-300 text-sm mx-1"></i>
                        <a href="{{ route('po.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                            List
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-slate-300 text-sm mx-1"></i>
                        <span class="text-slate-600">Create</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Create New Purchase Order</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Fill in the details below to create a new purchase order.</p>
        </div>

        {{-- Create form --}}
        <div class="bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-2xl shadow-sm"
              x-data="poForm({
                  total: @entangle('total').live,
                  currency: @entangle('currency'),
                  pdfFileName: null,
                  isSubmitting: false
              })">
            <form class="p-8 space-y-6">
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
                <label for="po_number" class="block text-sm font-medium text-gray-700">
                    PO Number <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                        <input type="text"
                               wire:model.blur="po_number"
                               id="po_number"
                               class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('po_number') border-red-300 @enderror">
                    @error('po_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Vendor Name --}}
            <div>
                <label for="vendor_name" class="block text-sm font-medium text-gray-700">
                    Vendor Name <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="text"
                           wire:model.blur="vendor_name"
                           id="vendor_name"
                           list="vendors-list"
                           placeholder="Start typing to search..."
                           class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('vendor_name') border-red-300 @enderror">
                    <datalist id="vendors-list">
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
                <label for="currency" class="block text-sm font-medium text-gray-700">
                    Currency <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                        <select wire:model.live="currency"
                                id="currency"
                                class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('currency') border-red-300 @enderror">
                        <option value="IDR">IDR - Indonesian Rupiah</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="SGD">SGD - Singapore Dollar</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Total Amount --}}
            <div>
                <label for="total" class="block text-sm font-medium text-gray-700">
                    Total Amount <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm" x-text="currency"></span>
                    </div>
                    <input type="text"
                           x-model="totalFormatted"
                           @input="updateTotalFormatted($event.target.value)"
                           id="total"
                           placeholder="0"
                           class="pl-12 py-2.5 block w-full bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('total') border-red-300 @enderror">
                    @error('total')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Category --}}
            <div>
                <label for="purchase_order_category_id" class="block text-sm font-medium text-gray-700">
                    Category <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <select wire:model.blur="purchase_order_category_id"
                            id="purchase_order_category_id"
                            class="block w-full px-3 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner @error('purchase_order_category_id') border-red-300 @enderror">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                    @error('purchase_order_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- PDF File Upload --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">
                PDF File <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200/60 border-dashed rounded-xl hover:border-indigo-400 transition-all bg-slate-50/50">
                <div class="space-y-1 text-center" wire:loading.remove wire:target="pdf_file">
                    @if($pdf_file)
                        <div class="flex items-center justify-center">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-900">
                            <p class="font-medium" x-text="pdfFileName || '{{ $pdf_file->getClientOriginalName() }}'"></p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-gray-500" x-text="'{{ number_format($pdf_file->getSize() / 1024, 1) }} KB'"></p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="'Ready to upload'"></span>
                            </div>
                        </div>
                        <button type="button" @click="removeFile()" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Remove file
                        </button>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="pdf_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a PDF file</span>
                                <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" wire:model="pdf_file" @change="handleFileSelect($event)" x-ref="pdfFileInput" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF up to 5MB</p>
                    @endif
                </div>
                
                {{-- Uploading State --}}
                <div class="space-y-3 text-center" wire:loading wire:target="pdf_file">
                    <div class="flex justify-center">
                        <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-indigo-600 animate-pulse">
                        Uploading PDF file...
                    </div>
                    <p class="text-xs text-slate-400">Please wait while we process your document</p>
                </div>
            </div>
            @error('pdf_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="button"
                    wire:click="save(true)"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all disabled:opacity-50">
                <span wire:loading.remove wire:target="save(true)">
                    <i class="bi bi-bookmark mr-2"></i>
                    Save as Draft
                </span>
                <span wire:loading wire:target="save(true)">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-slate-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                </span>
            </button>
            
            <button type="button"
                    wire:click="save(false)"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md hover:bg-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save(false)">
                    <i class="bi bi-send mr-2"></i>
                    Create & Submit for Review
                </span>
                <span wire:loading wire:target="save(false)">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Submitting...
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
                    this.$wire.clearPdfFile();
                },

                resetFormState() {
                    this.isSubmitting = false;
                    this.pdfFileName = null;
                    this.total = '';
                    this.totalFormatted = '';
                }
            }
        }
    </script>
        </div>
    </div>
</div>
