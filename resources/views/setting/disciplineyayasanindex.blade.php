@extends('layouts.app')

@section('content')

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




<section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>


    @foreach ($employees as $employee)
        @include('partials.edit-discipline-yayasan-modal')
    @endforeach



{{ $dataTable->scripts() }}


<script type="module">
        $(function() {
            // Check if the filtered month is stored in localStorage
            let selectedMonth = localStorage.getItem('selectedMonth');

            // Initialize DataTable and apply initial filter if the month is stored
            let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];
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

                // Filter by month column
                dataTable.column(6).search('-' + formattedMonth + '-', true, false).draw();
            }
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

        </script>

@endsection