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
                        <div class="text-secondary">Sum of all purchase order total for each month.</div>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTotalsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Highest Vendor -->
            <div class="col-md-4">
                <div class="card">
                    <div class="p-3">
                        <div class="h4">Top Vendor</div>
                        <div class="text-secondary">Vendor with the highest sum of total based on the selected month.
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 id="highestVendorName">{{ $highestVendor?->vendor_name ?? 'N/A' }}</h5>
                        <p class="fs-5 fw-bold">Total: IDR <span
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
                        <div class="text-secondary">List of vendor with sum total based on the selected month.</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Vendor Name</th>
                                    <th>Total</th>
                                    <th>PO Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="vendorTableBody">
                                @if ($vendorTotals->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center">No data available for the selected month.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($vendorTotals as $vendor)
                                        <tr class="vendor-row" data-vendor="{{ $vendor->vendor_name }}">
                                            <td>{{ $vendor->vendor_name }}</td>
                                            <td>IDR {{ number_format($vendor->total, 2) }}</td>
                                            <td>{{ $vendor->po_count }}</td>
                                            <td>Action</td>
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
    <div class="modal fade" id="vendorMonthlyTotalsModal" tabindex="-1" aria-labelledby="vendorMonthlyTotalsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorMonthlyTotalsModalLabel">Monthly Totals for
                        Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <canvas id="vendorMonthlyTotalsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vendorPOModal" tabindex="-1" aria-labelledby="vendorPOModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorPOModalLabel">Purchase Orders for Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Invoice Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="vendorPOTableBody">
                            <!-- Data will be populated dynamically -->
                        </tbody>
                    </table>
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
                                <tr class="vendor-row" data-vendor="${vendor.vendor_name}">
                                    <td>${vendor.vendor_name}</td>
                                    <td>IDR ${new Intl.NumberFormat().format(vendor.total)}</td>
                                    <td>${vendor.po_count}</td>
                                    <td>
                                        <button class="btn btn-outline-primary rowDetailButton" data-vendor="${vendor.vendor_name}">Detail</button>
                                    </td>
                                </tr>`;
                                });
                            } else {
                                tableBody.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center">No data available for the selected month.</td>
                            </tr>`;
                            }
                        }

                        showModal();
                    })
                    .catch(error => console.error('Error loading data:', error));


            }

            function showModal() {
                // Handle Vendor Row Clicks
                const vendorRows = document.querySelectorAll('.vendor-row');
                vendorRows.forEach(row => {
                    row.addEventListener('click', function(e) {
                        // Prevent propagation if a button inside the row is clicked
                        if (e.target.closest('.rowDetailButton')) {
                            return;
                        }

                        const vendorName = this.getAttribute('data-vendor');

                        fetch(
                                `/purchase-orders/vendor-monthly-totals?vendor=${encodeURIComponent(vendorName)}`
                            )
                            .then(response => response.json())
                            .then(data => {
                                const modalTitle = document.getElementById(
                                    'vendorMonthlyTotalsModalLabel');
                                modalTitle.innerHTML =
                                    `Monthly Totals for <strong>${vendorName}</strong>`;

                                // Chart Rendering
                                const ctx = document.getElementById('vendorMonthlyTotalsChart')
                                    .getContext('2d');
                                const chartLabels = data.map(item => item.month);
                                const chartData = data.map(item => item.total);

                                // Destroy the chart if it already exists to avoid overlay
                                if (window.vendorChart) {
                                    window.vendorChart.destroy();
                                }

                                // Create a new chart
                                window.vendorChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: chartLabels,
                                        datasets: [{
                                            label: 'Total (IDR)',
                                            data: chartData,
                                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                        }],
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return 'IDR ' + value
                                                            .toLocaleString();
                                                    },
                                                },
                                            },
                                        },
                                    },
                                });

                                // Show the modal
                                const modal = new bootstrap.Modal(document.getElementById(
                                    'vendorMonthlyTotalsModal'));
                                modal.show();
                            })
                            .catch(error => console.error('Error fetching vendor monthly totals:',
                                error));
                    });
                });
            }


            // Trigger the filter method on page load with the default selected month
            const initialMonth = document.getElementById('monthFilter').value;
            loadData(initialMonth);

            // Handle Month Filter Change
            document.getElementById('monthFilter').addEventListener('change', function() {
                const selectedMonth = this.value;
                loadData(selectedMonth);
            });

            // Handle Detail Button Clicks
            const vendorTableBody = document.getElementById('vendorTableBody');
            vendorTableBody.addEventListener('click', function(e) {
                const detailButton = e.target.closest('.rowDetailButton');
                if (detailButton) {
                    e.stopPropagation(); // Prevent the row's click event from firing
                    const vendorName = detailButton.getAttribute('data-vendor');
                    const selectedMonth = document.getElementById('monthFilter')
                        .value; // Get the selected month

                    fetch(
                            `/purchase-orders/vendor-details?vendor=${encodeURIComponent(vendorName)}&month=${encodeURIComponent(selectedMonth)}`
                        )
                        .then(response => response.json())
                        .then(data => {
                            const modalTitle = document.getElementById('vendorPOModalLabel');
                            const modalBody = document.getElementById('vendorPOTableBody');

                            modalTitle.innerHTML =
                                `Purchase Orders for <strong>${vendorName}</strong> (${selectedMonth})`;
                            modalBody.innerHTML = '';

                            if (data.length > 0) {
                                data.forEach(po => {
                                    // Render the status dynamically based on po.status
                                    const statusBadge = getStatusBadge(po.status);
                                    modalBody.innerHTML += `
                                <tr>
                                    <td>${po.po_number}</td>
                                    <td>${po.invoice_date}</td>
                                    <td>IDR ${new Intl.NumberFormat().format(po.total)}</td>
                                    <td>${statusBadge}</td>
                                    <td><a href="/purchaseOrder/${po.id}" class="btn btn-outline-secondary">View</a></td>
                                </tr>`;
                                });
                            } else {
                                modalBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center">No purchase orders found for this vendor in the selected month.</td>
                            </tr>`;
                            }

                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('vendorPOModal'));
                            modal.show();
                        })
                        .catch(error => console.error('Error fetching purchase orders:', error));
                }
            });
        });

        // Helper function to render the status badge
        function getStatusBadge(status) {
            switch (status) {
                case 'Approved':
                    return `<span class="badge bg-success">Approved</span>`;
                case 'Rejected':
                    return `<span class="badge bg-danger">Rejected</span>`;
                default:
                    return `<span class="badge bg-warning">Pending</span>`;
            }
        }

        document.getElementById('checkDetailButton').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthFilter').value;
            document.getElementById('selectedMonthInput').value = selectedMonth;
            document.getElementById('checkDetailForm').submit();
        });
    </script>
@endpush
