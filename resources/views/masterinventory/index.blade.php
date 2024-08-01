@extends('layouts.app')

@section('content')

<style>
    .card {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}

.card-header {
    background-color: #e9ecef;
    border-bottom: 1px solid #dee2e6;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
}
</style>
<div class="container">
    <h1>Master Inventory List</h1>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="mb-3">
        <a href="{{ route('masterinventory.createpage') }}" class="btn btn-primary">Add New Inventory</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>IP Address</th>
                <th>Username</th>
                <th>Department</th>
                <th>Type</th>
                <th>Purpose</th>
                <th>Brand</th>
                <th>Description</th>
                <th>Hardwares</th>
                <th>Softwares</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datas as $data)
                <tr>
                    <td>{{ $data->ip_address }}</td>
                    <td>{{ $data->username }}</td>
                    <td>{{ $data->dept }}</td>
                    <td>{{ $data->type }}</td>
                    <td>{{ $data->purpose }}</td>
                    <td>{{ $data->brand }}</td>
                    <td>{{ $data->description }}</td>
                    <td>
                        @if($data->hardwares->isEmpty())
                            No hardwares
                        @else
                            @foreach($data->hardwares as $hardware)
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h5 class="card-title">{{ $hardware->hardwareType->name ?? 'Unknown Type' }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Brand:</strong> {{ $hardware->brand }}</p>
                                        <p><strong>Hardware Name:</strong> {{ $hardware->hardware_name }}</p>
                                        <p><strong>Remark:</strong> {{ $hardware->remark }}</p>
                                        <p><strong>Last Update:</strong> {{ $hardware->updated_at }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if($data->softwares->isEmpty())
                            No softwares
                        @else
                            @foreach($data->softwares as $software)
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h5 class="card-title">{{ $software->softwareType->name ?? 'Unknown Type' }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Software Name:</strong> {{ $software->software_name }}</p>
                                        <p><strong>License:</strong> {{ $software->license }}</p>
                                        <p><strong>Remark:</strong> {{ $software->remark }}</p>
                                        <p><strong>Last Update:</strong> {{ $software->updated_at }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('masterinventory.editpage', $data->id) }}" class="btn btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
