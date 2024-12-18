@extends('layouts.app')

@section('content')
    <style>
        .vendor-row {
            cursor: pointer;
        }
    </style>
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
        </div>

        <div class="row">
            <!-- Total Monthly Chart -->
            <div class="col-md-6">
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
            <div class="col-md-3">
                <div class="card">
                    <div class="ps-3 pt-3">
                        <div class="h4">PO by Status Chart</div>
                        <div class="text-secondary">Purchase Order count based on their statuses.</div>
                    </div>
                    <div class="card-body">
                        <canvas id="poStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="ps-3 pt-3">
                        <div class="h4">PO by Category Chart</div>
                        <div class="text-secondary">Purchase Order count based on their categories.</div>
                    </div>
                    <div class="card-body">
                        <canvas id="poCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="topVendorsModal" tabindex="-1" aria-labelledby="topVendorsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="topVendorsModalLabel">Top 5 Vendors with the Highest Total</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="topVendorsList">
                            @forelse ($topVendors as $index => $vendor)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#{{ $index + 1 }}: {{ $vendor->vendor_name }}</strong>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fs-5">IDR
                                        {{ number_format($vendor->total, 2) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center">
                                    No data available for the selected month.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- <!-- Top Vendors -->
        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="p-3">
                        <div class="h4">Top Vendors</div>
                        <div class="text-secondary">Vendors with the highest sum of total based on the selected month.</div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="topVendorList">
                            @forelse ($topVendors as $index => $vendor)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#{{ $index + 1 }}: {{ $vendor->vendor_name }}</strong>
                                    </div>
                                    <span class="badge bg-primary fs-5">IDR {{ number_format($vendor->total, 2) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center">
                                    No data available for the selected month.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="row mt-4 justify-content-end">
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
                <button class="btn btn-outline-primary " id="viewTopVendorsButton">View 5 Top
                    Vendors</button>
            </div>
            <div class="col-auto">
                <form id="checkDetailForm" action="{{ route('po.index') }}" method="GET">
                    <input type="hidden" name="month" id="selectedMonthInput">
                    <button type="button" class="btn btn-primary" id="checkDetailButton">Check Detail</button>
                </form>
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
                                <th>Invoice Date</th>
                                <th>PO Number</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
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
            const categoryChartData = @json($categoryChartData);

            const categoryChartCtx = document.getElementById('poCategoryChart').getContext('2d');
            const categoryData = categoryChartData.map(item => item.count); // Extract counts
            const categoryLabels = categoryChartData.map(item => item.label); // Extract labels

            new Chart(categoryChartCtx, {
                type: 'pie',
                data: {
                    labels: categoryLabels, // Category names
                    datasets: [{
                        data: categoryData, // Purchase order counts
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)', // Green
                            'rgba(255, 205, 86, 0.6)', // Yellow
                            'rgba(255, 99, 132, 0.6)', // Red
                            'rgba(54, 162, 235, 0.6)', // Blue
                            'rgba(153, 102, 255, 0.6)' // Purple (Add more as needed)
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    }
                }
            });

            const statusChartCtx = document.getElementById('poStatusChart').getContext('2d');
            const statusData = @json($statusCounts); // Pass data from backend

            new Chart(statusChartCtx, {
                type: 'pie', // Pie chart for visualizing proportions
                data: {
                    labels: ['Approved', 'Waiting', 'Rejected', 'Canceled'],
                    datasets: [{
                        data: [statusData.approved, statusData.waiting, statusData.rejected,
                            statusData.canceled
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)', // Approved - Green
                            'rgba(255, 205, 86, 0.6)', // Waiting - Yellow
                            'rgba(255, 99, 132, 0.6)', // Rejected - Red
                            'rgba(0, 0, 0, 0.3)' // Canceled - Gray
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)', // Approved - Green
                            'rgba(255, 205, 86, 1)', // Waiting - Yellow
                            'rgba(255, 99, 132, 1)', // Rejected - Red
                            'rgba(0, 0, 0, 0.3)' // Canceled - Gray
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    }
                }
            });

            const monthlyTotalsChartCtx = document.getElementById('monthlyTotalsChart').getContext('2d');
            let monthlyTotalsChart = null;

            // Initialize Chart
            function updateChart(data) {
                if (monthlyTotalsChart) {
                    monthlyTotalsChart.destroy();
                }
                monthlyTotalsChart = new Chart(monthlyTotalsChartCtx, {
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

                        // Store topVendors for later use
                        window.topVendorsData = data.topVendors;

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
                                // Group data by invoice_date
                                const groupedData = data.reduce((acc, po) => {
                                    if (!acc[po.invoice_date]) {
                                        acc[po.invoice_date] = [];
                                    }
                                    acc[po.invoice_date].push(po);
                                    return acc;
                                }, {});

                                // Render grouped rows with rowspan
                                Object.keys(groupedData).forEach((invoiceDate) => {
                                    const rows = groupedData[invoiceDate];
                                    rows.forEach((po, index) => {
                                        modalBody.innerHTML += `
                                        <tr>
                                            ${
                                                index === 0
                                                    ? `<td rowspan="${rows.length}">${invoiceDate}</td>` // Add rowspan to the first row of the group
                                                    : ''
                                            }
                                            <td>${po.po_number}</td>
                                            <td>IDR ${new Intl.NumberFormat().format(po.total)}</td>
                                            <td>${getStatusBadge(po.status)}</td>
                                            <td><a href="/purchaseOrder/${po.id}" class="btn btn-outline-secondary">View</a></td>
                                        </tr>`;
                                    });
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
                case 'Canceled':
                    return `<span class="badge bg-danger-subtle text-danger">Canceled</span>`;
                default:
                    return `<span class="badge bg-warning">Pending</span>`;
            }
        }

        function showTopVendorsModal() {
            const topVendorsList = document.getElementById('topVendorsList');
            topVendorsList.innerHTML = ''; // Clear existing content

            if (window.topVendorsData && window.topVendorsData.length > 0) {
                window.topVendorsData.forEach((vendor, index) => {
                    topVendorsList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>#${index + 1}: ${vendor.vendor_name}</strong>
                    </div>
                    <span class="badge bg-primary-subtle text-primary fs-5">IDR ${new Intl.NumberFormat().format(vendor.total)}</span>
                </li>`;
                });
            } else {
                topVendorsList.innerHTML = `
                <li class="list-group-item text-center">
                    No data available for the selected month.
                </li>`;
            }

            const modal = new bootstrap.Modal(document.getElementById('topVendorsModal'));
            modal.show();
        }

        // Event listener for the button
        document.getElementById('viewTopVendorsButton').addEventListener('click', showTopVendorsModal);

        document.getElementById('checkDetailButton').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthFilter').value;
            document.getElementById('selectedMonthInput').value = selectedMonth;
            document.getElementById('checkDetailForm').submit();
        });
    </script>
@endpush
