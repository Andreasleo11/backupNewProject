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
                        <x-card title="Approved" :content="$approvedDoc" color="green" titleColor="text-success"
                            icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                    </a>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('director.qaqc.index') }}">
                        <x-card title="Waiting" :content="$waitingDoc" color="orange" titleColor="text-warning"
                            icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                    </a>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('director.qaqc.index') }}">
                        <x-card title="Rejected" :content="$rejectedDoc" color="red" titleColor="text-danger"
                            contentColor="text-secondary"
                            icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />

                    </a>
                </div>

            </div>
        </div>
    </section>
@endsection
