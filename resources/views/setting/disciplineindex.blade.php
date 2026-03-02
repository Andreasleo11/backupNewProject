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

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">Filter Tahun</label>
                    <select name="filter_year" id="year-filter" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @for ($year = 2020; $year <= date('Y'); $year++)
                            <option value="{{ $year }}" @if ($year == date('Y')) selected @endif>
                                {{ $year }}</option>
                        @endfor
                    </select>
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

        {{-- Files List --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mt-4">
            <h4 class="text-lg font-semibold text-slate-800 mb-4">Files</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="file-list">
                <!-- Files will be loaded here dynamically -->
            </div>
        </div>
    </div>

    @push('modals')
        @include('partials.edit-discipline-modal')
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
                let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
                let selectedYear = new Date().getFullYear();

                // Initialize DataTable and apply initial filter if the month is stored
                let dataTable = window.LaravelDataTables["disciplinetable-table"];
                
                $('#status-filter').val(selectedMonth); // Set the selected month in the filter select
                $('#year-filter').val(selectedYear); // Set the selected year in the filter select
                applyFilter(selectedMonth, selectedYear); // Apply the filter

                // Event listener for filter select elements
                $('#status-filter').change(function() {
                    let selectedMonth = $(this).val();
                    let selectedYear = $('#year-filter').val();
                    console.log("Selected month:", selectedMonth);
                    applyFilter(selectedMonth, selectedYear);
                });

                $('#year-filter').change(function() {
                    let selectedYear = $(this).val();
                    let selectedMonth = $('#status-filter').val();
                    console.log("Selected year:", selectedYear);
                    applyFilter(selectedMonth, selectedYear);
                });

                // Function to apply filter to DataTable
                function applyFilter(selectedMonth, selectedYear) {
                    let formattedMonth = selectedMonth.toString().padStart(2, '0');
                    let formattedYear = selectedYear.toString();
                    console.log("Formatted filter:", formattedYear + '-' + formattedMonth);

                    filterAndDisplayEmployees(formattedMonth);

                    // Filter by month and year column
                    dataTable.column(6).search(formattedYear + '-' + formattedMonth + '-', true, false).draw();

                    let dept = "{{ Auth::user()->department->dept_no ?? '' }}";
                    loadFiles(formattedYear, formattedMonth, dept);
                }

                function loadFiles(year, month, dept) {
                    $.ajax({
                        url: "/get-files",
                        type: "GET",
                        data: {
                            year: year,
                            month: month,
                            dept: dept
                        },
                        success: function(response) {
                            $("#file-list").html("");

                            if (response.files && response.files.length > 0) {
                                response.files.forEach(file => {
                                    let fileCard = `
                                        <a href="/storage/files/${file.name}" download="${file.name}" class="block group">
                                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 h-full flex items-center hover:bg-slate-100 hover:border-blue-300 transition-colors">
                                                <div class="text-slate-600 text-sm font-medium line-clamp-2">
                                                    ${file.name}
                                                </div>
                                            </div>
                                        </a>
                                    `;
                                    $("#file-list").append(fileCard);
                                });
                            } else {
                                $("#file-list").html("<p class='text-slate-500 text-sm'>No Files Found</p>");
                            }
                        }
                    });
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
                // Legacy session storage call removed as it's not needed with datatables JS filtering
            }

            // Event listener for filter select element
            document.getElementById('status-filter').addEventListener('change', function() {
                var filterValue = this.value;
                // Currently handled by DataTables draw
            });

            // Highest/Lowest Score Calculation moved to Backend
            document.getElementById('status-filter').addEventListener('change', function() {
                var selectedMonth = this.value;
                var filteredEmployeesContainer = document.getElementById('filtered-employees');
                filteredEmployeesContainer.innerHTML = '<span class="text-xs text-slate-500 italic">Score metrics loading...</span>';

                // Fetch real metrics from the backend here if needed, or leave it to standard table sorting
            });


            document.getElementById('other-route-button').addEventListener('click', function() {
                const filterStatus = document.getElementById('status-filter').value;
                const url = `{{ route('export.yayasan.full') }}?filter_status=${filterStatus}`;
                window.location.href = url;
            });
        </script>
    @endpush
@endsection
