@extends('layouts.app')
@section('content')
  @include('partials.alert-success-error')

  {{-- GLOBAL VARIABLE --}}
  @php
    $authUser = auth()->user();
  @endphp
  {{-- END GLOBAL VARIABLE --}}

  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">SPK</a>
        </li>
        <li class="breadcrumb-item active">List</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col">
        <h2 class="fw-bold">SPK List</h2>
      </div>
      <div class="col text-end">
        @php
          $showCreateButton = false;
          if ($authUser->department->name !== 'MANAGEMENT') {
              $showCreateButton = true;
          }
        @endphp
        @if ($showCreateButton)
          <a href="{{ route('spk.create') }}" class="btn btn-primary">New Report</a>
        @endif
        <a href="{{ route('spk.monthlyreport') }}" class="btn btn-primary">Monthly Report</a>
      </div>
    </div>

    <form action="{{ route('spk.index') }}" method="GET" class="needs-validation" novalidate>
      <div class="row align-items-end mt-3">
        <div class="col-auto">
          <label for="filter_column">Filter Column</label>
          <select name="filter_column" id="filter_column" class="form-select">
            <option value="" selected disabled>--Select column--</option>
            <option value="no_dokumen"
              {{ request('filter_column') == 'no_dokumen' ? 'selected' : '' }}>No.
              Dokumen</option>
            <option value="pelapor" {{ request('filter_column') == 'pelapor' ? 'selected' : '' }}>
              Pelapor
            </option>
            <option value="tanggal_lapor"
              {{ request('filter_column') == 'tanggal_lapor' ? 'selected' : '' }}>
              Tanggal Lapor</option>
            <option value="judul_laporan"
              {{ request('filter_column') == 'judul_laporan' ? 'selected' : '' }}>
              Judul Laporan</option>
            <option value="pic" {{ request('filter_column') == 'pic' ? 'selected' : '' }}>PIC
            </option>
          </select>
          <div class="invalid-feedback">
            Please select the column.
          </div>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-auto">
          <label for="filter_action">Action</label>
          <select name="filter_action" id="filter_action" class="form-select">
            <option value="" selected disabled>--Select action--</option>
            <option value="contains" {{ request('filter_action') == 'contains' ? 'selected' : '' }}>
              Contains
            </option>
            <option value="equals" {{ request('filter_action') == 'equals' ? 'selected' : '' }}>Equals
            </option>
            <option value="between" {{ request('filter_action') == 'between' ? 'selected' : '' }}>
              Between
            </option>
            <option value="greater_than"
              {{ request('filter_action') == 'greater_than' ? 'selected' : '' }}>
              Greater Than</option>
            <option value="less_than"
              {{ request('filter_action') == 'less_than' ? 'selected' : '' }}>Less Than
            </option>
          </select>
          <div class="invalid-feedback">
            Please select the action.
          </div>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-auto">
          <label for="filter_value">Filter Value</label>
          <input type="text" name="filter_value" id="filter_value"
            value="{{ request('filter_value') }}" class="form-control">
          <div class="invalid-feedback">
            Please fill the filter value.
          </div>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-auto" id="filter_value_2_container"
          style="display: {{ request('filter_action') == 'between' && request('filter_column') == 'tanggal_lapor' ? 'block' : 'none' }}">
          <label for="filter_value_2">Filter Value 2</label>
          <input type="text" name="filter_value_2" id="filter_value_2"
            value="{{ request('filter_value_2') }}" class="form-control">
          <div class="invalid-feedback">
            Please fill the filter value.
          </div>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
          @if (request('filter_column') ||
                  request('filter_action') ||
                  request('filter_value') ||
                  request('filter_value_2'))
            <a href="{{ route('spk.index') }}" class="btn btn-secondary">Reset</a>
          @endif
        </div>
      </div>
    </form>

    <div class="card mt-3">
      <div class=card-body>
        <table class="table table-border text-center mb-0">
          <thead>
            <tr>
              <th>No. Dokumen</th>
              <th>Pelapor</th>
              <th>Requested By</th>
              <th>Tanggal Lapor</th>
              <th>Judul Laporan</th>
              <th>PIC</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($reports as $report)
              <tr>
                <td class="align-content-center">{{ $report->no_dokumen }}</td>
                <td class="align-content-center">{{ $report->pelapor }}</td>
                <td class="align-content-center">{{ $report->requested_by }}</td>
                <td class="align-content-center">@formatDate($report->tanggal_lapor)</td>
                <td class="align-content-center">{{ $report->judul_laporan }}</td>
                <td class="align-content-center">{{ $report->pic ?? 'Not Assigned' }}</td>
                <td class="align-content-center">@include('partials.spk-status', [
                    'status' => $report->status_laporan,
                    'is_urgent' => $report->is_urgent,
                ])</td>
                <td class="align-content-center">@include('partials.spk-actions')</td>
              </tr>
            @empty
              <tr>
                <td colspan="9">No data</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
      {{ $reports->links() }}
    </div>
  </div>
@endsection

@push('extraJs')
  <script>
    (() => {
      'use strict'

      const forms = document.querySelectorAll('.needs-validation')

      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          const filterColumn = document.getElementById('filter_column')
          const filterAction = document.getElementById('filter_action')
          const filterValue = document.getElementById('filter_value')
          const filterValue2Container = document.getElementById('filter_value_2_container')
          const filterValue2 = document.getElementById('filter_value_2')

          // Custom validation for select elements
          if (filterColumn.value === '' || filterColumn.value === '--Select column--') {
            filterColumn.setCustomValidity('Please select the column.')
          } else {
            filterColumn.setCustomValidity('')
          }

          if (filterAction.value === '' || filterAction.value === '--Select action--') {
            filterAction.setCustomValidity('Please select the action.')
          } else {
            filterAction.setCustomValidity('')
          }

          // Custom validation for filter values
          if (filterValue.value.trim() === '') {
            filterValue.setCustomValidity('Please fill the filter value.')
          } else {
            filterValue.setCustomValidity('')
          }

          if (filterValue2Container.style.display === 'block' && filterValue2.value
            .trim() ===
            '') {
            filterValue2.setCustomValidity('Please fill the filter value.')
          } else {
            filterValue2.setCustomValidity('')
          }

          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }

          form.classList.add('was-validated')
        }, false)
      })
    })()

    document.getElementById('filter_action').addEventListener('change', function() {
      const filterValue2Container = document.getElementById('filter_value_2_container');
      if (this.value === 'between' && document.getElementById('filter_column').value ===
        'tanggal_lapor') {
        filterValue2Container.style.display = 'block';
      } else {
        filterValue2Container.style.display = 'none';
      }
    });

    document.getElementById('filter_column').addEventListener('change', function() {
      const filterValueInput = document.getElementById('filter_value');
      const filterValue2Input = document.getElementById('filter_value_2');
      const filterAction = document.getElementById('filter_action');

      if (this.value === 'tanggal_lapor') {
        filterValueInput.type = 'date';
        filterValue2Input.type = 'date';

        // Enable greater_than and less_than for tanggal_lapor
        filterAction.querySelector('option[value="greater_than"]').disabled = false;
        filterAction.querySelector('option[value="less_than"]').disabled = false;
        filterAction.querySelector('option[value="between"]').disabled = false;
      } else {
        filterValueInput.type = 'text';
        filterValue2Input.type = 'text';

        // Disable greater_than, less_than, and between for other columns
        filterAction.querySelector('option[value="greater_than"]').disabled = true;
        filterAction.querySelector('option[value="less_than"]').disabled = true;
        filterAction.querySelector('option[value="between"]').disabled = true;
      }
    });

    document.getElementById('filter_column').dispatchEvent(new Event('change'));
  </script>
@endpush
