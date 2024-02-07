@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 3px solid blue;    ">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="fs-5 fw-bold text-primary text-uppercase mb-1">Approved</div>
                            <div class="h4 mb-0 fw-bold text-secondary">{{ $approvedDoc}}</div>
                        </div>
                        <div class="col-auto">
                            <box-icon name='check' color="gray" size="lg"></box-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 3px solid green;    ">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="fs-5 fw-bold text-success text-uppercase mb-1">Waiting</div>
                            <div class="h4 mb-0 fw-bold text-secondary">{{$waitingDoc}}</div>
                        </div>
                        <div class="col-auto">
                            <box-icon name='time' color="gray" size="lg"></box-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 3px solid red; ">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="fs-5 fw-bold text-danger text-uppercase mb-1">Rejected</div>
                            <div class="h4 mb-0 fw-bold text-secondary">{{$rejectedDoc}}</div>
                        </div>
                        <div class="col-auto">
                            <box-icon name='x-circle' color="gray" size="lg"></box-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.home') }}" class="btn btn-primary">HOME OF PR</a>
    </div>
</div>
@endsection
