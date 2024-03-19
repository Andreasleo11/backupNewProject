@extends('layouts.app')

@section('content')
    <section aria-label="header">
        <h4 class="fw-lighter text-secondary">QA/QC Reports</h4>
        <hr>
    </section>

    <section aria-label="content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('director.qaqc.index') }}">
                        <div class="card shadow h-100 py-2 btn btn-light text-start" style="border-left: 3px solid green;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-5 fw-bold text-success text-uppercase mb-1">Approved</div>
                                        <div class="h4 mb-0 fw-bold text-secondary">{{ $approvedDoc }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <box-icon name='check' color="gray" size="lg"></box-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('director.qaqc.index') }}">
                        <div class="card shadow h-100 py-2 btn btn-light text-start" style="border-left: 3px solid orange;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-5 fw-bold text-warning text-uppercase mb-1">Waiting</div>
                                        <div class="h4 mb-0 fw-bold text-secondary">{{ $waitingDoc }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <box-icon name='time' color="gray" size="lg"></box-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('director.qaqc.index') }}">
                        <div class="card shadow h-100 py-2 btn btn-light text-start" style="border-left: 3px solid red;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-5 fw-bold text-danger text-uppercase mb-1">Rejected</div>
                                        <div class="h4 mb-0 fw-bold text-secondary">{{ $rejectedDoc }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <box-icon name='x-circle' color="gray" size="lg"></box-icon>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
