<div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl">
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
                <label for="po_number" class="block text-sm font-medium text-gray-700">
                    PO Number <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="number"
                           wire:model.blur="po_number"
                           id="po_number"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('po_number') border-red-300 @enderror">
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
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('vendor_name') border-red-300 @enderror">
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
                    <select wire:model.blur="currency"
                            id="currency"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('currency') border-red-300 @enderror">
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
                        <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                    </div>
                    <input type="text"
                           wire:model.blur="total"
                           id="total"
                           placeholder="0"
                           class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('total') border-red-300 @enderror"
                           oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/(\..*)\./g, '$1');">
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
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('purchase_order_category_id') border-red-300 @enderror">
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
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                <div class="space-y-1 text-center">
                    @if($pdf_file)
                        <div class="flex items-center justify-center">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-900">
                            <p class="font-medium">{{ $pdf_file->getClientOriginalName() }}</p>
                            <p class="text-gray-500">{{ number_format($pdf_file->getSize() / 1024, 1) }} KB</p>
                        </div>
                        <button type="button" wire:click="$set('pdf_file', null)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Remove file
                        </button>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="pdf_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a PDF file</span>
                                <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" wire:model="pdf_file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF up to 5MB</p>
                    @endif
                </div>
            </div>
            @error('pdf_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button wire:click="exitFormMode"
                    wire:loading.attr="disabled"
                    type="button"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>Cancel</span>
                <span wire:loading>
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Returning...
                </span>
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>Create Purchase Order</span>
                <span wire:loading>
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating...
                </span>
            </button>
        </div>
    </form>

    {{-- amount formatting --}}
    <script>
        document.getElementById('total').addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                parts.splice(2);
            }
            parts[0] = parts[0].replace(/\D/g, '');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            e.target.value = parts.join('.');
        });
    </script>
</div>