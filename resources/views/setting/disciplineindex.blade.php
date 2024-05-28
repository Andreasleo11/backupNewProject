@extends('layouts.app')

@section('content')

<!-- <form method="POST" action="{{ route('discipline.import') }}" enctype="multipart/form-data">
        @csrf
        <label for="excel_files">Upload File Excel yang sudah diisi dengan point point kedisiplinan disini dalam bentuk EXCEL (.xlsx):</label>
        <input type="file" name="excel_files[]" id="excel_files" onchange="displayUploadedFiles()" multiple>
        <br>
        <button type="submit">Submit</button>
    </form> -->

    @include('partials.info-discipline-page-modal')
    <a class="btn btn-secondary float-right" data-bs-target="#info-discipline-page" data-bs-toggle="modal" > Info </a>

    <a href="{{ route('update.point') }}" class="btn btn-primary">Update Point</a>

    @if($user->department_id === 7 || $user->department_id === 22)
        <a href="{{ route('alldiscipline.index') }}" class="btn btn-primary">List All Department</a></a>
    @endif

 
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="form-label">Filter Bulan</div>
        </div>
        <div class="col-auto">
            <select name="filter_status" id="status-filter" class="form-select">
                <option value="01" selected>January</option>
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
        <div class="col-auto">
            <?php echo date('Y'); ?>
        </div>
        <div class="col text-end" id="filtered-employees">
            <!-- Filtered employees will be displayed here --> 
        </div>
    </div>

<section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>

    @foreach($employees as $employee)
            @include('partials.edit-discipline-modal')
    @endforeach

{{ $dataTable->scripts() }}

<script type="module">
    $(function() {
    // Check if the filtered month is stored in localStorage
    let selectedMonth = localStorage.getItem('selectedMonth');

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
        localStorage.setItem('selectedMonth', selectedMonth);

        applyFilter(selectedMonth); // Apply the filter
    });

    // Function to apply filter to DataTable
    function applyFilter(selectedMonth) {
        // Extract the month part from the date format (yyyy-mm-dd)
        let formattedMonth = selectedMonth.padStart(2, '0'); // Pad single-digit months with 0
        console.log("Formatted month:", formattedMonth);

        filterAndDisplayEmployees(formattedMonth);

        // Filter by month column
        dataTable.column(4).search('-' + formattedMonth + '-', true, false).draw();
    }
});
    
    function setFilterValue(filterValue) {
        fetch('/set-filter-value', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ filterValue: filterValue }),
        })
        .then(response => response.json())
        .then(data => {
            console.log('Filter value set in session:', data.filterValue);
        })
        .catch(error => {
            console.error('Error setting filter value:', error);
        });
    }

    // Function to restore filter value from session
    function restoreFilterValue() {
        fetch('/get-filter-value')
        .then(response => response.json())
        .then(data => {
            if (data.filterValue) {
                document.getElementById('status-filter').value = data.filterValue;
            }
        })
        .catch(error => {
            console.error('Error getting filter value:', error);
        });
    }

    // Call restoreFilterValue() when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        restoreFilterValue();
    });

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
    var highestTotalEmployees = filteredEmployees.filter(employee => employee.total === highestTotal);

    // Filter out employees with total === 0 before calculating the lowest total
    var nonZeroEmployees = filteredEmployees.filter(employee => employee.total !== 0);

    // Find the lowest total among the non-zero filtered employees
    var lowestTotal = nonZeroEmployees.length > 0 ? Math.min(...nonZeroEmployees.map(employee => employee.total)) : 0;

    // Filter employees with the lowest total for the selected month
    var lowestTotalEmployees = nonZeroEmployees.filter(employee => employee.total === lowestTotal);

    // Display filtered employees with the highest and lowest total
    var filteredEmployeesContainer = document.getElementById('filtered-employees');
    filteredEmployeesContainer.innerHTML = ''; // Clear previous content

    if (filteredEmployees.length === 0) {
        filteredEmployeesContainer.textContent = 'No employees found for selected month';
    } else {
        highestTotalEmployees.forEach(function(employee) {
            var employeeInfo = document.createElement('div');
            employeeInfo.textContent = 'Karyawan Terbaik : ' + employee.karyawan.Nama + ' - Total: ' + employee.total;
            filteredEmployeesContainer.appendChild(employeeInfo);
        });

        lowestTotalEmployees.forEach(function(employee) {
            var employeeInfo = document.createElement('div');
            employeeInfo.textContent = 'Karyawan Terburuk: ' + employee.karyawan.Nama + ' - Total: ' + employee.total;
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
    var highestTotal = nonZeroEmployees.length > 0 ? Math.max(...nonZeroEmployees.map(employee => employee.total)) : 0;

    // Filter employees with the highest total for the selected month
    var highestTotalEmployees = nonZeroEmployees.filter(employee => employee.total === highestTotal);

    // Find the lowest total among the non-zero filtered employees
    var lowestTotal = nonZeroEmployees.length > 0 ? Math.min(...nonZeroEmployees.map(employee => employee.total)) : 0;

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
            employeeInfo.textContent = 'Karyawan Tertinggi : ' + employee.karyawan.Nama + ' - Poin: ' + employee.total;
            employeeInfo.style.color = 'green';
            filteredEmployeesContainer.appendChild(employeeInfo);
        });

        lowestTotalEmployees.forEach(function(employee) {
            var employeeInfo = document.createElement('div');
            employeeInfo.textContent = 'Karyawan Terbawah: ' + employee.karyawan.Nama + ' - Poin: ' + employee.total;
            employeeInfo.style.color = 'red';
            filteredEmployeesContainer.appendChild(employeeInfo);
        });
    }
}


</script>


@endsection