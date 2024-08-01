@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="fw-lighter text-secondary fs-3">QA/QC Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Approved" :content="$reportCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.qaqc.index') }}">
                                <x-card title="Waiting" :content="$reportCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
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
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="text-secondary fs-3">Purchase Requests</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Approved" :content="$purchaseRequestCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Waiting" :content="$purchaseRequestCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('director.pr.index') }}">
                                <x-card title="Rejected" :content="$purchaseRequestCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="fw-lighter text-secondary fs-3">Monthly Budget Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Approved" :content="$monthlyBudgetReportsCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Waiting" :content="$monthlyBudgetReportsCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.report.index') }}">
                                <x-card title="Rejected" :content="$monthlyBudgetReportsCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="container">
                <div class="p-4 pb-0">
                    <h4 class="text-secondary fs-3">Monthly Budget Summary Reports</h4>
                </div>
                <hr>
                <div class="container p-2 px-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Approved" :content="$monthlyBudgetSummaryReportsCounts['approved']" color="green" titleColor="text-success"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Waiting" :content="$monthlyBudgetSummaryReportsCounts['waiting']" color="orange" titleColor="text-warning"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                        <div class="col">
                            <a href="{{ route('monthly.budget.summary.report.index') }}">
                                <x-card title="Rejected" :content="$monthlyBudgetSummaryReportsCounts['rejected']" color="red" titleColor="text-danger"
                                    contentColor="text-secondary"
                                    icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
