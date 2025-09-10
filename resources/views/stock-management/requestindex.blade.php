@extends('layouts.app')

@section('content')

  <!-- Filter Form -->
  <form method="GET" action="{{ route('testing.request') }}" class="mb-3">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="filterStock" class="form-label">Filter by Stock</label>
        <select class="form-select" id="filterStock" name="stock_id">
          <option value="">All Stocks</option>
          @foreach ($masterStocks as $masterStock)
            <option value="{{ $masterStock->id }}"
              {{ request('stock_id') == $masterStock->id ? 'selected' : '' }}>
              {{ $masterStock->stock_code }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label for="filterDepartment" class="form-label">Filter by Department</label>
        <select class="form-select" id="filterDepartment" name="dept_id">
          <option value="">All Departments</option>
          @foreach ($departments as $dept)
            <option value="{{ $dept->id }}"
              {{ request('dept_id') == $dept->id ? 'selected' : '' }}>
              {{ $dept->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label for="filterMonth" class="form-label">Filter by Month</label>
        <input type="month" class="form-control" id="filterMonth" name="month"
          value="{{ request('month') }}">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary mt-3">Apply Filters</button>
        @if (request()->hasAny(['stock_id', 'dept_id', 'month']))
          <a href="{{ route('testing.request') }}" class="btn btn-link mt-3">Clear Filters</a>
        @endif
      </div>
    </div>
  </form>

  <!-- Modal for adding Stock Request -->
  <div class="modal fade" id="addStockRequestModal" tabindex="-1"
    aria-labelledby="addStockRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('stockrequest.store') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="addStockRequestModalLabel">Add Stock Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"
              aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="masterStock" class="form-label">Stock Master</label>
              <select class="form-select" id="masterStock" name="masterStock" required>
                @foreach ($masterStocks as $masterStock)
                  <option value="{{ $masterStock->id }}">{{ $masterStock->stock_code }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <select class="form-select" id="department" name="department" required>
                @foreach ($departments as $dept)
                  <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="stockRequest" class="form-label">Stock Requested</label>
              <input type="number" class="form-control" id="stockRequest" name="stockRequest"
                required>
            </div>
            <div class="mb-3">
              <label for="month" class="form-label">Month</label>
              <input type="date" class="form-control" id="month" name="month" required>
            </div>
            <div class="mb-3">
              <label for="remark" class="form-label">Remark</label>
              <input type="text" class="form-control" id="remark" name="remark">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Button trigger modal -->
  <div class="mt-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
      data-bs-target="#addStockRequestModal">
      Add Stock Request
    </button>
  </div>

  <div class="row mt-4">
    <div class="col">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            @if ($datas->isEmpty())
              <p class="text-center">No data</p>
            @else
              <table class="table mb-0">
                <thead>
                  <tr>
                    <th>Stock Type</th>
                    <th>Stock Name</th>
                    <th>Dept No Request</th>
                    <th>Dept Name Request</th>
                    <th>Stock Requested</th>
                    <th>Available Stock</th>
                    <th>Month</th>
                    <th>Remark</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($datas as $data)
                    <tr>
                      <td>{{ $data->stockRelation->stockType->name }}</td>
                      <td>{{ $data->stockRelation->stock_code }}</td>
                      <td>{{ $data->deptRelation->dept_no }}</td>
                      <td>{{ $data->deptRelation->name }}</td>
                      <td>{{ $data->request_quantity }}</td>
                      <td>{{ $data->quantity_available }}</td>
                      <td>{{ $data->month }}</td>
                      <td>{{ $data->remark }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
