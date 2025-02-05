<div>
    <div class="row">
        <div class="container-fluid">
            <div class="row">
                <h1 class=" fs-1">HRIS Dashboard</h1>

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
                                <div class="card-body">
                                    <p class="card-text text-secondary fs-5">Total Employees
                                    </p>
                                    <span class="fw-bold badge text-bg-dark fs-4"
                                        id="totalEmployees">{{ $employeeData['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card mt-4">
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

                <div class="col">
                    <label for="monthYearFilter" class="form-label">Select Available Report</label>
                    <select id="monthYearFilter" name="monthYear" class="form-select">
                        {{-- <option value="">All</option> --}}
                        @foreach ($monthYearOptions as $option)
                            <option value="{{ $option['value'] }}" {{ $loop->last ? 'selected' : '' }}>
                                {{ $option['name'] }}</option>
                        @endforeach
                    </select>

                    <!-- Employee Category Cards -->
                    <div class="row">
                        @foreach (['Alpha' => 'danger', 'Telat' => 'warning', 'Izin' => 'primary', 'Sakit' => 'success'] as $category => $color)
                            <div class="col">
                                <div class="card mt-4" data-category="{{ $category }}">
                                    <button class="btn btn-light open-modal" data-category="{{ $category }}"
                                        data-bs-toggle="modal" data-bs-target="#employeeByCategoryModal">
                                        <div class="card-body text-start">
                                            <span class="fw-bold badge text-bg-{{ $color }} fs-3"
                                                id="{{ strtolower($category) }}">{{ $employeeData[strtolower($category)] }}</span>
                                            <p class="card-text text-secondary fs-4">{{ $category }}</p>
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
                                    <h3 class="modal-title" id="employeeByCategoryModalLabel">Employee List</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h5 class="text-secondary" id="modalCategoryTitle"></h5>
                                    <p class="fw-bold" id="modalMonthTitle"></p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>NIK</th>
                                                    <th>Name</th>
                                                    <th>Department</th>
                                                    <th>Status</th>
                                                    <th id="categoryCountTitle"></th> <!-- Dynamic Category Column -->
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
                        <h3 class="text-secondary">Employee Count per Department</h3>
                        <canvas class="mt-3" id="departmentEmployeeChart"></canvas>
                    </div>
                </div>
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
                                    <tr>
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

    {{-- Employee List Modal Script --}}
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('employeeByCategoryModal'));

            document.querySelectorAll('.open-modal').forEach(button => {
                button.addEventListener('click', function() {
                    let category = this.getAttribute('data-category');
                    let monthYear = document.getElementById('monthYearFilter')
                        .value; // Get selected month-year filter

                    document.getElementById('modalCategoryTitle').innerText =
                        `${category} category`;
                    document.getElementById('categoryCountTitle').innerText =
                        category; // Set table column title
                    document.getElementById('modalMonthTitle').innerText = monthYear;

                    fetch("{{ route('getEmployeesByCategory') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute("content")
                            },
                            body: JSON.stringify({
                                category: category,
                                monthYear: monthYear
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
                                    totalCount += parseInt(emp.category_count) ||
                                        0; // Sum up category count

                                    let row = `<tr>
                                            <td>${index + 1}</td> <!-- Row Number -->
                                            <td>${emp.NIK}</td>
                                            <td>${emp.Nama}</td>
                                            <td>${emp.department_name}</td>
                                            <td>${emp.employee_status}</td>
                                            <td>${emp.category_count}</td> <!-- Show category count -->
                                        </tr>`;
                                    tableBody.innerHTML += row;
                                });

                                // Append total count row
                                let totalRow = `<tr class="fw-bold">
                                                <td colspan="5" class="text-end">Total ${category}:</td>
                                                <td>${totalCount}</td>
                                            </tr>`;
                                tableBody.innerHTML += totalRow;
                            } else {
                                tableBody.innerHTML =
                                    `<tr><td colspan="6" class="text-center">No employees found</td></tr>`;
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

            // Create datasets dynamically based on available statuses
            const datasets = allStatuses.map(status => ({
                label: status,
                data: barDataArray.map(item => item.breakdown[status] ||
                    0), // Fill missing values with 0
                backgroundColor: getRandomColor(), // Assign a unique color
                borderColor: 'rgba(0, 0, 0, 0.8)',
                borderWidth: 1
            }));

            // Add total employee count as a separate dataset (bar with different color)
            datasets.push({
                label: 'Total Employees',
                data: barDataArray.map(item => item.total_count), // Total employees per department
                backgroundColor: 'rgba(255, 99, 132, 0.6)', // Different color for total count
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                type: 'line', // Line chart overlay on top of bar chart
                fill: false
            });

            // Chart.js instance
            const ctx = document.getElementById('departmentEmployeeChart').getContext('2d');
            new Chart(ctx, {
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
        fetchEmployeeData($('#monthYearFilter').val());

        $('#monthYearFilter').on('change', function() {
            fetchEmployeeData(this.value);
        });

        function fetchEmployeeData(monthYear) {
            fetch("{{ route('filter.employees') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({
                        monthYear: monthYear
                    })
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("totalEmployees").innerText = data.total;
                    document.getElementById("alpha").innerText = data.alpha;
                    document.getElementById("telat").innerText = data.telat;
                    document.getElementById("izin").innerText = data.izin;
                    document.getElementById("sakit").innerText = data.sakit;
                })
                .catch(error => console.error("Error fetching data:", error));
        }
    </script>

    {{-- dynamic active filter script for pie chart and table --}}
    <script>
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
                deptFilter.innerHTML = '<option value="" selected>All</option>';
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
    </script>
</div>
