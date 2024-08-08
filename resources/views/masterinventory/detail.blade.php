@extends('layouts.app')

@section('content')


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">

<div class="container">
    <h1>Detail for Master Inventory</h1>

    <div class="card mb-4">
    <a href="{{ route('masterinventory.editpage', $data->id) }}" class="btn btn-warning">Edit</a>
        <div class="card-header">
            <h5>Master Inventory Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $data->ip_address }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $data->username }}</td>
                    </tr>
                    <tr>
                        <th>Position Image</th>
                        <td>
                            @if($data->position_image)
                                <a href="{{ asset('storage/' . $data->position_image) }}" data-fancybox="gallery" data-caption="Position Image">
                                    <img src="{{ asset('storage/' . $data->position_image) }}" alt="Position Image" style="max-width: 200px; max-height: 100px;">
                                </a>
                            @else
                                No image available
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{ $data->dept }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{{ $data->type }}</td>
                    </tr>
                    <tr>
                        <th>Purpose</th>
                        <td>{{ $data->purpose }}</td>
                    </tr>
                    <tr>
                        <th>Brand</th>
                        <td>{{ $data->brand }}</td>
                    </tr>
                    <tr>
                        <th>OS</th>
                        <td>{{ $data->os }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $data->description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="hardware-tab" data-toggle="tab" href="#hardware" role="tab" aria-controls="hardware" aria-selected="true">Hardware</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="software-tab" data-toggle="tab" href="#software" role="tab" aria-controls="software" aria-selected="false">Software</a>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="inventoryTabsContent">
        <!-- Hardware Tab -->
        <div class="tab-pane fade show active" id="hardware" role="tabpanel" aria-labelledby="hardware-tab">
            <h2>Hardware Details</h2>
            @if($data->hardwares->isEmpty())
                <p>No hardware details available.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Brand</th>
                            <th>Hardware Name</th>
                            <th>Remark</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->hardwares as $hardware)
                            <tr>
                                <td>{{ $hardware->hardwareType->name ?? 'Unknown Type' }}</td>
                                <td>{{ $hardware->brand }}</td>
                                <td>{{ $hardware->hardware_name }}</td>
                                <td>{{ $hardware->remark }}</td>
                                <td>{{ $hardware->updated_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Software Tab -->
        <div class="tab-pane fade" id="software" role="tabpanel" aria-labelledby="software-tab">
            <h2>Software Details</h2>
            @if($data->softwares->isEmpty())
                <p>No software details available.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Software Name</th>
                            <th>License</th>
                            <th>Remark</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->softwares as $software)
                            <tr>
                                <td>{{ $software->softwareType->name ?? 'Unknown Type' }}</td>
                                <td>{{ $software->software_name }}</td>
                                <td>{{ $software->license }}</td>
                                <td>{{ $software->remark }}</td>
                                <td>{{ $software->updated_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Include Bootstrap JS for tabs functionality -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

@endsection
