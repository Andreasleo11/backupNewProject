<div>
    <div class="row">
        <div class="container-fluid">
            <div class="row">
                <div class="d-flex align-items-center">
                    <h1 class="fs-1">Employee Dashboard
                        {{ $authUser && $authUser->department->name !== 'MANAGEMENT' ? ucwords(strtolower($authUser->department->name)) : '' }}
                    </h1>
                    <div class="ms-3">
                        <button id="updateButton" type="submit" class="btn btn-primary" onclick="startSyncProgress()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                style="fill: rgba(255, 255, 255, 1);">
                                <path
                                    d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z">
                                </path>
                            </svg>
                            <span id="buttonText">Update</span>
                        </button>
                    </div>
                    <div class="ms-auto">
                        <h5 class="text-secondary fw-bold">
                            <span id="currentDateTime"></span>
                        </h5>
                    </div>
                </div>
                <text class="text-secondary">Last updated at <strong>{{ $latestUpdatedAt ?? 'No Data' }}</strong></text>
                <div class="col">
                    <div class="alert alert-warning d-flex align-items-center" id="riskAlert" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <span id="riskText"></span>
                    </div>

                    <div class="row mb-4">
                        <div class="col">
                            <div class="card mt-4">
                                <button class="btn btn-light text-start" data-bs-toggle="modal"
                                    data-bs-target="#departmentEmployeeModal">
                                    <div class="card-body">
                                        <p class="card-text text-secondary fs-5">Total Employees
                                        </p>
                                        <span class="fw-bold badge text-bg-dark fs-4"
                                            id="totalEmployees">{{ $employeeData['total'] }}</span>
                                    </div>
                                </button>

                                <!-- Employee Count by Department Modal -->
                                <div class="modal fade" id="departmentEmployeeModal" tabindex="-1"
                                    aria-labelledby="departmentEmployeeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title" id="departmentEmployeeModalLabel">Employee Count
                                                    by Department</h3>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="d-flex mb-3 justify-content-between align-items-center">
                                                    <div>
                                                        <h4>Summary</h4>
                                                    </div>
                                                    <div>
                                                        <button type="submit" class="btn btn-outline-primary"
                                                            data-bs-target="#allEmployeesModal"
                                                            data-bs-toggle="modal">Employee List ></button>
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Department</th>
                                                                @php
                                                                    // Extract unique statuses dynamically
                                                                    $uniqueStatuses = collect($departmentEmployeeCounts)
                                                                        ->flatMap(
                                                                            fn($dept) => array_keys(
                                                                                $dept['breakdown']->toArray(),
                                                                            ),
                                                                        )
                                                                        ->unique();

                                                                    // Initialize totals array
                                                                    $statusTotals = array_fill_keys(
                                                                        $uniqueStatuses->toArray(),
                                                                        0,
                                                                    );
                                                                    $grandTotal = 0;
                                                                @endphp

                                                                @foreach ($uniqueStatuses as $status)
                                                                    <th>{{ $status }}</th>
                                                                @endforeach
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($departmentEmployeeCounts as $department => $counts)
                                                                <tr>
                                                                    <td>{{ $counts['label'] }}</td>
                                                                    @foreach ($uniqueStatuses as $status)
                                                                        @php
                                                                            $countValue = $counts['breakdown']->get(
                                                                                $status,
                                                                                0,
                                                                            );
                                                                            $statusTotals[$status] += $countValue; // Sum each column
                                                                        @endphp
                                                                        <td>{{ $countValue }}</td>
                                                                    @endforeach
                                                                    @php $grandTotal += $counts['total_count']; @endphp
                                                                    <td><strong>{{ $counts['total_count'] }}</strong>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-secondary fw-bold">
                                                                <td>Total</td>
                                                                @foreach ($uniqueStatuses as $status)
                                                                    <td>{{ $statusTotals[$status] }}</td>
                                                                @endforeach
                                                                <td>{{ $grandTotal }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- All Employees Modal -->
                                <div class="modal fade" id="allEmployeesModal" tabindex="-1"
                                    aria-labelledby="allEmployeesModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title" id="allEmployeesModalLabel">All Employees
                                                    (Employee List)</h3>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    {!! $dataTableEmployee->table(['id' => 'employee-table']) !!}
                                                    {!! $dataTableEmployee->scripts() !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card mt-4 p-2">
                                <div class="card-body">
                                    <p class="card-text text-secondary fs-5">Dominant</p>
                                    <span class="fw-bold badge text-bg-secondary fs-4" id="dominantCategory"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="fw-semibold text-secondary fs-5 mb-3">Filters</h4>
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
                                        </select>
                                    </div>

                                    <!-- Employee Status Filter -->
                                    <div class="mb-4">
                                        <label for="statusFilter" class="form-label">Filter by Status</label>
                                        <select id="statusFilter" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="genderFilter" class="form-label">Filter by Gender</label>
                                        <select id="genderFilter" class="form-select">
                                            <option value="" selected>All</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <div class="d-flex flex-wrap">
                                    <h6 class="text-secondary font-semibold me-2 my-1 p-1">Active Filters:</h6>
                                    <span class="badge bg-secondary-subtle text-secondary p-2 me-2 my-1">Legend: <span
                                            id="currentLegendFilter">Status</span></span>
                                    <span class="badge bg-secondary-subtle text-secondary p-2 me-2 my-1">Branch: <span
                                            id="currentBranchFilter">All</span></span>
                                    <span class="badge bg-secondary-subtle text-secondary p-2 me-2 my-1">Dept: <span
                                            id="currentDeptFilter">All</span></span>
                                    <span class="badge bg-secondary-subtle text-secondary p-2 me-2 my-1">Status: <span
                                            id="currentStatusFilter">All</span></span>
                                    <span class="badge bg-secondary-subtle text-secondary p-2 me-2 my-1">Gender: <span
                                            id="currentGenderFilter">All</span></span>
                                </div>
                            </div>
                            <!-- Pie Chart -->
                            <div>
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mt-3">
                    <div class="row">
                        <div class="col">
                            <label for="monthYearFilter" class="form-label">Select Month</label>
                            <select id="monthYearFilter" name="monthYear" class="form-select">
                                <option value="" selected>All</option>
                                @foreach ($monthYearOptions as $option)
                                    <option value="{{ $option['value'] }}">
                                        {{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label for="weekFilter" class="form-label">Select Week</label>
                            <input type="week" id="weekFilter" class="form-control" value="{{ $latestWeek }}">
                        </div>

                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <!-- Display selected week range -->
                            <div class="mt-3">
                                <p id="weekRange" class="fw-bold text-secondary text-center fs-3"></p>
                            </div>

                            <!-- Employee Category Cards -->
                            <div class="row">
                                @foreach (['Alpha' => 'danger', 'Telat' => 'warning', 'Izin' => 'primary', 'Sakit' => 'success'] as $category => $color)
                                    <div class="col col-md-6 col-xl-3">
                                        <div class="card mt-2" data-category="{{ $category }}">
                                            <button class="btn btn-light open-category-modal"
                                                data-category="{{ $category }}" data-bs-toggle="modal"
                                                data-bs-target="#employeeByCategoryModal">
                                                <div class="card-body text-start">
                                                    <span
                                                        class="card-text text-secondary fs-4">{{ $category }}</span>
                                                    <br>
                                                    <span class="fw-bold badge text-bg-{{ $color }} fs-3"
                                                        id="{{ strtolower($category) }}">{{ $employeeData[strtolower($category)] }}</span>
                                                    <br>
                                                    <span class="text-secondary">employees</span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Employee By Category Modal -->
                            <div class="modal fade" id="employeeByCategoryModal" tabindex="-1"
                                aria-labelledby="employeeByCategoryModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title" id="employeeByCategoryModalLabel">Employee List
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h5 class="text-secondary" id="modalCategoryTitle"></h5>
                                            <p class="fw-bold" id="modalSubtitle"></p>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>No.</th>
                                                            <th>NIK</th>
                                                            <th>Name</th>
                                                            <th>Gender</th>
                                                            <th>Department</th>
                                                            <th>Status</th>
                                                            <th id="categoryCountTitle"></th>
                                                            <!-- Dynamic Category Column -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="employeeByCategoryList">
                                                        <!-- Employee data will be inserted here dynamically -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <!-- Bar Chart -->
                                <div class="col-12">
                                    <canvas id="weeklyEvaluationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <hr>
                    </div>
                </div>
                <div class="text-secondary fw-bold text-center fs-3">Employee Count per Department</div>
                <div class="row justify-content-center">
                    <div class="col-md-4 ">
                        <hr>
                    </div>
                </div>
                <!-- Add a Toggle Button -->
                <button id="toggleChartView" class="btn btn-primary mt-2">Show Detailed Breakdown</button>
                <canvas class="mt-3" id="departmentEmployeeChart"></canvas>
            </div>
        </div>

        <!-- Employee By Department Modal -->
        <div class="modal fade" id="employeeByDepartmentModal" tabindex="-1"
            aria-labelledby="employeeByDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="employeeByDepartmentModalLabel">Employee List</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="text-secondary" id="modalDepartmentTitle"></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>NIK</th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeByDepartmentList">
                                    <!-- Employee data will be inserted here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee List Modal -->
        <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr id="tableHead">
                                        <th>No.</th>
                                        <th>NIK</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeList">
                                    <!-- Employee data will be inserted here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Week range script --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const weekFilter = document.getElementById("weekFilter");
        const weekRange = document.getElementById("weekRange");

        function getWeekRange(weekInputValue) {
            const monthFilter = document.getElementById('monthYearFilter');
            const monthFilterValue = monthFilter ? monthFilter.value : null;

            if (!weekInputValue) {
                if (!monthFilterValue) {
                    return "No month year and week selected";
                } else {
                    return monthFilter.innerText;
                }
            }

            const [year, week] = weekInputValue.split("-W").map(Number);

            // Create a date set to the beginning of the ISO week
            const simple = new Date(year, 0, 1 + (week - 1) * 7);

            // Get the ISO week day (0 is Sunday, 1 is Monday, ..., 6 is Saturday)
            const dayOfWeek = simple.getDay();
            const ISOWeekStart = new Date(simple);

            // Adjust to the previous Monday if not already Monday
            const diff = (dayOfWeek <= 0 ? -6 : 1) - dayOfWeek;
            ISOWeekStart.setDate(simple.getDate() + diff);

            const ISOWeekEnd = new Date(ISOWeekStart);
            ISOWeekEnd.setDate(ISOWeekStart.getDate() + 6);

            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return `${ISOWeekStart.toLocaleDateString(undefined, options)} - ${ISOWeekEnd.toLocaleDateString(undefined, options)}`;
        }

        function updateWeekRange() {
            weekRange.textContent = getWeekRange(weekFilter.value);
        }

        // Update when input changes
        weekFilter.addEventListener("change", updateWeekRange);

        // Set initial value
        updateWeekRange();
    });

    // sync employee dashboard from api
    let syncInterval = null;

    function startSyncProgress() {
        // Update button text
        document.getElementById('buttonText').innerText = 'Updating... 0%';

        // Dispatch the job via POST (like submitting the form)
        fetch('{{ route('employee.dashboard.updateEmployeeData') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to start sync');
                pollSyncProgress(); // Start polling only if dispatch succeeded
            })
            .catch(err => {
                document.getElementById('buttonText').innerText = 'Update Failed';
                console.error(err);
            });
    }

    function pollSyncProgress() {
        syncInterval = setInterval(() => {
            fetch('/sync-progress/10000')
                .then(res => res.json())
                .then(data => {
                    const progress = data.progress;
                    document.getElementById('buttonText').innerText = `Updating... ${progress}%`;

                    if (progress >= 100) {
                        clearInterval(syncInterval);
                        location.reload();
                    }
                });
        }, 500);
    }

    function updateCurrentDateTime() {
        const now = new Date();

        const formatted = now.toLocaleString('en', {
            year: 'numeric',
            month: 'long', // e.g. April
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });

        document.getElementById('currentDateTime').textContent = formatted;
    }

    // Show immediately
    updateCurrentDateTime();

    // Update every second
    setInterval(updateCurrentDateTime, 1000);
</script>

{{-- Weekly bar chart script  --}}
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        const weekFilter = document.getElementById('weekFilter');
        const ctx = document.getElementById('weeklyEvaluationChart').getContext('2d');

        let weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                        label: 'Alpha',
                        data: [],
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    },
                    {
                        label: 'Telat',
                        data: [],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    },
                    {
                        label: 'Izin',
                        data: [],
                        backgroundColor: 'rgba(255, 206, 86, 0.6)',
                    },
                    {
                        label: 'Sakit',
                        data: [],
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    },
                    {
                        label: 'Total',
                        data: [],
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        type: 'line',
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
                onClick: function(event, elements) {
                    if (elements.length > 0) {
                        let clickedIndex = elements[0].index; // Get the department index
                        let datasetIndex = elements[0]
                            .datasetIndex; // Get which dataset (Alpha, Telat, etc.)
                        let department = weeklyChart.data.labels[
                            clickedIndex]; // Get department name
                        let category = weeklyChart.data.datasets[datasetIndex]
                            .label; // Get category (Alpha, Telat, etc.)

                        // Extract Year and Week Number from 'YYYY-WWW' format
                        const [year, week] = weekFilter.value.split('-W');

                        fetchEmployeeList(department, category, year, week);
                    }
                },
            },
        });

        function fetchWeeklyEvaluation(weekValue) {
            if (!weekValue) {
                console.error("Week value is missing");
                return;
            }

            // Extract Year and Week Number
            const [year, week] = weekValue.split('-W');
            const branch = document.getElementById('branchFilter').value || '';
            const department = document.getElementById('deptFilter').value || '';
            const status = document.getElementById('statusFilter').value || '';
            const gender = document.getElementById('genderFilter').value || '';

            let url = `{{ route('getWeeklyEvaluationData', ['year' => '__YEAR__', 'week' => '__WEEK__']) }}`;
            url = url.replace('__YEAR__', year).replace('__WEEK__', week);

            // Append filters as query parameters
            url +=
                `?branch=${encodeURIComponent(branch)}&department=${encodeURIComponent(department)}&status=${encodeURIComponent(status)}&gender=${encodeURIComponent(gender)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const labels = Object.keys(data);
                    const alphaData = labels.map(dept => data[dept]?.breakdown?.Alpha || 0);
                    const telatData = labels.map(dept => data[dept]?.breakdown?.Telat || 0);
                    const izinData = labels.map(dept => data[dept]?.breakdown?.Izin || 0);
                    const sakitData = labels.map(dept => data[dept]?.breakdown?.Sakit || 0);
                    const totalData = labels.map(dept => data[dept]?.total_count ||
                        0); // Total distinct NIK

                    // Update chart data
                    weeklyChart.data.labels = labels;
                    weeklyChart.data.datasets[0].data = alphaData;
                    weeklyChart.data.datasets[1].data = telatData;
                    weeklyChart.data.datasets[2].data = izinData;
                    weeklyChart.data.datasets[3].data = sakitData;
                    weeklyChart.data.datasets[4].data = totalData; // Update Total Count dataset

                    weeklyChart.update();
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        // Load current week's data
        fetchWeeklyEvaluation(weekFilter.value);

        // Update chart when week changes
        weekFilter.addEventListener('change', function() {
            fetchWeeklyEvaluation(this.value);
        });

        // Function to fetch and show employees in a modal
        function fetchEmployeeList(department, category, year, week) {
            let url =
                `{{ route('getEmployeesByCategoryAndWeek', ['department' => '__DEPT__', 'category' => '__CAT__', 'year' => '__YEAR__', 'week' => '__WEEK__']) }}`;
            url = url.replace('__DEPT__', encodeURIComponent(department))
                .replace('__CAT__', encodeURIComponent(category))
                .replace('__YEAR__', year)
                .replace('__WEEK__', week);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.getElementById("employeeList");
                    let tableHead = document.getElementById("tableHead");
                    tableBody.innerHTML = "";

                    // Extract employees and total count
                    const employees = data.employees || [];
                    const totalCategory = data.total_selected_category ?? null;

                    // Reset table head
                    tableHead.innerHTML = `
                    <th>No.</th>
                    <th>NIK</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                `;

                    // If a category is selected, add an extra column for it
                    let showCategoryColumn = category !== "total" && employees.some(emp => emp
                        .category_total !== undefined);
                    if (showCategoryColumn) {
                        tableHead.innerHTML +=
                            `<th>${category.charAt(0).toUpperCase() + category.slice(1)}</th>`;
                    }

                    if (employees.length > 0) {
                        employees.forEach((emp, index) => {
                            let row = `<tr>
                            <td>${index + 1}</td>
                            <td>${emp.NIK}</td>
                            <td>${emp.Nama}</td>
                            <td>${emp.department_name}</td>
                            <td>${emp.employee_status}</td>
                            ${showCategoryColumn ? `<td>${emp.category_total}</td>` : ""}
                        </tr>`;
                            tableBody.innerHTML += row;
                        });

                        // Append total row if a specific category is selected
                        if (showCategoryColumn && totalCategory !== null) {
                            let totalRow = `<tr class="fw-bold">
                            <td colspan="5" class="text-end">Total ${category}:</td>
                            <td>${totalCategory}</td>
                        </tr>`;
                            tableBody.innerHTML += totalRow;
                        }
                    } else {
                        tableBody.innerHTML =
                            `<tr><td colspan="${showCategoryColumn ? 6 : 5}" class="text-center">No employees found</td></tr>`;
                    }

                    // Update modal title dynamically
                    document.getElementById("modalTitle").innerText =
                        category === "total" ?
                        `Total Employees in ${department}` :
                        `Employees in ${department} - ${category}`;

                    // Show modal
                    let employeeModal = new bootstrap.Modal(document.getElementById("employeeModal"));
                    employeeModal.show();
                })
                .catch(error => console.error("Error fetching employee data:", error));
        }

    });
</script>

{{-- Employee List Modal Script --}}
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('employeeByCategoryModal'));

        document.querySelectorAll('.open-category-modal').forEach(button => {
            button.addEventListener('click', function() {
                let category = this.getAttribute('data-category');
                let monthYear = document.getElementById('monthYearFilter').value;
                let branch = document.getElementById('branchFilter').value;
                let department = document.getElementById('deptFilter').value;
                let status = document.getElementById('statusFilter').value;
                let gender = document.getElementById('genderFilter').value;
                let week = document.getElementById('weekFilter').value

                document.getElementById('modalCategoryTitle').innerText =
                    `${category} category`;
                document.getElementById('categoryCountTitle').innerText = category;

                // Create an array of selected filters, excluding empty values
                let filters = [monthYear, branch, department, status, gender, week].filter(
                    value =>
                    value.trim() !== "");

                // Set the subtitle with only active filters, joined by commas
                document.getElementById('modalSubtitle').innerText = filters.join(', ');

                fetch("{{ route('getEmployeesByCategory') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({
                            category: category,
                            monthYear: monthYear,
                            branch: branch,
                            department: department,
                            status: status,
                            gender: gender,
                            week: week,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        let tableBody = document.getElementById("employeeByCategoryList");
                        tableBody.innerHTML = ""; // Clear previous data

                        let totalCount = 0; // Initialize total count variable

                        // console.log(data);
                        if (data.length > 0) {
                            data.forEach((emp, index) => {
                                console.log(emp);
                                totalCount += parseInt(emp.category_count) ||
                                    0; // Sum up category count

                                let row = `<tr>
                                            <td>${index + 1}</td> <!-- Row Number -->
                                            <td>${emp.NIK}</td>
                                            <td>${emp.Nama}</td>
                                            <td>${emp.Gender}</td>
                                            <td>${emp.department_name}</td>
                                            <td>${emp.employee_status}</td>
                                            <td>${emp.category_count}</td> <!-- Show category count -->
                                        </tr>`;
                                tableBody.innerHTML += row;
                            });

                            // Append total count row
                            let totalRow = `<tr class="fw-bold">
                                                <td colspan="6" class="text-end">Total ${category}:</td>
                                                <td>${totalCount}</td>
                                            </tr>`;
                            tableBody.innerHTML += totalRow;
                        } else {
                            tableBody.innerHTML =
                                `<tr><td colspan="7" class="text-center">No employees found</td></tr>`;
                        }

                        modal.show();
                    })
                    .catch(error => console.error("Error fetching data:", error));
            });
        });
    });
</script>


{{-- Department Employee Chart Scipt --}}
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        const barData = {!! json_encode($departmentEmployeeCounts) !!}; // Pass data from Laravel

        // Convert object to an array
        const barDataArray = Object.values(barData);

        // Extract unique employee statuses (keys from "breakdown")
        const allStatuses = [...new Set(barDataArray.flatMap(item => Object.keys(item.breakdown)))];

        // Extract department names
        const labels = barDataArray.map(item => item.label);

        let showDetailedChart = false; // Flag to track the chart mode
        let chartInstance = null;

        // Function to generate the chart
        function generateChart(showDetailed) {
            // Destroy existing chart if it exists
            chartInstance?.destroy();

            // Create datasets dynamically based on the mode
            const datasets = showDetailed ?
                allStatuses.map(status => ({
                    label: status,
                    data: barDataArray.map(item => item.breakdown[status] || 0),
                    backgroundColor: getRandomColor(),
                    borderColor: 'rgba(0, 0, 0, 0.8)',
                    borderWidth: 1
                })) : [{
                    label: 'Total Employees',
                    data: barDataArray.map(item => item.total_count),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }];

            // Chart.js instance
            const ctx = document.getElementById('departmentEmployeeChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            let clickedIndex = elements[0].index; // Get department index
                            let datasetIndex = elements[0].datasetIndex; // Get clicked status index
                            let department = labels[clickedIndex]; // Get department name
                            let status = datasets[datasetIndex].label; // Get status name

                            // If "Total Employees" bar is clicked, show all employees in the department
                            if (status === "Total Employees") {
                                status = null; // No status filtering
                            }

                            // Update modal title
                            let modalTitle = `Employees in ${department}`;
                            if (status) {
                                modalTitle += ` (${status})`;
                            }
                            document.getElementById("modalDepartmentTitle").innerText = modalTitle;

                            // Fetch employees and show modal
                            fetchEmployeeByDepartmentData(department, status);
                        }
                    }
                }
            });
        }

        // Generate the initial detailed chart
        generateChart(showDetailedChart);

        // Add event listener to toggle chart view
        document.getElementById("toggleChartView").addEventListener("click", function() {
            showDetailedChart = !showDetailedChart; // Toggle flag
            generateChart(showDetailedChart); // Regenerate chart

            // Update button text
            this.innerText = showDetailedChart ? "Show Total Employees Only" :
                "Show Detailed Breakdown";
        });

        // Function to fetch employees by department
        function fetchEmployeeByDepartmentData(department, status) {
            fetch("{{ route('getEmployeesByDepartment') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                            "content")
                    },
                    body: JSON.stringify({
                        department: department,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.getElementById("employeeByDepartmentList");
                    tableBody.innerHTML = ""; // Clear previous data

                    if (data.length > 0) {
                        data.forEach((emp, index) => {
                            let row = `<tr>
                        <td>${index + 1}</td> <!-- Row Number -->
                        <td>${emp.NIK}</td>
                        <td>${emp.Nama}</td>
                        <td>${emp.Branch}</td>
                        <td>${emp.employee_status}</td>
                    </tr>`;
                            tableBody.innerHTML += row;
                        });
                    } else {
                        tableBody.innerHTML =
                            `<tr><td colspan="4" class="text-center">No employees found</td></tr>`;
                    }

                    // Show the modal
                    let employeeModal = new bootstrap.Modal(document.getElementById(
                        "employeeByDepartmentModal"));
                    employeeModal.show();
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        // Function to generate random colors for datasets
        function getRandomColor() {
            return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.6)`;
        }
    });
</script>

{{-- month year filter script --}}
<script type="module">
    // Fetch employee data on page load
    fetchEmployeeData();

    // Attach event listener for all filters
    $('#monthYearFilter, #weekFilter, #branchFilter, #deptFilter, #statusFilter, #genderFilter')
        .on('change', function() {
            fetchEmployeeData();
        });

    function fetchEmployeeData() {
        let monthYear = $('#monthYearFilter').val();
        let week = $('#weekFilter').val(); // New Week Filter
        let branch = $('#branchFilter').val();
        let department = $('#deptFilter').val();
        let status = $('#statusFilter').val();
        let gender = $('#genderFilter').val();

        fetch("{{ route('filter.employees') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({
                    monthYear: monthYear,
                    week: week,
                    branch: branch,
                    department: department,
                    status: status,
                    gender: gender,
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("alpha").innerText = data.alpha;
                document.getElementById("telat").innerText = data.telat;
                document.getElementById("izin").innerText = data.izin;
                document.getElementById("sakit").innerText = data.sakit;
            })
            .catch(error => console.error("Error fetching data:", error));
    }
</script>

{{-- pie chart employee filter script --}}
<script type="module">
    const chartData = @json($chartData);
    const tableData = @json($employees);

    const branchFilter = document.getElementById('branchFilter');
    const deptFilter = document.getElementById('deptFilter');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const legendFilter = document.getElementById('legendFilter'); // Optional if legend selection is added
    const ctx = document.getElementById('pieChart').getContext('2d');
    const employeeTableBody = document.getElementById('employeewithevaluation-table').querySelector('tbody');

    // Call updateActiveFilters whenever a filter changes
    branchFilter.addEventListener('change', updateActiveFilters);
    deptFilter.addEventListener('change', updateActiveFilters);
    statusFilter.addEventListener('change', updateActiveFilters);
    genderFilter.addEventListener('change', updateActiveFilters);
    legendFilter.addEventListener('change', updateActiveFilters);

    // Initial call to set the active filters on page load
    updateActiveFilters();

    function updateActiveFilters() {
        document.getElementById('currentLegendFilter').textContent = legendFilter.value;
        document.getElementById('currentBranchFilter').textContent = branchFilter.value || 'All';
        document.getElementById('currentDeptFilter').textContent = deptFilter.value || 'All';
        document.getElementById('currentStatusFilter').textContent = statusFilter.value || 'All';
        document.getElementById('currentGenderFilter').textContent = genderFilter.value || 'All';
    }

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
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const value = tooltipItem.raw; // Get the count value
                            const total = tooltipItem.dataset.data.reduce((sum, val) => sum + val,
                                0); // Calculate total count
                            const percentage = ((value / total) * 100).toFixed(2); // Calculate percentage
                            return `${tooltipItem.label}: ${value} (${percentage}%)`; // Combine count and percentage
                        },
                    },
                },
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    let clickedIndex = elements[0].index;
                    let clickedCategory = employeeChart.data.labels[clickedIndex];

                    // Get active filters
                    const selectedBranch = branchFilter.value;
                    const selectedDept = deptFilter.value;
                    const selectedStatus = statusFilter.value;
                    const selectedGender = genderFilter.value;
                    const selectedLegend = legendFilter.value; // Legend filter (Dept, Branch, Status)

                    fetchEmployeeData(clickedCategory, selectedBranch, selectedDept, selectedStatus,
                        selectedGender, selectedLegend);
                }
            }
        },
    });

    // Function to fetch employees based on active filters
    function fetchEmployeeData(category, branch, dept, status, gender, legend) {
        fetch("{{ route('getEmployeesByChartCategory') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({
                    category,
                    branch,
                    dept,
                    status,
                    gender,
                    legend
                })
            })
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById("employeeList");
                tableBody.innerHTML = "";

                if (data.length > 0) {
                    data.forEach((emp, index) => {
                        let row = `<tr>
                        <td>${index + 1}</td>
                        <td>${emp.NIK}</td>
                        <td>${emp.Nama}</td>
                        <td>${emp.department_name}</td>
                        <td>${emp.employee_status}</td>
                    </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No employees found</td></tr>`;
                }

                document.getElementById("modalTitle").innerText = `Employees in ${category}`;
                let employeeModal = new bootstrap.Modal(document.getElementById("employeeModal"));
                employeeModal.show();
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    ctx.onclick = function(event) {
        const points = employeeChart.getElementsAtEventForMode(event, 'nearest', {
            intersect: true
        }, true);
        if (points.length) {
            const clickedLabel = employeeChart.data.labels[points[0].index];
            // Filter table
            table.column(selectedLegend).search(clickedLabel).draw();
        }
    };


    // Function to filter data based on current selections
    function getFilteredData() {
        const selectedBranch = branchFilter.value;
        const selectedDept = deptFilter.value;
        const selectedStatus = statusFilter.value;
        const selectedGender = genderFilter.value;

        // Filter the data
        const filteredData = chartData.filter(item => {
            return (!selectedBranch || item.Branch === selectedBranch) &&
                (!selectedDept || item.Dept.dept_no === selectedDept) &&
                (!selectedStatus || item.Status === selectedStatus) &&
                (!selectedGender || item.Gender === selectedGender);
        });

        const totalEmployees = filteredData.length;
        const statusRatios = filteredData.reduce((acc, item) => {
            acc[item.Status] = (acc[item.Status] || 0) + 1;
            return acc;
        }, {});

        const ratio = ((statusRatios['KONTRAK'] || 0) / totalEmployees) * 100;
        const riskAlert = document.getElementById('riskAlert');
        const riskText = document.getElementById('riskText');


        if (ratio > 50) {
            riskText.textContent = `Ratio karyawan Kontrak dengan status karyawan lainnya: ${ratio.toFixed(2)}%`;
            riskAlert.classList.remove('d-none'); // Remove d-none to make it visible
        } else {
            riskText.textContent = '';
            riskAlert.classList.add('d-none'); // Add d-none to hide it again
        }

        return filteredData;
    }

    // Function to update the chart
    function updateChart() {
        const filteredData = getFilteredData();
        const selectedLegend = legendFilter.value; // Get the selected legend (Status, Dept, or Branch)

        // Group data for the chart
        const groupedData = filteredData.reduce((acc, item) => {
            let key;

            if (selectedLegend === "Dept") {
                // If legend is "Dept", use the department name or dept_no as the key
                key = item.Dept.name; // Use item.Dept.dept_no if needed
            } else {
                // For "Branch" or "Status", use the respective property
                key = item[selectedLegend];
            }

            acc[key] = (acc[key] || 0) + 1;
            return acc;
        }, {});

        const labels = Object.keys(groupedData);
        const data = Object.values(groupedData);

        const dominantCount = Math.max(...data);
        const dominantCategory = dominantCount === -Infinity ? 'undefined' : labels[data.indexOf(dominantCount)];
        document.getElementById('dominantCategory').textContent =
            `${dominantCategory} (${dominantCount === -Infinity ? 'undefined' : dominantCount})`;

        employeeChart.data.labels = labels;
        employeeChart.data.datasets[0].data = data;
        employeeChart.data.datasets[0].backgroundColor = labels.map(() =>
            `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`
        );

        employeeChart.update();
    }


    function updateDropdowns() {
        const selectedLegend = legendFilter.value; // Get the selected legend
        const selectedBranch = branchFilter.value;
        const selectedDept = deptFilter.value;
        const selectedStatus = statusFilter.value;
        const selectedGender = genderFilter.value;

        // Disable filters based on the selected legend and reset their values
        if (selectedLegend === "Branch") {
            branchFilter.disabled = true;
            branchFilter.value = ""; // Reset to "All"
            deptFilter.disabled = false;
            statusFilter.disabled = false;
        } else if (selectedLegend === "Dept") {
            branchFilter.disabled = false;
            deptFilter.disabled = true;
            deptFilter.value = ""; // Reset to "All"
            statusFilter.disabled = false;
        } else if (selectedLegend === "Status") {
            branchFilter.disabled = false;
            deptFilter.disabled = false;
            statusFilter.disabled = true;
            statusFilter.value = ""; // Reset to "All"
        }

        // Populate Branch dropdown dynamically (exclude the legend if it's "Branch")
        if (selectedLegend !== "Branch") {
            const branchOptions = [...new Set(chartData
                .filter(item => (!selectedDept || item.Dept.dept_no === selectedDept) &&
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
        }

        // Populate Dept dropdown dynamically (exclude the legend if it's "Dept")
        if (selectedLegend !== "Dept") {
            const deptOptions = [...new Set(chartData
                .filter(item => (!selectedBranch || item.Branch === selectedBranch) &&
                    (!selectedStatus || item.Status === selectedStatus))
                .map(item => JSON.stringify(item.Dept)))];
            deptFilter.innerHTML = '<option value="">All</option>';
            deptOptions.forEach(dept => {
                const deptObj = JSON.parse(dept);
                const option = document.createElement('option');
                option.value = deptObj.dept_no;
                option.textContent = deptObj.name;
                deptFilter.appendChild(option);
            });

            deptFilter.value = selectedDept && deptOptions.some(dept => JSON.parse(dept).dept_no === selectedDept) ?
                selectedDept :
                "";
        }

        // Populate Status dropdown dynamically (exclude the legend if it's "Status")
        if (selectedLegend !== "Status") {
            const statusOptions = [...new Set(chartData
                .filter(item => (!selectedBranch || item.Branch === selectedBranch) &&
                    (!selectedDept || item.Dept.dept_no === selectedDept))
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

        // Populate Gender dropdown dynamically
        const genderOptions = [...new Set(chartData
            .filter(item => (!selectedBranch || item.Branch === selectedBranch) &&
                (!selectedDept || item.Dept.dept_no === selectedDept) &&
                (!selectedStatus || item.Status === selectedStatus))
            .map(item => item.Gender))];
        genderFilter.innerHTML = '<option value="" selected>All</option>';
        genderOptions.forEach(gender => {
            const option = document.createElement('option');
            option.value = gender;
            option.textContent = gender === 'M' ? 'Male' : 'Female';
            genderFilter.appendChild(option);
        });
        genderFilter.value = selectedGender && genderOptions.includes(selectedGender) ? selectedGender : "";
    }

    // Event listeners for dropdowns
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

    genderFilter.addEventListener('change', () => {
        updateChart();
    });

    legendFilter.addEventListener('change', () => {
        updateDropdowns(); // Update dropdown options and visibility
        updateChart(); // Update the chart with the new legend
    });

    updateDropdowns();
    updateChart();

    const authDepartmentDeptNo =
        {{ $authUser->department->name !== 'MANAGEMENT' ? $authUser->department->dept_no ?? 'null' : 'null' }};
    if (authDepartmentDeptNo) {
        deptFilter.value = authDepartmentDeptNo;
        updateChart();
        updateDropdowns();
    }
</script>
