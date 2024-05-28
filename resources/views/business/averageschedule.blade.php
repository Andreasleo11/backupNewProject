@extends('layouts.app')

@section('content')

<H1>Average Delivery Schedule Per Month</H1>

<label for="monthFilter">Filter by Month:</label>
<select id="monthFilter">
    <option value="all">All Months</option>
    @foreach($totalQuantities as $month => $items)
        <option value="{{ $month }}">{{ $month }}</option>
    @endforeach
</select>


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
            <th>Quantity</th>
            <th>Count</th>
            <th>Average with Count</th>
            <th>In Stock</th>
            <!-- <th>Day In Stock / Average</th> -->
        </tr>
    </thead>
    <tbody>
        @foreach($totalQuantities as $month => $items)
        @php $first = true; @endphp
            @foreach($items as $itemCode => $quantity)
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ $itemCode }}</td>
                    <td>{{ $quantity }}</td>
                    <td>{{ $itemCounts[$month][$itemCode] ?? 0 }}</td>
                    @php
                        $averageWithCount = ($quantity / $itemCounts[$month][$itemCode]) ?? 0;
                        $averagePerMonth = $quantity / 30; // Assuming 30 days in a month
                        $inStock = 0;

                        $inStock = floor($result[$month][$itemCode] ?? 0);

                        $newColumnValue = $averageWithCount > 0 ? floor($inStock / $averageWithCount) : 0;
                    @endphp
                    <td>{{ $averageWithCount }}</td>
                    <td>{{ $inStock }}</td>
                    <!-- <td>{{ $newColumnValue }}</td> -->
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var monthFilter = document.getElementById('monthFilter');
        var deliveryTable = document.getElementById('deliveryTable').querySelector('tbody');
        var allRows = deliveryTable.querySelectorAll('tr');

        monthFilter.addEventListener('change', function() {
            var selectedMonth = monthFilter.value;

            allRows.forEach(function(row) {
                var monthColumn = row.cells[0].textContent; // Get the content of the first cell (Month column)
                if (selectedMonth === 'all' || monthColumn === selectedMonth) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>

@endsection