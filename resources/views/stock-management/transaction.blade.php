@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="flex items-center gap-1 text-sm text-gray-500">
                <li>
                    <a href="{{ route('mastertinta.index') }}" class="font-medium text-gray-600 hover:text-indigo-600">
                        Management Stock
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="font-medium text-gray-900">
                    Edit
                </li>
            </ol>
        </nav>

        {{-- Error summary --}}
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Edit Management Stock
            </h2>
        </div>

        <form id="stock-form" action="{{ route('mastertinta.process') }}" method="post" class="space-y-4">
            @csrf

            {{-- Header card --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="p-4 sm:p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Stock Type --}}
                        <div>
                            <label for="stock_id" class="block text-sm font-medium text-gray-700">
                                Stock Type <span class="text-red-500">*</span>
                            </label>
                            <select name="stock_id" id="stock_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                   px-3 py-2
                   focus:border-indigo-500 focus:ring-indigo-500
                   @error('stock_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="" selected disabled>--Select Master Stock--</option>
                                @foreach ($masterStocks as $stock)
                                    <option value="{{ $stock->id }}"
                                        {{ old('stock_id') == $stock->id ? 'selected' : '' }}>
                                        {{ $stock->stock_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('stock_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Transaction Type --}}
                        <div>
                            <span class="block text-sm font-medium text-gray-700">
                                Transaction Type <span class="text-red-500">*</span>
                            </span>
                            <div class="mt-2 flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        type="radio" name="transaction_type" id="in" value="in"
                                        {{ old('transaction_type', 'out') === 'in' ? 'checked' : '' }}>
                                    <span>In</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        type="radio" name="transaction_type" id="out" value="out"
                                        {{ old('transaction_type', 'out') === 'out' ? 'checked' : '' }}>
                                    <span>Out</span>
                                </label>
                            </div>
                            @error('transaction_type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Department --}}
                        <div id="department-wrapper">
                            <label for="department" class="block text-sm font-medium text-gray-700">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <select name="department" id="department" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                   px-3 py-2
                   focus:border-indigo-500 focus:ring-indigo-500
                   @error('department') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="" selected disabled>--Select Department--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PIC --}}
                        <div id="pic-wrapper">
                            <label for="pic" class="block text-sm font-medium text-gray-700">
                                PIC <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="pic" name="pic" value="{{ old('pic') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                  px-3 py-2
                  focus:border-indigo-500 focus:ring-indigo-500
                  @error('pic') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('pic')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Remark --}}
                    <div id="remark-wrapper">
                        <label for="remark" class="block text-sm font-medium text-gray-700">
                            Remark <span class="text-red-500">*</span>
                        </label>
                        <textarea name="remark" id="remark" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                     px-3 py-2
                     focus:border-indigo-500 focus:ring-indigo-500
                     @error('remark') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Your remark here">{{ old('remark') }}</textarea>
                        @error('remark')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Available Quantity --}}
                    <div>
                        <label for="available_quantity" class="block text-sm font-medium text-gray-700">
                            Available Quantity
                        </label>
                        <input type="text" id="available_quantity" name="available_quantity" readonly
                            value="{{ old('available_quantity') }}"
                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-gray-700 text-sm
                  px-3 py-2
                  focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Items card (Alpine) --}}
            <div x-data="itemsForm({ initialItems: [] })" class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="p-4 sm:p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        List Items <span class="text-red-500">*</span>
                    </label>

                    <div id="item-container" class="space-y-2">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="item-row flex items-center gap-3 py-2 px-2 border border-gray-100 rounded-md">
                                {{-- No --}}
                                <div class="w-10 text-sm text-gray-500">
                                    <span x-text="index + 1"></span>
                                </div>

                                {{-- Item Name + dropdown --}}
                                <div class="relative flex-1" @click.away="closeDropdown(index)">
                                    <label class="sr-only" :for="'item_name_' + (index + 1)">Item Name</label>
                                    <input type="text"
                                        class="item-name block w-full rounded-md border-gray-300 shadow-sm text-sm
                                                  focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                                        :id="'item_name_' + (index + 1)" :name="'item_name_' + (index + 1)"
                                        x-model="item.name" placeholder="Item Name"
                                        @input.debounce.300ms="fetchSuggestions(index)">
                                    {{-- Dropdown --}}
                                    <div x-show="item.showDropdown && item.suggestions.length"
                                        class="absolute left-0 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg max-h-40 overflow-y-auto z-50"
                                        x-transition>
                                        <button type="button"
                                            class="block w-full text-left px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-50 border-b border-gray-100"
                                            @click="clearItem(index)">
                                            Clear
                                        </button>
                                        <template x-for="suggestion in item.suggestions" :key="suggestion.unique_code">
                                            <button type="button"
                                                class="dropdown-item block w-full text-left px-3 py-1.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                                                @click="selectSuggestion(index, suggestion.unique_code)">
                                                <span x-text="suggestion.unique_code"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Remove button --}}
                                <div>
                                    <button type="button"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500"
                                        @click="removeItem(index)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3">
                        <button type="button" id="add-item-btn"
                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            @click="addItem()">
                            Add Item
                        </button>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Submit
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            // TomSelect untuk department
            new TomSelect('#department', {
                plugins: ['dropdown_input'],
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>

    <script>
        // Alpine component untuk items
        function itemsForm({
            initialItems
        }) {
            return {
                items: initialItems.length ?
                    initialItems :
                    [{
                        id: Date.now(),
                        name: '',
                        suggestions: [],
                        showDropdown: false
                    }],

                addItem() {
                    this.items.push({
                        id: Date.now() + Math.random(),
                        name: '',
                        suggestions: [],
                        showDropdown: false,
                    });
                },

                removeItem(index) {
                    if (this.items.length === 1) return; // minimal 1 row
                    this.items.splice(index, 1);
                },

                clearItem(index) {
                    this.items[index].name = '';
                    this.items[index].suggestions = [];
                    this.items[index].showDropdown = false;
                },

                closeDropdown(index) {
                    this.items[index].showDropdown = false;
                },

                async fetchSuggestions(index) {
                    const item = this.items[index];
                    const query = item.name.trim();
                    const stockIdEl = document.getElementById('stock_id');
                    const stockId = stockIdEl ? stockIdEl.value : '';
                    const typeEl = document.querySelector('input[name="transaction_type"]:checked');
                    const transactionType = typeEl ? typeEl.value : 'out';

                    if (!query || !stockId || transactionType !== 'out') {
                        item.suggestions = [];
                        item.showDropdown = false;
                        return;
                    }

                    try {
                        const url = `/masterstock/get-items/${stockId}?name=${encodeURIComponent(query)}`;
                        const res = await fetch(url);
                        const data = await res.json();

                        item.suggestions = data;
                        item.showDropdown = data.length > 0;
                    } catch (e) {
                        console.error('Error fetching items:', e);
                        item.suggestions = [];
                        item.showDropdown = false;
                    }
                },

                selectSuggestion(index, code) {
                    this.items[index].name = code;
                    this.items[index].showDropdown = false;
                },
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            function fetchAvailableQuantity() {
                const stockId = document.getElementById('stock_id').value;
                const departmentId = document.getElementById('department').value;

                if (stockId && departmentId) {
                    document.getElementById('available_quantity').value = '0';

                    fetch(`/stock/get-available-quantity/${stockId}/${departmentId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('available_quantity').value = data.available_quantity;
                        })
                        .catch(error => console.error('Error fetching available quantity:', error));
                }
            }

            // toggle field In/Out
            const transactionTypeInputs = document.querySelectorAll('input[name="transaction_type"]');
            const departmentField = document.getElementById('department');
            const picField = document.getElementById('pic');
            const remarkField = document.getElementById('remark');

            const departmentWrapper = document.getElementById('department-wrapper');
            const picWrapper = document.getElementById('pic-wrapper');
            const remarkWrapper = document.getElementById('remark-wrapper');

            const toggleFields = () => {
                const isOut = document.getElementById('out').checked;
                const displayStyle = isOut ? 'block' : 'none';

                departmentWrapper.style.display = displayStyle;
                picWrapper.style.display = displayStyle;
                remarkWrapper.style.display = displayStyle;

                departmentField.disabled = !isOut;
                picField.disabled = !isOut;
                remarkField.disabled = !isOut;
            };

            transactionTypeInputs.forEach(input => input.addEventListener('change', toggleFields));
            toggleFields();

            // quantity
            document.getElementById('stock_id').addEventListener('change', fetchAvailableQuantity);
            document.getElementById('department').addEventListener('change', fetchAvailableQuantity);
        });
    </script>
@endpush
