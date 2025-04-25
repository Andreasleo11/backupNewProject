@extends('layouts.app')

@section('content')
    <style>
        .vendor-row {
            cursor: pointer;
        }
    </style>
    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-auto">
                <div class="d-flex align-items-center gap-3">
                    <h1 class="text-nowrap">Purchase Order Dashboard</h1>
                    <select class="form-select" id="yearFilter">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
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

        <div class="row mt-4 justify-content-end">
            <div class="col-auto">
                <!-- Month Filter -->
                <select id="monthFilter" class="form-select d-inline-block w-auto">
                    @foreach ($availableMonths as $month)
                        <option value="{{ $month['number'] }}" {{ $selectedMonth === $month['number'] ? 'selected' : '' }}>
                            {{ $month['name'] }}
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
                    <button type="button" class="btn btn-primary" id="checkDetailButton">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                            <path d="M3 2h2v20H3zm7 4h7v2h-7zm0 4h7v2h-7z"></path>
                            <path d="M19 2H6v20h13c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2zm0 18H8V4h11v16z"></path>
                        </svg>
                        PO List
                    </button>
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
                                    <th>#</th>
                                    <th>Vendor Name</th>
                                    <th>Total</th>
                                    <th>PO Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="vendorTableBody">
                                @if ($vendorTotals->isEmpty())
                                    <tr>
                                        <td colspan="20" class="text-center">No data available for the selected month.
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="20" class="text-center">Loading</td>
                                    </tr>
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
                                <th>Tanggal Pembayaran</th>
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
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            const categoryChartData = @json($categoryChartData);

            const categoryChartCtx = document.getElementById('poCategoryChart').getContext('2d');
            var categoryData = categoryChartData.map(item => item.count); // Extract counts
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
            var statusData = @json($statusCounts); // Pass data from backend

            function getStatusChart() {

            }
            new Chart(statusChartCtx, {
                type: 'pie', // Pie chart for visualizing proportions
                data: {
                    labels: ['Approved', 'Waiting', 'Rejected', 'Cancelled', 'Open', 'Closed'],
                    datasets: [{
                        data: [statusData.approved, statusData.waiting, statusData.rejected,
                            statusData.cancelled, statusData.open, statusData.closed
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)', // Approved - Green
                            'rgba(255, 205, 86, 0.6)', // Waiting - Yellow
                            'rgba(255, 99, 132, 0.6)', // Rejected - Red
                            'rgba(0, 0, 0, 0.3)', // Canceled - Gray
                            'rgba(54, 162, 235, 0.6)', // Open - Blue
                            'rgba(153, 102, 255, 0.6)' // Closed - Purple
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)', // Approved - Green
                            'rgba(255, 205, 86, 1)', // Waiting - Yellow
                            'rgba(255, 99, 132, 1)', // Rejected - Red
                            'rgba(0, 0, 0, 0.3)', // Canceled - Gray
                            'rgba(54, 162, 235, 1)', // Open - Blue
                            'rgba(153, 102, 255, 1)' // Closed - Purple
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
            function loadData(selectedMonth, selectedYear) {
                fetch(`/purchase-orders/filter?month=${selectedMonth}&year=${selectedYear}`)
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
                                data.vendorTotals.forEach((vendor, index) => {
                                    tableBody.innerHTML += `
                                    <tr class="vendor-row" data-vendor="${vendor.vendor_name}">
                                        <td>${index + 1}</td>
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
                                        <td colspan="20" class="text-center">No data available for the selected month-year.</td>
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
            const initialYear = document.getElementById('yearFilter').value;
            loadData(initialMonth, initialYear);


            document.getElementById('monthFilter').addEventListener('change', function() {
                const selectedYear = document.getElementById('yearFilter').value;
                categoryData = categoryChartData.map(item => item.count);
                statusData = @json($statusCounts);
                loadData(this.value, selectedYear);
            });

            document.getElementById('yearFilter').addEventListener('change', function() {
                const selectedMonth = document.getElementById('monthFilter').value;
                categoryData = categoryChartData.map(item => item.count);
                statusData = @json($statusCounts);
                loadData(selectedMonth, this.value);
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
                    const selectedYear = document.getElementById('yearFilter')
                        .value; // Get the selected year

                    fetch(
                            `/purchase-orders/vendor-details?vendor=${encodeURIComponent(vendorName)}&month=${encodeURIComponent(selectedMonth)}&year=${encodeURIComponent(selectedYear)}`
                        )
                        .then(response => response.json())
                        .then(data => {
                            const modalTitle = document.getElementById('vendorPOModalLabel');
                            const modalBody = document.getElementById('vendorPOTableBody');

                            modalTitle.innerHTML =
                                `Purchase Orders for <strong>${vendorName}</strong> (${selectedMonth}-${selectedYear})`;
                            modalBody.innerHTML = '';

                            if (data.length > 0) {
                                // Group data by tanggal_pembayaran
                                const groupedData = data.reduce((acc, po) => {
                                    if (!acc[po.tanggal_pembayaran]) {
                                        acc[po.tanggal_pembayaran] = [];
                                    }
                                    acc[po.tanggal_pembayaran].push(po);
                                    return acc;
                                }, {});

                                // Render grouped rows with rowspan
                                Object.keys(groupedData).forEach((tanggalPembayaran) => {
                                    const rows = groupedData[tanggalPembayaran];
                                    rows.forEach((po, index) => {
                                        modalBody.innerHTML += `
                                        <tr>
                                            ${
                                                index === 0
                                                    ? `<td rowspan="${rows.length}">${tanggalPembayaran}</td>` // Add rowspan to the first row of the group
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
                                    <td colspan="5" class="text-center">No purchase orders found for this vendor in the selected month-year.</td>
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
                case 'approved':
                    return `<span class="badge text-bg-success">Approved</span>`;
                    break;
                case 'rejected':
                    return `<span class="badge text-bg-danger">Rejected</span>`;
                    break;
                case 'cancelled':
                    return `<span class="badge bg-danger-subtle text-danger">Canceled</span>`;
                    break;
                case 'open':
                    return `<span class="badge bg-primary-subtle text-primary">Open</span>`;
                    break;
                case 'closed':
                    return `<span class="badge bg-secondary">Closed</span>`;
                    break;
                case 'waiting':
                    return `<span class="badge bg-danger-subtle text-danger">Waiting Approval</span>`;
                    break;
                default:
                    return `<span class="badge bg-dark-subtle text-dark">Unknown</span>`;
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
            const selectedYear = document.getElementById('yearFilter').value;
            document.getElementById('selectedMonthInput').value = selectedMonth;
            document.getElementById('checkDetailForm').submit();
        });
    </script>
@endpush
