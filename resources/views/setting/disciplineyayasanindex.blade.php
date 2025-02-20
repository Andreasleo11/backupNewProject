@extends('layouts.app')

@section('content')

    @include('partials.info-discipline-page-yayasan-modal')
    <a class="btn btn-secondary float-right" data-bs-target="#info-discipline-page-yayasan" data-bs-toggle="modal"> Info </a>


    <!-- @if (!$user->is_head && !$user->is_gm)
        @include('partials.upload-excel-file-discipline-yayasan-modal')
        <button type="button" class="btn btn-primary btn-upload" data-bs-toggle="modal"
            data-bs-target="#upload-excel-file-discipline-yayasan-modal">Upload
            File
            Excel</button>
    @endif -->

    
    @php
        foreach ($employees as $employee) {
            if ($employee->pengawas) {
                $condition = 0;
            } else {
                $condition = 1;
            }
        }
    @endphp

    @if ($user->is_head && !$user->is_gm || $user->email === "fery@daijo.co.id")
        <!-- <form method="POST" action="{{ route('approve.data.depthead') }}" id="lock-form">
            @csrf
            <input type="hidden" name="filter_month" id="filter-month-input">
            <input type="hidden" name="filter_year" id="filter-year-input"> Add this hidden input -->
            <!-- If there are employees that are not locked, show the button -->
            <!-- <button type="submit" class="btn btn-danger" id="approve-data-btn"><i class='bx bxs-lock'></i> Approve DeptHead
            </button>
        </form> -->
    @endif

    @if ($user->is_gm)
        <!-- <form method="POST" action="{{ route('approve.data.gm') }}" id="lock-form">
            @csrf
            <input type="hidden" name="filter_month" id="filter-month-input">
            <input type="hidden" name="filter_dept" id="filter-dept-input"> -->
            <!-- If there are employees that are not locked, show the button -->
            <!-- <button type="submit" class="btn btn-danger" id="approve-gm-data-btn"><i class='bx bxs-lock'></i> Approve GM
            </button>
        </form> -->
    @endif

    <input type="hidden" name="filter_month" id="filter-month-input">

    <div class="container mt-5">

</div>

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
            <div class="form-label">Filter Tahun</div>
        </div>
        <div class="col-auto">
            <select name="filter_year" id="year-filter" class="form-select">
                @for($year = 2020; $year <= date('Y'); $year++)
                    <option value="{{ $year }}" @if($year == date('Y')) selected @endif>{{ $year }}</option>
                @endfor
            </select>
        </div>
        @if ($user->is_head === 1 && $user->name !== 'Bernadett')
        <div class="col-auto">
            <!-- Approve Form -->
            <form method="POST" action="{{ route('approve.depthead.yayasan') }}" id="approve-form">
                @csrf
                <input type="hidden" name="filter_month" id="bulanDepthead">
                <input type="hidden" name="filter_year" id="tahunDepthead">
                <button type="submit" class="btn btn-success">Approve</button>
            </form>


            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                Reject
            </button>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('reject.depthead.yayasan') }}" id="reject-form">
                                @csrf
                                <input type="hidden" name="filter_month" id="rejectbulanDepthead">
                                <input type="hidden" name="filter_year" id="rejecttahunDepthead">

                                <!-- Remark Textarea -->
                                <div class="form-group">
                                    <label for="remark">Remark</label>
                                    <textarea name="remark" id="remark" class="form-control" rows="3" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" form="reject-form">Confirm Reject</button>
                        </div>
                    </div>
                </div>
            </div>
   


        </div>
        @endif

        @if ($user->name === 'Bernadett')
        <div class="col-auto">
            <!-- Approve Form -->
            <form method="POST" action="{{ route('approve.hrd.yayasan') }}" id="approve-form">
                @csrf
                <input type="hidden" name="filter_month" id="bulanDepthead">
                <input type="hidden" name="filter_year" id="tahunDepthead">
                <input type="hidden" name="filter_dept" id="hrdDept">
                
                <button type="submit" class="btn btn-success">Approve</button>
            </form>


            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectHrdModal">
                Reject
            </button>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectHrdModal" tabindex="-1" aria-labelledby="rejectHrdModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectHrdModalLabel">Reject Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('reject.hrd.yayasan') }}" id="reject-hrd-form">
                                @csrf
                                <input type="hidden" name="filter_month" id="rejectbulanDepthead">
                                <input type="hidden" name="filter_year" id="rejecttahunDepthead">
                                <input type="hidden" name="filter_dept" id="rejecthrdDept">

                                <!-- Remark Textarea -->
                                <div class="form-group">
                                    <label for="remark">Remark</label>
                                    <textarea name="remark" id="remark" class="form-control" rows="3" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" form="reject-hrd-form">Confirm Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        

        <div class="col text-end" id="filtered-employees">
            <!-- Filtered employees will be displayed here -->
        </div>

        @if ($user->is_gm === 1 || $user->name === 'Bernadett')
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="form-label">Filter Departement</div>
                </div>
                <div class="col-auto">
                    <select name="filter_dept" id="dept-filter" class="form-select">
                        <option value="">All</option>
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

        <div class="container border rounded-2 p-3 mt-4">
    <h4>Files</h4>
        <div class="row" id="file-list">
            <!-- Files will be loaded here dynamically -->
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($user->is_head !== 1 && $user->name !== 'Bernadett')
    <!-- Trigger the Modal with a Button -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
            Upload File
        </button>

        <!-- Modal -->
        <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('file.upload.evaluation') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Hidden inputs for filter_month and filter_year -->
                            <input type="hidden" name="filter_month" id="bulanDepthead">
                            <input type="hidden" name="filter_year" id="tahunDepthead">
                            
                            <!-- Hidden input for department -->
                            <input type="hidden" name="department" value="{{ Auth::user()->department->dept_no }}">
                            
                            <div class="form-group">
                                <label for="files">Choose File</label>
                                <input type="file" class="form-control-file" id="files" name="files[]" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        @foreach ($employees as $employee)
            @include('partials.edit-discipline-yayasan-modal')
        @endforeach

        {{ $dataTable->scripts() }}

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const statusFilter = document.getElementById('status-filter');
                const yearFilter = document.getElementById('year-filter');
                const statusFilterDropdown = document.getElementById('status-filter');
                const deptFilterDropdown = document.getElementById('dept-filter');
                const yearFilterDropdown = document.getElementById('year-filter'); // Added year filter
                const filterMonthInput = document.getElementById('filter-month-input');
                const filterDeptInput = document.getElementById('filter-dept-input');
                const filterYearInput = document.getElementById('filter-year-input');
                const lockDataBtn = document.getElementById('approve-data-btn');
                const gmApprovalDataBtn = document.getElementById('approve-gm-data-btn');
                const triggerbutton = document.getElementById('trigger-script-btn');
                var isGm = @json(Auth::user()->is_gm);

                // Get current month in 'MM' format
                let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');

                let selectedYear = new Date().getFullYear();

                // Initialize dropdown with the current month selected
                statusFilterDropdown.value = selectedMonth;
                yearFilterDropdown.value = selectedYear;
                // Initialize hidden input with the same month
                filterMonthInput.value = statusFilterDropdown.value;
                filterYearInput.value = selectedYear;

              
        </script>

        <script type="module">
            $(function() {
        let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
        let selectedYear = new Date().getFullYear();  // Get current year
        let realMonth = selectedMonth;
        let realYear = selectedYear;

        let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];
        
        // Initialize the filters to current values
        $('#status-filter').val(realMonth);
        $('#year-filter').val(realYear);
        applyFilter(realMonth, realYear);

        // When the month is changed
        $('#status-filter').change(function() {
            realMonth = $(this).val();
            console.log("Selected month:", realMonth);
            localStorage.setItem('selectedMonth', realMonth);
            applyFilter(realMonth, realYear);
        });

        // When the year is changed
        $('#year-filter').change(function() {
            realYear = $(this).val();
            console.log("Selected year:", realYear);
            applyFilter(realMonth, realYear);
        });

        function applyFilter(realMonth, realYear) {
            let formattedMonth = realMonth.padStart(2, '0');
            let formattedYear = realYear;

            console.log("Formatted month:", formattedMonth);
            console.log("Formatted year:", formattedYear);
            console.log("reject month:", formattedMonth);
            console.log("reject year:", formattedYear);


            $('#bulanDepthead').val(formattedMonth);
            $('#tahunDepthead').val(formattedYear);
            $('#rejectbulanDepthead').val(formattedMonth);
            $('#rejecttahunDepthead').val(formattedYear);

            // Apply both month and year filter to the dataTable
            dataTable.column(6).search(formattedYear + '-' + formattedMonth + '-', true, false).draw();

            let dept = "{{ Auth::user()->department->dept_no }}";
            console.log("Department:", dept);
            // Call AJAX to fetch files
            loadFiles(formattedYear, formattedMonth, dept);
        }

        function loadFiles(year, month, dept) {
            $.ajax({
                url: "/get-files", // Laravel route that handles the request
                type: "GET",
                data: {
                    year: year,
                    month: month,
                    dept: dept
                },
                success: function(response) {
                    console.log(response.files); // Debugging: Show response in console
                    $("#file-list").html(""); // Clear previous files

                    if (response.files.length > 0) {
                        response.files.forEach(file => {
                            let extension = file.name.split('.').pop().toLowerCase();

                            let fileCard = `
                                <div class="col d-flex col-xl-3 col-md-4 my-2">
                                    <a href="/storage/files/${file.name}" download="${file.name}">
                                        <div class="card">
                                            <div class="card-body btn btn-light" style="max-width: 250px">
                                                <div class="col d-flex align-items-center p-0 text-center" style="min-height:100px">
                                                    <img src="" alt="ext-logo" width="50px" class="me-2">
                                                    <div class="text-secondary text-start fw-semibold" style="overflow: hidden; text-overflow: ellipsis; max-height: 4.5em; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                                        ${file.name}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            `;
                            $("#file-list").append(fileCard);
                        });
                    } else {
                        $("#file-list").html("<p>No Files Found</p>");
                    }
                }
            });
        }


    });

            $(function() {
                let dataTable = window.LaravelDataTables["disciplineyayasantable-table"];

                $('#dept-filter').change(function() {
                    let selectedDept = $(this).val();
                    console.log("Selected department:", selectedDept);
                    applyDeptFilter(selectedDept);
                    console.log("Selected Dept: ", selectedDept);
                    $('#hrdDept').val(selectedDept);
                    $('#rejecthrdDept').val(selectedDept);
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

            function setFilterValues(filterMonth, filterYear) {
                fetch('/set-filter-value', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        filterMonth: filterMonth,
                        filterYear: filterYear,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Filter values set in session:', data);
                })
                .catch(error => {
                    console.error('Error setting filter values:', error);
                });
            }

            document.getElementById('status-filter').addEventListener('change', function() {
                var filterMonth = this.value;
                var filterYear = document.getElementById('year-filter').value;  // Get year from the dropdown
                setFilterValues(filterMonth, filterYear);
            });

            document.getElementById('year-filter').addEventListener('change', function() {
                var filterMonth = document.getElementById('status-filter').value;  // Get month from the dropdown
                var filterYear = this.value;
                setFilterValues(filterMonth, filterYear);
            });

            $(document).ready(function () {
                let dataTable = $('#disciplineyayasantable-table').DataTable();

                // Intercept the print button click to modify the title dynamically
                $(document).on('click', '.buttons-print', function () {
                    let formattedMonth = $("#bulanDepthead").val() || "All Months";
                    let formattedYear = $("#tahunDepthead").val() || "All Years";
                    console.log("asw:", formattedMonth);
                    console.log("pp:", formattedYear);
                    // Modify the title dynamically
                    $(".dt-buttons .buttons-print").attr("title", "Report for " + formattedMonth + "/" + formattedYear);
                });
            });
            

        </script>
    @endsection
