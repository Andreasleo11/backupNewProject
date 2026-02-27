@extends('new.layouts.app')

@section('page-title', 'Employee Discipline')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                @push('modals')
                    @include('partials.info-discipline-page-modal')
                @endpush
                <button type="button" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium" data-bs-target="#info-discipline-page" data-bs-toggle="modal">
                    Info
                </button>

                @if ($user->department?->name === 'PERSONALIA')
                    <a href="{{ route('alldiscipline.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium border border-blue-600">
                        List All Selain Yayasan
                    </a>
                    <a href="{{ route('allyayasandiscipline.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium border border-blue-600">
                        List All Yayasan
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @push('modals')
                    @include('partials.upload-excel-file-discipline-modal')
                @endpush
                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium btn-upload" data-bs-toggle="modal" data-bs-target="#upload-excel-file-discipline-modal">
                    Upload File Excel
                </button>

                @push('modals')
                    @include('partials.lock-confirmation-modal', [
                        'id' => 1,
                        'route' => route('lock.data'),
                        'title' => 'Lock Selected Month Data',
                        'body' => 'Once the report is locked, it cannot be <b> modified </b> or <b> edited </b>. Are you sure want to lock all the selected month data?',
                    ])
                @endpush
                <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium" id="lock-data-btn" data-bs-target="#lock-confirmation-modal-1" data-bs-toggle="modal">
                    <i class='bx bxs-lock'></i> Lock Data
                </button>
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('export.yayasan.first.time') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">Filter Bulan</label>
                    <select name="filter_status" id="status-filter" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <div class="text-sm text-slate-600">
                    <?php echo date('Y') - 1; ?>
                </div>

                <div class="flex-1 text-right" id="filtered-employees">
                    <!-- Filtered employees will be displayed here -->
                </div>

                @if ($user->name === 'timotius' || $user->name === 'ani')
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium">
                        Export Yayasan
                    </button>

                    <button type="button" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium" id="other-route-button">
                        Export Yayasan Full
                    </button>
                @endif
            </div>
            <input type="hidden" id="user-department" value="{{ Auth::user()->department_id }}">
        </form>

        {{-- Data Table --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-6">
                <div class="overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        @foreach ($employees as $employee)
            @include('partials.edit-discipline-modal')
        @endforeach
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const statusFilterDropdown = document.getElementById('status-filter');
                const filterMonthInput = document.getElementById('filter-month-input');
                const lockDataBtn = document.getElementById('lock-data-btn');

                // Get current month in 'MM' format
                let selectedMonth = (new Date().getMonth()).toString().padStart(2, '0');

                // Initialize dropdown with the current month selected
                statusFilterDropdown.value = selectedMonth;
                // Initialize hidden input with the same month
                filterMonthInput.value = statusFilterDropdown.value;


                function checkIfAllLocked(employees) {
                    return employees.every(employee => employee.is_lock);
                }

                // Fetch filtered employees and update the button status
                function fetchFilteredEmployeesAndUpdateButton(filterMonth) {
                    fetch(`/fetch/filtered/employees?filter_month=${filterMonth}`)
                        .then(response => response.json())
                        .then(employees => {
                            if (checkIfAllLocked(employees)) {
                                lockDataBtn.disabled = true;
                            } else {
                                lockDataBtn.disabled = false;
                            }
                        })
                        .catch(error => console.error('Error fetching filtered employees:', error));
                }

                // Initial fetch and button update
                fetchFilteredEmployeesAndUpdateButton(selectedMonth);


                statusFilterDropdown.addEventListener('change', () => {
                    const selectedFilterMonth = statusFilterDropdown.value;
                    filterMonthInput.value = selectedFilterMonth;
                    fetchFilteredEmployeesAndUpdateButton(selectedFilterMonth);
                });
            });
        </script>

        <script type="module">
            introJs().start();
            introJs(".btn-upload").start();
            $(document).ready(function() {
                $('buttons-excel').on('click', function() {
                    console.log('cliekd!');
                });
            });
        </script>

        {{ $dataTable->scripts() }}

        <script type="module">
            document.addEventListener('DOMContentLoaded', (event) => {
                const selectElement = document.getElementById('status-filter');
                const currentMonth = new Date().getMonth() + 1; // JavaScript months are 0-11
                const formattedMonth = currentMonth.toString().padStart(2, '0'); // Ensure two digits

                selectElement.value = formattedMonth;
            });

            $(function() {
                // Get the current month and format it as a two-digit string
                let selectedMonth = (new Date().getMonth()).toString().padStart(2, '0');

                // Initialize DataTable and apply initial filter if the month is stored
                let dataTable = window.LaravelDataTables["disciplinetable-table"];
                if (selectedMonth) {
                    $('#status-filter').val(selectedMonth); // Set the selected month in the filter select
                    applyFilter(selectedMonth); // Apply the filter
                }

                // Event listener for filter select element
                $('#status-filter').change(function() {
                    let selectedMonth = $(this).val();
                    console.log("Selected month:", selectedMonth); // Output the selected month to console

                    // Store the selected month in localStorage
                    // localStorage.setItem('selectedMonth', selectedMonth);

                    applyFilter(selectedMonth); // Apply the filter
                });

                // Function to apply filter to DataTable
                function applyFilter(selectedMonth) {
                    // Ensure the month is a two-digit string
                    let formattedMonth = selectedMonth.toString().padStart(2,
                        '0'); // Pad single-digit months with 0
                    console.log("Formatted month:", formattedMonth);

                    filterAndDisplayEmployees(formattedMonth);

                    // Filter by month column
                    dataTable.column(6).search('-' + formattedMonth + '-', true, false).draw();
                }

                dataTable.on('draw.dt', function() {
                    dataTable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        var data = this.data();
                        if (data.is_lock) {
                            // Disable action buttons in this row
                            $(this.node()).find('.action-button').attr('disabled', 'disabled');
                        }
                    });
                });
            });

            function setFilterValue(filterValue) {
                fetch('/set-filter-value', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            filterValue: filterValue
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Filter value set in session:', data.filterValue);
                    })
                    .catch(error => {
                        console.error('Error setting filter value:', error);
                    });
            }


            // Event listener for filter select element
            document.getElementById('status-filter').addEventListener('change', function() {
                var filterValue = this.value;
                setFilterValue(filterValue);
            });

            document.getElementById('status-filter').addEventListener('change', function() {
                var selectedMonth = this.value;
                var employees = @json($employees);

                // Filter employees based on the selected month
                var filteredEmployees = employees.filter(function(employee) {
                    var month = employee.Month.split('-')[1]; // Extract the month part from the date
                    return month === selectedMonth;
                });

                // Find the highest total among the filtered employees
                var highestTotal = Math.max(...filteredEmployees.map(employee => employee.total));

                // Filter employees with the highest total for the selected month
                var highestTotalEmployees = filteredEmployees.filter(employee => employee.total ===
                    highestTotal);

                // Filter out employees with total === 0 before calculating the lowest total
                var nonZeroEmployees = filteredEmployees.filter(employee => employee.total !== 0);

                // Find the lowest total among the non-zero filtered employees
                var lowestTotal = nonZeroEmployees.length > 0 ? Math.min(...nonZeroEmployees.map(employee =>
                    employee
                    .total)) : 0;

                // Filter employees with the lowest total for the selected month
                var lowestTotalEmployees = nonZeroEmployees.filter(employee => employee.total ===
                    lowestTotal);

                // Display filtered employees with the highest and lowest total
                var filteredEmployeesContainer = document.getElementById('filtered-employees');
                filteredEmployeesContainer.innerHTML = ''; // Clear previous content

                if (filteredEmployees.length === 0) {
                    filteredEmployeesContainer.textContent = 'No employees found for selected month';
                } else {
                    highestTotalEmployees.forEach(function(employee) {
                        var employeeInfo = document.createElement('div');
                        employeeInfo.textContent = 'Karyawan Terbaik : ' + employee.karyawan.Nama +
                            ' - Total: ' + employee.total;
                        filteredEmployeesContainer.appendChild(employeeInfo);
                    });

                    lowestTotalEmployees.forEach(function(employee) {
                        var employeeInfo = document.createElement('div');
                        employeeInfo.textContent = 'Karyawan Terburuk: ' + employee.karyawan.Nama +
                            ' - Total: ' + employee.total;
                        filteredEmployeesContainer.appendChild(employeeInfo);
                    });
                }
            });

            // Function to filter and display highest and lowest total employees
            function filterAndDisplayEmployees(month) {
                var employees = @json($employees);

                // Filter employees based on the selected month
                var filteredEmployees = employees.filter(function(employee) {
                    var employeeMonth = employee.Month.split('-')[1]; // Extract the month part from the date
                    return employeeMonth === month;
                });

                // Filter out employees with total === 0
                var nonZeroEmployees = filteredEmployees.filter(employee => employee.total !== 0);

                // Find the highest total among the filtered employees
                var highestTotal = nonZeroEmployees.length > 0 ? Math.max(...nonZeroEmployees.map(employee =>
                        employee.total)) :
                    0;

                // Filter employees with the highest total for the selected month
                var highestTotalEmployees = nonZeroEmployees.filter(employee => employee.total ===
                    highestTotal);

                // Find the lowest total among the non-zero filtered employees
                var lowestTotal = nonZeroEmployees.length > 0 ? Math.min(...nonZeroEmployees.map(employee =>
                        employee.total)) :
                    0;

                // Filter employees with the lowest total for the selected month
                var lowestTotalEmployees = nonZeroEmployees.filter(employee => employee.total === lowestTotal);

                // Display filtered employees with the highest and lowest total
                var filteredEmployeesContainer = document.getElementById('filtered-employees');
                filteredEmployeesContainer.innerHTML = ''; // Clear previous content

                if (nonZeroEmployees.length === 0) {
                    filteredEmployeesContainer.textContent = 'No employees found for selected month';
                } else {
                    highestTotalEmployees.forEach(function(employee) {
                        var employeeInfo = document.createElement('div');
                        employeeInfo.textContent = 'Karyawan Tertinggi : ' + employee.karyawan.Nama +
                            ' - Poin: ' +
                            employee.total;
                        employeeInfo.style.color = 'green';
                        filteredEmployeesContainer.appendChild(employeeInfo);
                    });

                    lowestTotalEmployees.forEach(function(employee) {
                        var employeeInfo = document.createElement('div');
                        employeeInfo.textContent = 'Karyawan Terbawah: ' + employee.karyawan.Nama +
                            ' - Poin: ' +
                            employee.total;
                        employeeInfo.style.color = 'red';
                        filteredEmployeesContainer.appendChild(employeeInfo);
                    });
                }
            }


            document.getElementById('other-route-button').addEventListener('click', function() {
                const filterStatus = document.getElementById('status-filter').value;
                const url = `{{ route('export.yayasan.full') }}?filter_status=${filterStatus}`;
                window.location.href = url;
            });
        </script>
    @endpush
@endsection
