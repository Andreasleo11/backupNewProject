@extends('layouts.app')

@section('content')

<div class="container">
    <div class="card">
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


@if ($project->start_date == null || $project->status == "Initiating")
    <form action="{{ route('pt.updateongoing', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        <button type="submit" class="btn btn-primary">Start Project</button>
    </form>
@endif


@if ($project->status == "OnGoing" || $project->status == "NeedToBeRevised" )
    <form action="{{ route('pt.updatetest', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
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
        <button type="submit" class="btn btn-primary">Accept</button>
    </form>
@endif





@endsection