@extends('layouts.app')

@section('content')
  @include('partials.info-discipline-page-yayasan-modal')
  <a class="btn btn-secondary float-right" data-bs-target="#info-discipline-page-yayasan"
    data-bs-toggle="modal"> Info </a>

  @if (!$user->is_head && !$user->is_gm)
    @include('partials.upload-excel-file-discipline-magang-modal')
    <button type="button" class="btn btn-primary btn-upload" data-bs-toggle="modal"
      data-bs-target="#upload-excel-file-discipline-magang-modal">Upload
      File
      Excel</button>
  @endif
  @php
    foreach ($employees as $employee) {
        if ($employee->pengawas) {
            $condition = 0;
        } else {
            $condition = 1;
        }
    }
  @endphp

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
      <button type="submit" class="btn btn-danger" id="approve-gm-data-btn"><i
          class='bx bxs-lock'></i> Approve GM
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
      {{ date('Y') }}
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
      @include('partials.edit-discipline-magang-modal')
    @endforeach

    {{ $dataTable->scripts() }}

    <script type="module">
      $(function() {
        let selectedMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
        let realMonth = selectedMonth;

        let dataTable = window.LaravelDataTables["disciplinemagang-table"];
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
