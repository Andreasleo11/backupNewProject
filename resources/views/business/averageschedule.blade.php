@extends('layouts.app')

@section('content')

<h1>Average Delivery Schedule Per Month</h1>

<div style="margin-bottom: 20px;">
    <label for="daysFilter">Filter by Days:</label>
    <select id="daysFilter" class="custom-select">
        <option value="all">All</option>
        <option value="small">0 - 1 (Small)</option>
        <option value="middle">2 - 7 (Middle)</option>
        <option value="huge">8+ (Huge)</option>
    </select>
</div>

<div style="margin-bottom: 20px;">
    <label for="itemCodeFilter">Filter by Item Code:</label>
    <select id="itemCodeFilter" multiple="multiple" class="custom-select" style="width: 100%;">
        @foreach($totalQuantities as $month => $items)
            @foreach($items as $itemCode => $quantity)
                @php
                    $itemName = isset($result[$month][$itemCode]['item_name']) ? $result[$month][$itemCode]['item_name'] : '';
                    $optionText = $itemCode . ' - ' . $itemName;
                @endphp
                <option value="{{ $itemCode }}">{{ $optionText }}</option>
            @endforeach
        @endforeach
    </select>
</div>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>

<table id="deliveryTable">
    <thead>
        <tr>
            <th>Month</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Count</th>
            <th>Average with Count</th>
            <th>In Stock</th>
            <th>Quantity</th>
            <th>Days</th>
        </tr>
    </thead>
    <tbody>
        @foreach($totalQuantities as $month => $items)
            @foreach($items as $itemCode => $quantity)
                @php
                    $count = $itemCounts[$month][$itemCode] ?? 0;
                    $averageWithCount = $count > 0 ? round($quantity / $count) : 0;
                    $inStock = isset($result[$month][$itemCode]['in_stock']) ? $result[$month][$itemCode]['in_stock'] : 0;
                    $itemName = isset($result[$month][$itemCode]['item_name']) ? $result[$month][$itemCode]['item_name'] : '';
                    $days = $averageWithCount > 0 ? floor($inStock / $averageWithCount) : 0;
                @endphp
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ $itemCode }}</td>
                    <td>{{ $itemName }}</td>
                    <td>{{ $quantity }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ $averageWithCount }}</td>
                    <td>{{ $inStock }}</td>
                    <td>{{ $days }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('#itemCodeFilter').select2({
            placeholder: "Select Item Code(s)",
            allowClear: true
        });

        var daysFilter = document.getElementById('daysFilter');
        var itemCodeFilter = $('#itemCodeFilter');  // jQuery object for Select2
        var deliveryTable = document.getElementById('deliveryTable').querySelector('tbody');
        var allRows = deliveryTable.querySelectorAll('tr');

        function filterRows() {
            var selectedDays = daysFilter.value;
            var selectedItems = itemCodeFilter.val(); // Get the selected values from Select2

            allRows.forEach(function(row) {
                var days = parseInt(row.cells[7].textContent); // Get the content of the Days column
                var itemCode = row.cells[1].textContent; // Get the content of the Item Code column

                var daysMatch = selectedDays === 'all' ||
                    (selectedDays === 'small' && days >= 0 && days <= 1) ||
                    (selectedDays === 'middle' && days >= 2 && days <= 7) ||
                    (selectedDays === 'huge' && days >= 8);

                var itemCodeMatch = selectedItems.length === 0 || selectedItems.includes(itemCode);

                if (daysMatch && itemCodeMatch) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add event listeners to filter the table based on selected filters
        daysFilter.addEventListener('change', filterRows);
        itemCodeFilter.on('change', filterRows);  // Use jQuery's 'on' method for Select2

        // Automatically trigger filtering when the page loads
        filterRows();
    });
</script>

@endsection
