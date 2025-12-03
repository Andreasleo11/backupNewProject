@extends('new.layouts.app')

@section('content')
    {{-- XLSX untuk export Excel --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <div class="px-4 py-4 md:px-6 md:py-6">
        {{-- Header + tombol ke Delivery Schedule --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-900">
                    FG Stock Monitoring
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Pantau stok FG berdasarkan delivery schedule dan estimasi hari stok.
                </p>
            </div>

            <button type="button" onclick="openInNewTab('{{ route('indexds') }}')"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-2
                           text-sm font-medium text-white shadow-sm hover:bg-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Delivery Schedule For Verification
            </button>
        </div>

        {{-- Filter controls --}}
        <div class="grid gap-4 md:grid-cols-2 md:max-w-3xl mb-5">
            {{-- Filter by days --}}
            <div class="flex flex-col md:flex-row md:items-end gap-2 md:gap-3">
                <div>
                    <label for="daysFilter" class="block text-sm font-medium text-slate-700">
                        Filter by Days
                    </label>
                    <p class="text-xs text-slate-400">
                        Berdasarkan estimasi hari stok (Stock Days).
                    </p>
                </div>
                <select id="daysFilter"
                    class="mt-1 md:mt-0 block w-full md:w-40 rounded-md border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-900 shadow-sm
                               focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="all">All</option>
                    <option value="small">0 - 1 (Small)</option>
                    <option value="middle">2 - 5 (Middle)</option>
                    <option value="huge">6+ (Huge)</option>
                </select>
            </div>

            {{-- Filter by item code --}}
            <div class="flex flex-col gap-1">
                <label for="itemCodeFilter" class="block text-sm font-medium text-slate-700">
                    Filter by Item Code
                </label>
                <select id="itemCodeFilter" multiple
                    class="block w-full min-h-[3rem] rounded-md border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-900 shadow-sm
                               focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    @foreach ($totalQuantities as $month => $items)
                        @foreach ($items as $itemCode => $quantity)
                            @php
                                $itemName = isset($result[$month][$itemCode]['item_name'])
                                    ? $result[$month][$itemCode]['item_name']
                                    : '';
                                $optionText = $itemCode . ' - ' . $itemName;
                            @endphp
                            <option value="{{ $itemCode }}">{{ $optionText }}</option>
                        @endforeach
                    @endforeach
                </select>
                <p class="text-xs text-slate-400">
                    Gunakan <span class="font-semibold">Ctrl / Cmd</span> untuk pilih lebih dari satu item.
                </p>
            </div>
        </div>

        {{-- Export button --}}
        <button type="button" onclick="exportToExcel()"
            class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 mb-4
                       text-sm font-medium text-white shadow-sm hover:bg-emerald-700
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
            Export Data to Excel
        </button>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table id="deliveryTable" class="min-w-full text-sm text-left text-slate-700">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2 border-b border-slate-200">Month</th>
                        <th class="px-3 py-2 border-b border-slate-200">Item Code</th>
                        <th class="px-3 py-2 border-b border-slate-200">Item Name</th>
                        <th class="px-3 py-2 border-b border-slate-200">Warehouse</th>
                        <th class="px-3 py-2 border-b border-slate-200">Total Delivery</th>
                        <th class="px-3 py-2 border-b border-slate-200">Delivery Freq</th>
                        <th class="px-3 py-2 border-b border-slate-200">Avg Per Delivery</th>
                        <th class="px-3 py-2 border-b border-slate-200">In Stock</th>
                        <th class="px-3 py-2 border-b border-slate-200">Stock Days</th>
                        <th class="px-3 py-2 border-b border-slate-200">Min Stock (2 Days)</th>
                        <th class="px-3 py-2 border-b border-slate-200">Max Stock (5 Days)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($totalQuantities as $month => $items)
                        @foreach ($items as $itemCode => $quantity)
                            @php
                                $count = $itemCounts[$month][$itemCode] ?? 0;
                                $averageWithCount = $count > 0 ? round($quantity / $count) : 0;
                                $inStock = isset($result[$month][$itemCode]['in_stock'])
                                    ? $result[$month][$itemCode]['in_stock']
                                    : 0;
                                $itemName = isset($result[$month][$itemCode]['item_name'])
                                    ? $result[$month][$itemCode]['item_name']
                                    : '';
                                $warehouse = $result[$month][$itemCode]['warehouse'] ?? '';
                                $days = $averageWithCount > 0 ? floor($inStock / $averageWithCount) : 0;
                                $minStock = $averageWithCount * 2;
                                $maxStock = $averageWithCount * 5;
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $month }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $itemCode }}</td>
                                <td class="px-3 py-2">{{ $itemName }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $warehouse }}</td>
                                <td class="px-3 py-2 text-right">{{ $quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ $count }}</td>
                                <td class="px-3 py-2 text-right">{{ $averageWithCount }}</td>
                                <td class="px-3 py-2 text-right">{{ $inStock }}</td>
                                <td class="px-3 py-2 text-right">{{ $days }}</td>
                                <td class="px-3 py-2 text-right">{{ $minStock }}</td>
                                <td class="px-3 py-2 text-right">{{ $maxStock }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="11" class="px-3 py-4 text-center text-slate-500">
                                No Data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openInNewTab(url) {
            const win = window.open(url, '_blank');
            if (win) {
                win.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const daysFilter = document.getElementById('daysFilter');
            const itemCodeFilter = document.getElementById('itemCodeFilter');
            const deliveryTableBody = document.querySelector('#deliveryTable tbody');
            const allRows = Array.from(deliveryTableBody.querySelectorAll('tr'));

            function getSelectedItemCodes() {
                return Array.from(itemCodeFilter.selectedOptions).map(opt => opt.value);
            }

            function filterRows() {
                const selectedDays = daysFilter.value;
                const selectedItems = getSelectedItemCodes();

                allRows.forEach(row => {
                    // Skip row "No Data" atau row yang tidak lengkap
                    if (row.cells.length < 9) return;

                    const days = parseInt(row.cells[8].textContent || '0', 10); // Stock Days kolom index 8
                    const itemCode = row.cells[1].textContent.trim(); // Item Code kolom index 1

                    const daysMatch =
                        selectedDays === 'all' ||
                        (selectedDays === 'small' && days >= 0 && days <= 1) ||
                        (selectedDays === 'middle' && days >= 2 && days <= 5) ||
                        (selectedDays === 'huge' && days >= 6);

                    const itemCodeMatch =
                        selectedItems.length === 0 || selectedItems.includes(itemCode);

                    row.style.display = daysMatch && itemCodeMatch ? 'table-row' : 'none';
                });
            }

            daysFilter.addEventListener('change', filterRows);
            itemCodeFilter.addEventListener('change', filterRows);

            // Trigger filter saat pertama kali load
            filterRows();
        });

        function exportToExcel() {
            const table = document.getElementById('deliveryTable');
            const wb = XLSX.utils.table_to_book(table, {
                sheet: 'Sheet1'
            });
            XLSX.writeFile(wb, 'fg_stock_monitoring.xlsx');
        }
    </script>
@endsection

{{-- di layout atau di view ini --}}
@push('head')
    {{-- jQuery + Select2 CSS --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#itemCodeFilter').select2({
                placeholder: "Select Item Code(s)",
                allowClear: true,
                width: '100%' // biar nyatu sama Tailwind width
            });

            const daysFilter = document.getElementById('daysFilter');
            const itemCodeFilter = $('#itemCodeFilter');
            const deliveryTableBody = document.querySelector('#deliveryTable tbody');
            const allRows = Array.from(deliveryTableBody.querySelectorAll('tr'));

            function filterRows() {
                const selectedDays = daysFilter.value;
                const selectedItems = itemCodeFilter.val() ?? [];

                allRows.forEach(row => {
                    if (row.cells.length < 9) return;

                    const days = parseInt(row.cells[8].textContent || '0', 10);
                    const itemCode = row.cells[1].textContent.trim();

                    const daysMatch =
                        selectedDays === 'all' ||
                        (selectedDays === 'small' && days >= 0 && days <= 1) ||
                        (selectedDays === 'middle' && days >= 2 && days <= 5) ||
                        (selectedDays === 'huge' && days >= 6);

                    const itemCodeMatch =
                        selectedItems.length === 0 || selectedItems.includes(itemCode);

                    row.style.display = (daysMatch && itemCodeMatch) ? 'table-row' : 'none';
                });
            }

            daysFilter.addEventListener('change', filterRows);
            itemCodeFilter.on('change', filterRows);

            filterRows();
        });
    </script>
@endpush
