@extends('layouts.app')

@section('content')



<div class="container">
    <div class="card">
    @if($project->status == "Initiating")
    <div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 10%"></div>
    </div>
    @endif
    @if($project->status == "OnGoing")
    <div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 30%"></div>
    </div>
    @endif
    @if($project->status == "ReadyToTest")
    <div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 60%"></div>
    </div>
    @endif
    @if($project->status == "NeedToBeRevised")
    <div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 80%"></div>
    </div>
    @endif

    @if($project->status == "Accept")
    <div class="progress" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
    </div>
    @endif
        <div class="card-header">{{ $project->project_name }}</div>
        <div class="card-body">
            <p><strong>Department:</strong> {{ $project->dept }}</p>
            <p><strong>Request Date:</strong> {{ $project->request_date }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
            <p><strong>End Date:</strong> {{ $project->end_date }}</p>
            <p><strong>PIC:</strong> {{ $project->pic }}</p>
            <p><strong>Description:</strong> {{ $project->description }}</p>
            <p><strong>Status:</strong> {{ $project->status }}</p>
        </div>
    </div>
</div>

            @if ($histories->isEmpty())
                <p>No historical data available</p>
            @else
            @foreach ($histories as $history)
                <p><strong>Date:</strong> {{ $history->date }} &nbsp;&nbsp; <strong>Status:</strong> {{ $history->status }} &nbsp;&nbsp; <strong>Remark:</strong> {{ $history->remarks }}</p>
            @endforeach
            @endif



@if ($project->start_date == null || $project->status == "Initiating")
    <form action="{{ route('pt.updateongoing', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="remark">Remark:</label>
            <input type="text" id="remark" name="remark" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Start Project</button>
    </form>
@endif


@if ($project->status == "OnGoing" || $project->status == "NeedToBeRevised" )
    <form action="{{ route('pt.updatetest', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="remark">Remark:</label>
            <input type="text" id="remark" name="remark" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Go for Testing</button>
    </form>
@endif


@if ($project->status == "ReadyToTest")
@include('partials.reject-project-tracker', ['id' => $project->id])
<button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal" data-bs-target="#rejectModal">Revision</button>
    <!-- <form action="{{ route('pt.updaterevision', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        <button type="submit" class="btn btn-primary">Need Revision</button>
    </form> -->

    <form action="{{ route('pt.updateaccept', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="remark">Remark:</label>
            <input type="text" id="remark" name="remark" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Accept</button>
    </form>
@endif





@endsection