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

@if($user->is_head && !$user->is_gm)
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
        {{ date('Y') }}
    </div>
    <div class="col text-end" id="filtered-employees">
        <!-- Filtered employees will be displayed here -->
    </div>

    @if($user->is_gm === 1)
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="form-label">Filter Departement</div>
        </div>
        <div class="col-auto">
            <select name="filter_dept" id="dept-filter" class="form-select">
                <option value="351">Maintenance Moulding</option>
                <option value="311">PPIC</option>
                <option value="390">Plastic Injection</option>
                <option value="363">Moulding</option>
                <option value="362">Assembly</option>
                <option value="361">Second Process</option>
                <option value="350">Maintenance</option>
                <option value="331">Logistic</option>
                <option value="330">Store</option>
                <option value="340">QC</option>
            </select>
        </div>
        <div class="col-auto">
            {{ date('Y') }}
        </div>
        <div class="col text-end" id="filtered-employees">
            <!-- Filtered employees will be displayed here -->
        </div>
    @endif
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
        const deptFilterDropdown = document.getElementById('dept-filter');
        const filterMonthInput = document.getElementById('filter-month-input');
        const lockDataBtn = document.getElementById('approve-data-btn');
        const gmApprovalDataBtn = document.getElementById('approve-gm-data-btn');
        const triggerbutton = document.getElementById('trigger-script-btn');
        var isGm = @json(Auth::user()->is_gm);

        // Get current month in 'MM' format
        let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');

        // Initialize dropdown with the current month selected
        statusFilterDropdown.value = selectedMonth;

        // Initialize hidden input with the same month
        filterMonthInput.value = statusFilterDropdown.value;

        function checkIfAllLocked(employees) {
            console.log('Checking if all locked:', employees);
            return employees.every(employee => employee.pengawas !== null && employee.depthead === null);
        }

        function checkifGmReady(employees){
            console.log('Checking if GM ready:', employees);
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
                        if (checkIfAllLocked(employees)) {
                            lockDataBtn.disabled = false;
                        } else {
                            lockDataBtn.disabled = true;
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

        function fetchFilteredEmployeeGM(filterMonth, deptFilter) {
            fetch(`/fetch/filtered/yayasan-employees-GM?filter_month=${filterMonth}&filter_dept=${deptFilter}`)
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
                            if (checkifGmReady(employees)) {
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

        fetchFilteredEmployeesAndUpdateButton(selectedMonth);

        statusFilterDropdown.addEventListener('change', () => {
            const selectedFilterMonth = statusFilterDropdown.value;
            filterMonthInput.value = selectedFilterMonth;
            const selectedDepartment = deptFilterDropdown ? deptFilterDropdown.value : null;

            if(isGm) {
                fetchFilteredEmployeeGM(selectedFilterMonth, selectedDepartment); // Call GM specific function
            } else {
                fetchFilteredEmployeesAndUpdateButton(selectedFilterMonth); // Default fetch function
            }
        });

        if (deptFilterDropdown) {
            deptFilterDropdown.addEventListener('change', () => {
                const selectedFilterMonth = statusFilterDropdown.value;
                const selectedDepartment = deptFilterDropdown.value;
                fetchFilteredEmployeeGM(selectedFilterMonth, selectedDepartment);
            });
        }
    });
</script>

<script type="module">
    $(function() {
        let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
        let realMonth = selectedMonth;

        let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];
        if (realMonth) {
            $('#status-filter').val(realMonth);
            applyFilter(realMonth);
        }

        $('#status-filter').change(function() {
            let realMonth = $(this).val();
            console.log("Selected month:", realMonth);
            localStorage.setItem('selectedMonth', realMonth);
            applyFilter(realMonth);
        });

        function applyFilter(realMonth) {
            let formattedMonth = realMonth.padStart(2, '0');
            console.log("Formatted month:", formattedMonth);
            dataTable.column(6).search('-' + formattedMonth + '-', true, false).draw();
        }
    });

    $(function() {
        let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];

        $('#dept-filter').change(function() {
            let selectedDept = $(this).val();
            console.log("Selected department:", selectedDept);
            applyDeptFilter(selectedDept);
        });

        function applyDeptFilter(selectedDept) {
            console.log("Applying department filter:", selectedDept);
            if (selectedDept) {
                dataTable.column(3).search(selectedDept, true, false).draw();
            } else {
                dataTable.column(3).search('').draw();
            }
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

    document.getElementById('status-filter').addEventListener('change', function() {
        var filterValue = this.value;
        setFilterValue(filterValue);
    });
</script>

@endsection
