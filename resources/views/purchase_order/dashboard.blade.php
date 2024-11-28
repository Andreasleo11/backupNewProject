@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="row mb-3 justify-content-between">
            <div class="col">
                <h1>Purchase Order Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('po.dashboard') }}">Purchase Orders</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <!-- Month Filter -->
                <select id="monthFilter" class="form-select d-inline-block w-auto">
                    @foreach ($availableMonths as $month)
                        <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                            {{ $month }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <form id="checkDetailForm" action="{{ route('po.index') }}" method="GET">
                    <input type="hidden" name="month" id="selectedMonthInput">
                    <button type="button" class="btn btn-primary" id="checkDetailButton">Check Detail</button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Total Monthly Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="ps-3 pt-3">
                        <div class="h4">Monthly Totals</div>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTotalsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Highest Vendor -->
            <div class="col-md-4">
                <div class="card">
                    <div class="ps-3 pt-3">
                        <div class="h4">Top Vendor</div>
                    </div>
                    <div class="card-body text-center">
                        <h5 id="highestVendorName">{{ $highestVendor?->vendor_name ?? 'N/A' }}</h5>
                        <p class="fw-bold">Total: IDR <span
                                id="highestVendorTotal">{{ number_format($highestVendor?->total ?? 0, 2) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Totals Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="ps-3 pt-3">
                        <div class="h4">Vendors</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Vendor Name</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="vendorTableBody">
                                @if ($vendorTotals->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center">No data available for the selected month.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($vendorTotals as $vendor)
                                        <tr>
                                            <td>{{ $vendor->vendor_name }}</td>
                                            <td>IDR {{ number_format($vendor->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extraJs')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthlyTotalsChart').getContext('2d');
            let monthlyTotalsChart = null;

            // Initialize Chart
            function updateChart(data) {
                if (monthlyTotalsChart) {
                    monthlyTotalsChart.destroy();
                }
                monthlyTotalsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Total (IDR)',
                            data: data.totals,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'IDR ' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Function to load data from the filter endpoint
            function loadData(selectedMonth) {
                fetch(`/purchase-orders/filter?month=${selectedMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update Chart
                        updateChart(data.chartData);

                        // Update Top Vendor
                        const highestVendorName = document.getElementById('highestVendorName');
                        const highestVendorTotal = document.getElementById('highestVendorTotal');
                        if (highestVendorName && highestVendorTotal) {
                            if (data.highestVendor) {
                                highestVendorName.textContent = data.highestVendor.vendor_name;
                                highestVendorTotal.textContent = new Intl.NumberFormat().format(data
                                    .highestVendor.total);
                            } else {
                                highestVendorName.textContent = 'No data available for the selected month.';
                                highestVendorTotal.textContent = '';
                            }
                        }

                        // Update Vendors Table
                        const tableBody = document.getElementById('vendorTableBody');
                        if (tableBody) {
                            tableBody.innerHTML = '';
                            if (data.vendorTotals.length > 0) {
                                data.vendorTotals.forEach(vendor => {
                                    tableBody.innerHTML += `
                                <tr>
                                    <td>${vendor.vendor_name}</td>
                                    <td>IDR ${new Intl.NumberFormat().format(vendor.total)}</td>
                                </tr>`;
                                });
                            } else {
                                tableBody.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center">No data available for the selected month.</td>
                            </tr>`;
                            }
                        }
                    })
                    .catch(error => console.error('Error loading data:', error));
            }

            // Trigger the filter method on page load with the default selected month
            const initialMonth = document.getElementById('monthFilter').value;
            loadData(initialMonth);

            // Handle Month Filter Change
            document.getElementById('monthFilter').addEventListener('change', function() {
                const selectedMonth = this.value;
                loadData(selectedMonth);
            });
        });

        document.getElementById('checkDetailButton').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthFilter').value;
            document.getElementById('selectedMonthInput').value = selectedMonth;
            document.getElementById('checkDetailForm').submit();
        });

        $(function() {
            const table = $('#purchaseorder-table').DataTable();

            // Add event listener to filter by month
            $('#monthFilter').on('change', function() {
                const selectedMonth = $(this).val();

                // Reload DataTable with the selected month
                table.ajax.url("{{ route('po.index') }}?month=" + selectedMonth).load();
            });
        });
    </script>
@endpush
