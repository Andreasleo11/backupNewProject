@extends('layouts.app')

@section('content')
    <h1>MonthlyReport VQC</h1>
    <div class="mt-3 row">
        <div class=""></div>
        <div class="col-md-2">
            <form action="{{ route('monthlyreport.details') }}" method="POST" target="_blank">
                @csrf
                <label for="monthFilter">Filter by Month:</label>
                <select id="monthFilter" name="monthData" class="form-select">
                    <option value="all">All Months</option>
                    @foreach (array_keys($result) as $month)
                        <option value="{{ $month }}">{{ $month }}</option>
                    @endforeach
                </select>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">View Detail</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-3 mb-4">
        <div class="mt-2">
            <form id="exportForm" action="{{ route('monthlyreport.export') }}" method="POST">
                @csrf
                <input type="hidden" name="monthData" id="exportMonthData">
                <button type="submit" class="btn btn-success">Export to Excel</button>
            </form>
        </div>
    </div>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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

    <!-- <a href="{{ route('monthlyreport.details', ['month' => $month, 'data' => $result]) }}">View Details</a> -->

    @foreach ($result as $month => $customers)
        <div class="month-table" id="table-{{ $month }}" style="display: none;">
            <h2>{{ $month }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Total Cannot Use</th>
                        <th>Total Rec Quantity</th>
                        <th>Total Price (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customerId => $customerData)
                        <tr>
                            <td>{{ $customerId }}</td>
                            <td>{{ $customerData['cant_use'] }}</td>
                            <td>{{ $customerData['total_rec_quantity'] }}</td>
                            <td>{{ 'IDR ' . number_format($customerData['total_price'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthFilter = document.getElementById('monthFilter');
            const exportMonthData = document.getElementById('exportMonthData');

            // Initialize the hidden input with the current selected value
            exportMonthData.value = monthFilter.value;

            // Update the hidden input whenever the month filter changes
            monthFilter.addEventListener('change', function() {
                exportMonthData.value = monthFilter.value;
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var monthFilter = document.getElementById('monthFilter');
            var tables = document.querySelectorAll('.month-table');

            monthFilter.addEventListener('change', function() {
                var selectedMonth = monthFilter.value;

                tables.forEach(function(table) {
                    if (selectedMonth === 'all') {
                        table.style.display = 'none';
                    } else {
                        table.style.display = table.id === 'table-' + selectedMonth ? 'block' :
                            'none';
                    }
                });

                if (selectedMonth === 'all') {
                    tables.forEach(function(table) {
                        table.style.display = 'block';
                    });
                }
            });

            // Trigger change event to show the correct table on load
            monthFilter.dispatchEvent(new Event('change'));
        });
    </script>
@endsection
