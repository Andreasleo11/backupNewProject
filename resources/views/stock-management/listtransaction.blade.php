@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Transaction List
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('transaction.list') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="stock_id" class="form-label">Stock ID</label>
                                <select class="form-select" id="stock_id" name="stock_id">
                                    <option value="">Select Stock</option>
                                    @foreach ($masterStocks as $masterStock)
                                        <option value="{{ $masterStock->id }}" {{ request('stock_id') == $masterStock->id ? 'selected' : '' }}>
                                            {{ $masterStock->stock_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 align-self-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('transaction.list') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <!-- Transaction Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Unique Code</th>
                                    <th>Stock Code</th>
                                    <th>Department</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Receiver</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datas as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->unique_code }}</td>
                                    <td>{{ $data->historyTransaction->stock_code }}</td>
                                    <td>{{ $data->deptRelation->name ?? '' }}</td>
                                    <td>{{ $data->in_time }}</td>
                                    <td>{{ $data->out_time }}</td>
                                    <td>{{ $data->receiver }}</td>
                                    <td>{{ $data->remark }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- No data message -->
                    @if ($datas->isEmpty())
                        <p class="text-center">No data</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
