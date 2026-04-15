@extends('new.layouts.app')

@section('page-title', 'Create Verification Report')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        @if ($message = Session::get('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-6">
                {{-- Progress Indicator --}}
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full font-bold">1
                    </div>
                    <div class="flex-1 bg-slate-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full w-1/2"></div>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 border-2 border-blue-600 text-blue-600 rounded-full font-bold bg-white">
                        2</div>
                    <div class="flex-1 bg-slate-200 rounded-full h-3">
                        <div class="bg-slate-200 h-3 rounded-full w-0"></div>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 border-2 border-blue-600 text-blue-600 rounded-full font-bold bg-white">
                        3</div>
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-xl font-semibold text-slate-900 mb-2">Create Verification Header</h2>
                    <p class="text-slate-600 mb-6">You need to fill the verification report header</p>

                    <form action="{{ route('qaqc.report.createheader') }}" method="post" class="space-y-6">
                        @csrf
                        <input type="hidden" value="{{ Auth::user()->name }}" name="created_by">

                        {{-- Rec'D Date --}}
                        <div class="space-y-2">
                            <label for="rec_date" class="block text-sm font-medium text-slate-700">Rec'D Date:</label>
                            <input type="date" value="{{ $header->rec_date ?? '' }}" id="rec_date" name="rec_date"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required>
                        </div>

                        {{-- Verify Date --}}
                        <div class="space-y-2">
                            <label for="verify_date" class="block text-sm font-medium text-slate-700">Verify Date:</label>
                            <input type="date" value="{{ $header->verify_date ?? '' }}" id="verify_date"
                                name="verify_date"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required>
                        </div>

                        {{-- Customer --}}
                        <div class="space-y-2">
                            <label for="customer" class="block text-sm font-medium text-slate-700">Customer:</label>
                            <div class="relative">
                                <input type="text" value="{{ $header->customer ?? '' }}" id="itemNameInput"
                                    name="customer"
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    required placeholder="Enter customer name" autocomplete="off">
                                <div id="itemDropdown"
                                    class="absolute z-10 w-full bg-white border border-slate-300 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                                </div>
                            </div>
                        </div>

                        {{-- Invoice No --}}
                        <div class="space-y-2">
                            <label for="invoice_no" class="block text-sm font-medium text-slate-700">Invoice No:</label>
                            <input type="text" value="{{ $header->invoice_no ?? '' }}" id="invoice_no" name="invoice_no"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                required>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                                Next
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemNameInput = document.getElementById('itemNameInput');
            const itemDropdown = document.getElementById('itemDropdown');

            itemNameInput.addEventListener('keyup', function() {
                const inputValue = itemNameInput.value.trim();

                // Make an AJAX request to fetch relevant items
                fetch(`/customers?customer_name=${inputValue}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear previous dropdown options
                        itemDropdown.innerHTML = '';

                        // Display dropdown options
                        if (data.length > 0) {
                            data.forEach(item => {
                                const option = document.createElement('div');
                                option.className =
                                    'px-3 py-2 hover:bg-slate-100 cursor-pointer text-sm';
                                option.textContent = item;
                                option.addEventListener('click', function() {
                                    itemNameInput.value = item;
                                    itemDropdown.classList.add('hidden');
                                    itemDropdown.innerHTML = '';
                                });
                                itemDropdown.appendChild(option);
                            });
                            itemDropdown.classList.remove('hidden');
                        } else {
                            itemDropdown.classList.add('hidden');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!itemNameInput.contains(event.target) && !itemDropdown.contains(event.target)) {
                    itemDropdown.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
