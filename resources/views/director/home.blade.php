@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="fw-lighter text-secondary fs-3">QA/QC Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Approved" :content="$reportCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Waiting" :content="$reportCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Rejected" :content="$reportCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="text-secondary fs-3">Purchase Requests</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Approved" :content="$purchaseRequestCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Waiting" :content="$purchaseRequestCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Rejected" :content="$purchaseRequestCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="fw-lighter text-secondary fs-3">Monthly Budget Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Approved" :content="$monthlyBudgetReportsCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Waiting" :content="$monthlyBudgetReportsCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Rejected" :content="$monthlyBudgetReportsCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="text-secondary fs-3">Monthly Budget Summary Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Approved" :content="$monthlyBudgetSummaryReportsCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Waiting" :content="$monthlyBudgetSummaryReportsCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Rejected" :content="$monthlyBudgetSummaryReportsCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <div class="container">
                    <div class="p-4 pb-0">
                        <h4 class="text-secondary fs-3">Purchase Order Reports</h4>
                    </div>
                    <hr>
                    <div class="container p-2 px-5">
                        <div class="row justify-content-center">
                            <div class="col">
                                <a href="{{ route('po.dashboard') }}">
                                    <x-card title="Approved" :content="$poCounts['approved']" color="green" titleColor="text-success"
                                        icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>

                            <div class="col">
                                <a href="{{ route('po.dashboard') }}">
                                    <x-card title="Waiting" :content="$poCounts['waiting']" color="orange" titleColor="text-warning"
                                        icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>

                            <div class="col">
                                <a href="{{ route('po.dashboard') }}">
                                    <x-card title="Rejected" :content="$poCounts['rejected']" color="red" titleColor="text-danger"
                                        contentColor="text-secondary"
                                        icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="container">
            <h1>Employee Chart</h1>

            <!-- Legend Selection -->
            <div class="mb-4">
                <label for="legendFilter" class="form-label">Choose Legend</label>
                <select id="legendFilter" class="form-select">
                    <option value="Status" selected>Status</option>
                    <option value="Dept">Department</option>
                    <option value="Branch">Branch</option>
                </select>
            </div>


            <!-- Branch Filter -->
            <div class="mb-4">
                <label for="branchFilter" class="form-label">Filter by Branch</label>
                <select id="branchFilter" class="form-select">
                    <option value="" selected>All</option>
                </select>
            </div>

            <!-- Dept Filter -->
            <div class="mb-4">
                <label for="deptFilter" class="form-label">Filter by Department</label>
                <select id="deptFilter" class="form-select">
                    <option value="" selected>All</option>
                </select>
            </div>

            <!-- Employee Status Filter -->
            <div class="mb-4">
                <label for="statusFilter" class="form-label">Filter by Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="" selected>All</option>
                </select>
            </div>

            <div class="col-md-4 text-center">
                <!-- Pie Chart -->
                <canvas id="employeeChart" width="400" height="200"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartData = @json($chartData);

            const branchFilter = document.getElementById('branchFilter');
            const deptFilter = document.getElementById('deptFilter');
            const statusFilter = document.getElementById('statusFilter');
            const legendFilter = document.getElementById('legendFilter'); // New Legend Selector
            const ctx = document.getElementById('employeeChart').getContext('2d');

            // Initialize empty pie chart
            let employeeChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    const value = tooltipItem.raw;
                                    const total = tooltipItem.dataset.data.reduce((sum, val) => sum + val, 0);
                                    const percentage = ((value / total) * 100).toFixed(2);
                                    return `${tooltipItem.label}: ${percentage}%`;
                                },
                            },
                        },
                    },
                },
            });

            // Function to filter data and group by selected legend
            function getFilteredData() {
                const selectedBranch = branchFilter.value;
                const selectedDept = deptFilter.value;
                const selectedStatus = statusFilter.value;
                const selectedLegend = legendFilter.value;

                // Filter the data
                const filteredData = chartData.filter(item => {
                    return (!selectedBranch || item.Branch === selectedBranch) &&
                        (!selectedDept || item.Dept === selectedDept) &&
                        (!selectedStatus || item.Status === selectedStatus);
                });

                // Group the filtered data by the selected legend
                const groupedData = filteredData.reduce((acc, item) => {
                    const key = item[selectedLegend];
                    acc[key] = (acc[key] || 0) + 1;
                    return acc;
                }, {});

                return groupedData;
            }

            // Function to update the chart
            function updateChart() {
                const filteredData = getFilteredData();

                const labels = Object.keys(filteredData);
                const data = Object.values(filteredData);

                employeeChart.data.labels = labels;
                employeeChart.data.datasets[0].data = data;
                employeeChart.data.datasets[0].backgroundColor = labels.map(() =>
                    `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`);

                employeeChart.update();
            }

            function updateDropdowns() {
                const selectedLegend = legendFilter.value;
                const selectedBranch = branchFilter.value;
                const selectedDept = deptFilter.value;
                const selectedStatus = statusFilter.value;

                // Hide or disable the dropdown corresponding to the selected legend
                if (selectedLegend === "Branch") {
                    branchFilter.parentElement.style.display = "none"; // Hide the Branch filter
                    deptFilter.parentElement.style.display = "block"; // Show Dept filter
                    statusFilter.parentElement.style.display = "block"; // Show Status filter
                } else if (selectedLegend === "Dept") {
                    branchFilter.parentElement.style.display = "block"; // Show Branch filter
                    deptFilter.parentElement.style.display = "none"; // Hide the Dept filter
                    statusFilter.parentElement.style.display = "block"; // Show Status filter
                } else if (selectedLegend === "Status") {
                    branchFilter.parentElement.style.display = "block"; // Show Branch filter
                    deptFilter.parentElement.style.display = "block"; // Show Dept filter
                    statusFilter.parentElement.style.display = "none"; // Hide the Status filter
                }

                // Populate Branch dropdown dynamically
                const branchOptions = [...new Set(chartData
                    .filter(item => (!selectedDept || item.Dept === selectedDept) &&
                        (!selectedStatus || item.Status === selectedStatus))
                    .map(item => item.Branch))];
                branchFilter.innerHTML = '<option value="" selected>All</option>';
                branchOptions.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch;
                    option.textContent = branch;
                    branchFilter.appendChild(option);
                });
                branchFilter.value = selectedBranch && branchOptions.includes(selectedBranch) ? selectedBranch : "";

                // Populate Dept dropdown dynamically
                const deptOptions = [...new Set(chartData
                    .filter(item => (!selectedBranch || item.Branch === selectedBranch) &&
                        (!selectedStatus || item.Status === selectedStatus))
                    .map(item => item.Dept))];
                deptFilter.innerHTML = '<option value="" selected>All</option>';
                deptOptions.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    deptFilter.appendChild(option);
                });
                deptFilter.value = selectedDept && deptOptions.includes(selectedDept) ? selectedDept : "";

                // Populate Status dropdown dynamically
                const statusOptions = [...new Set(chartData
                    .filter(item => (!selectedBranch || item.Branch === selectedBranch) &&
                        (!selectedDept || item.Dept === selectedDept))
                    .map(item => item.Status))];
                statusFilter.innerHTML = '<option value="" selected>All</option>';
                statusOptions.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    statusFilter.appendChild(option);
                });
                statusFilter.value = selectedStatus && statusOptions.includes(selectedStatus) ? selectedStatus : "";
            }

            // Event listeners for dropdowns
            legendFilter.addEventListener('change', () => {
                updateDropdowns(); // Adjust dropdowns based on new legend
                updateChart();
            });

            branchFilter.addEventListener('change', () => {
                updateDropdowns();
                updateChart();
            });

            deptFilter.addEventListener('change', () => {
                updateDropdowns();
                updateChart();
            });

            statusFilter.addEventListener('change', () => {
                updateDropdowns();
                updateChart();
            });

            // Initial load
            updateDropdowns();
            updateChart();
        </script>

    </div>
@endsection
