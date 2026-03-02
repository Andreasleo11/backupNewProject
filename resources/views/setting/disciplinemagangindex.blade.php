@extends('new.layouts.app')

@section('content')
    @include('partials.info-discipline-page-yayasan-modal')
    <a class="btn btn-secondary float-right" data-bs-target="#info-discipline-page-yayasan" data-bs-toggle="modal"> Info </a>

    @if (!$user->is_head && !$user->is_gm)
        @include('partials.upload-excel-file-discipline-magang-modal')
        <button type="button" class="btn btn-primary btn-upload" data-bs-toggle="modal"
            data-bs-target="#upload-excel-file-discipline-magang-modal">Upload
            File
            Excel</button>
    @endif


    @if (($user->is_head && !$user->is_gm) || $user->email === 'fery@daijo.co.id')
        <form method="POST" action="{{ route('approve.data.depthead') }}" id="lock-form">
            @csrf
            <input type="hidden" name="filter_month" id="filter-month-input">
            <!-- If there are employees that are not locked, show the button -->
            <button type="submit" class="btn btn-danger" id="approve-data-btn"><i class='bx bxs-lock'></i>
                Approve DeptHead
            </button>
        </form>
    @endif

    @if ($user->is_gm)
        <form method="POST" action="{{ route('approve.data.gm') }}" id="lock-form">
            @csrf
            <input type="hidden" name="filter_month" id="filter-month-input">
            <input type="hidden" name="filter_dept" id="filter-dept-input">
            <!-- If there are employees that are not locked, show the button -->
            <button type="submit" class="btn btn-danger" id="approve-gm-data-btn"><i class='bx bxs-lock'></i> Approve GM
            </button>
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
            <div class="form-label">Filter Tahun</div>
        </div>
        <div class="col-auto">
            <select name="filter_year" id="year-filter" class="form-select">
                @for ($year = 2020; $year <= date('Y'); $year++)
                    <option value="{{ $year }}" @if ($year == date('Y')) selected @endif>
                        {{ $year }}</option>
                @endfor
            </select>
        </div>
        <div class="col text-end" id="filtered-employees">
            <!-- Filtered employees will be displayed here -->
        </div>

        @if ($user->is_gm === 1)
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="form-label">Filter Departement</div>
                </div>
                <div class="col-auto">
                    <select name="filter_dept" id="dept-filter" class="form-select">
                        <option value="351">Maintenance Machine</option>
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
                    <div class="form-label">Filter Tahun</div>
                </div>
                <div class="col-auto">
                    <select name="filter_year_gm" id="year-filter-gm" class="form-select">
                        @for ($year = 2020; $year <= date('Y'); $year++)
                            <option value="{{ $year }}" @if ($year == date('Y')) selected @endif>
                                {{ $year }}</option>
                        @endfor
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

        @include('partials.edit-discipline-magang-modal')

        {{ $dataTable->scripts() }}

        <script type="module">
            $(function() {
                let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
                let selectedYear = new Date().getFullYear();
                let realMonth = selectedMonth;
                let realYear = selectedYear;

                let dataTable = window.LaravelDataTables["disciplinemagang-table"];
                
                if (localStorage.getItem('selectedMonth')) {
                    realMonth = localStorage.getItem('selectedMonth');
                }

                $('#status-filter').val(realMonth);
                $('#year-filter').val(realYear);
                applyFilter(realMonth, realYear);

                $('#status-filter').change(function() {
                    realMonth = $(this).val();
                    console.log("Selected month:", realMonth);
                    localStorage.setItem('selectedMonth', realMonth);
                    applyFilter(realMonth, realYear);
                });

                $('#year-filter').change(function() {
                    realYear = $(this).val();
                    console.log("Selected year:", realYear);
                    applyFilter(realMonth, realYear);
                });

                function applyFilter(realMonth, realYear) {
                    let formattedMonth = realMonth.padStart(2, '0');
                    let formattedYear = realYear.toString();
                    console.log("Formatted filter:", formattedYear + "-" + formattedMonth);
                    
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
                                    <div class="col d-flex col-xl-3 col-md-4 my-2">
                                        <a href="/storage/files/${file.name}" download="${file.name}">
                                            <div class="card">
                                                <div class="card-body btn btn-light" style="max-width: 250px">
                                                    <div class="col d-flex align-items-center p-0 text-center" style="min-height:100px">
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
                let dataTable = window.LaravelDataTables["disciplinemagang-table"];

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


        </script>
    @endsection
