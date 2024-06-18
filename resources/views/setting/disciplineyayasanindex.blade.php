@extends('layouts.app')

@section('content')

@include('partials.upload-excel-file-discipline-yayasan-modal')
    <button type="button" class="btn btn-primary btn-upload" data-bs-toggle="modal"
        data-bs-target="#upload-excel-file-discipline-yayasan-modal">Upload
        File
        Excel</button>

@php

foreach($employees as $employee)
{
    if($employee->pengawas)
    {
        $condition = 0;
    }
    else
    {
        $condition = 1;
    }
}

@endphp


@if($user->is_head && !$user->is_gm )
    <form method="POST" action="{{ route('approve.data.depthead') }}" id="lock-form">
        @csrf
        <input type="hidden" name="filter_month" id="filter-month-input">
        <!-- If there are employees that are not locked, show the button -->
        <button type="submit" class="btn btn-danger" id="approve-data-btn"><i class='bx bxs-lock'></i> Approve DeptHead </button>
    </form>
@endif


@if($user->is_gm)
<form method="POST" action="{{ route('approve.data.gm') }}" id="lock-form">
        @csrf
        <input type="hidden" name="filter_month" id="filter-month-input">
        <!-- If there are employees that are not locked, show the button -->
        <button type="submit" class="btn btn-danger" id="approve-gm-data-btn"><i class='bx bxs-lock'></i> Approve GM </button>
    </form>
@endif


<input type="hidden" name="filter_month" id="filter-month-input">

<button type="button" id="trigger-script-btn">Check</button>


<div id="approval-badge-container"></div>
<br>


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

<script>
     document.addEventListener('DOMContentLoaded', () => {
                const statusFilterDropdown = document.getElementById('status-filter');
                const filterMonthInput = document.getElementById('filter-month-input');
                const lockDataBtn = document.getElementById('approve-data-btn');
                const gmApprovalDataBtn = document.getElementById('approve-gm-data-btn');
                const triggerbutton = document.getElementById('trigger-script-btn');
                var isGm = @json(Auth::user()->is_gm);

            
                // Get current month in 'MM' format
                let selectedMonth = (new Date().getMonth()).toString().padStart(2, '0');
                
                // Initialize dropdown with the current month selected
                statusFilterDropdown.value = selectedMonth;
               
                // Initialize hidden input with the same month
                filterMonthInput.value = statusFilterDropdown.value;


                function checkIfAllLocked(employees) {
                    return employees.every(employee => employee.pengawas !== null && employee.depthead === null);
                }


                function checkifGmReady(employees){
                    return employees.every(employee => employee.pengawas !== null && employee.depthead !== null);
                }

                function checkapprovedbydepthead(employees){
                    return employees.every(employee => employee.depthead !== null);
                }

                function checkapprovedbygm(employees){
                    return employees.every(employee => employee.generalmanager !== null);
                }

                // Fetch filtered employees and update the button status
                function fetchFilteredEmployeesAndUpdateButton(filterMonth) {
                    fetch(`/fetch/filtered/yayasan-employees?filter_month=${filterMonth}`)
                        .then(response => response.json())
                        .then(employees => {
                            if (employees.length > 0) {
                                // Check if all data is approved by GM
                                const isApprovedByGM = checkapprovedbygm(employees);

                                // Check if all data is approved by Depthead
                                const isApprovedByDepthead = checkapprovedbydepthead(employees);

                                if (isApprovedByGM) {
                                    // Show GM approval badge
                                    showApprovalBadge('Approved by GM');
                                } else if (isApprovedByDepthead) {
                                    // Show Depthead approval badge
                                    showApprovalBadge('Approved by Depthead');
                                } else {
                                    // Hide approval badge if neither is true
                                    hideApprovalBadge();
                                }

                                // Update button status based on the user role
                                if (isGm) {
                                    if (isApprovedByGM) {
                                        gmApprovalDataBtn.disabled = false;
                                    } else {
                                        gmApprovalDataBtn.disabled = true;
                                    }
                                } else {
                                    if (checkIfAllLocked(employees)) {
                                        lockDataBtn.disabled = false;
                                    } else {
                                        lockDataBtn.disabled = true;
                                    }
                                }
                            } else {
                                // No employees data, hide badge and disable button
                                hideApprovalBadge();
                                lockDataBtn.disabled = true;
                                gmApprovalDataBtn.disabled = true;
                            }
                        })
                        .catch(error => console.error('Error fetching filtered employees:', error));
                }

                function showApprovalBadge(text) {
                    const badgeContainer = document.getElementById('approval-badge-container');
                    badgeContainer.innerHTML = `<span class="badge bg-success">${text}</span>`;
                }

                function hideApprovalBadge() {
                    const badgeContainer = document.getElementById('approval-badge-container');
                    badgeContainer.innerHTML = '';
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

    
        $(function() {
            // Check if the filtered month is stored in localStorage
            let selectedMonth = (new Date().getMonth()).toString().padStart(2, '0');
            let realMonth = (parseInt(selectedMonth)).toString().padStart(2, '0');
            
            // Initialize DataTable and apply initial filter if the month is stored
            let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];
            if (realMonth) {
                $('#status-filter').val(realMonth); // Set the selected month in the filter select
                applyFilter(realMonth); // Apply the filter
            }

            // Event listener for filter select element
            $('#status-filter').change(function() {
                let realMonth = $(this).val();
                console.log("Selected month:", realMonth); // Output the selected month to console

                // Store the selected month in localStorage
                localStorage.setItem('selectedMonth', realMonth);

                applyFilter(realMonth); // Apply the filter
            });

            // Function to apply filter to DataTable
            function applyFilter(realMonth) {
                // Extract the month part from the date format (yyyy-mm-dd)
                let formattedMonth = realMonth.padStart(2, '0'); // Pad single-digit months with 0
                console.log("Formatted month:", formattedMonth );

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

         // Event listener for filter select element
         document.getElementById('status-filter').addEventListener('change', function() {
                var filterValue = this.value;
                setFilterValue(filterValue);
            });
       

        </script>

@endsection