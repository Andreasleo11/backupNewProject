@extends('layouts.app')

@section('content')
    <div class="container">

    </div>
    <section aria-label="header">

    </section>

    <section aria-label="content">
        <div class="container mt-5">
            <div class="card">
                <div class="p-4 pb-0">
                    <h4 class="fw-lighter text-secondary fs-3">QA/QC Reports</h4>
                </div>
                <hr>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Approved" :content="$reportCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Waiting" :content="$reportCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Rejected" :content="$reportCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="header2">
    </section>

    <section aria-label="content2">
        <div class="container mt-5">
            <div class="card">
                <div class="p-4 pb-0">
                    <h4 class="text-secondary fs-3">Purchase Requests</h4>
                </div>
                <hr>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('purchaserequest.home') }}">
                                <x-card title="Approved" :content="$purchaseRequestCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('purchaserequest.home') }}">
                                <x-card title="Waiting" :content="$purchaseRequestCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="{{ route('purchaserequest.home') }}">
                                <x-card title="Rejected" :content="$purchaseRequestCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
