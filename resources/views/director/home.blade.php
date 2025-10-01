@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <div class="text-end">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle px-4 py-2 fw-bold" type="button" id="viewSelectorBtn"
                data-bs-toggle="dropdown" aria-expanded="false">
                Select View
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item view-option" data-value="1" href="#">Dashboard</a></li>
                <li><a class="dropdown-item view-option" data-value="2" href="#">Dashboard HRIS</a></li>
            </ul>
        </div>
    </div>

    <div id="view-1">
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
                                <a href="{{ route('monthly-budget-summary-report.index') }}">
                                    <x-card title="Approved" :content="$monthlyBudgetSummaryReportsCounts['approved']" color="green" titleColor="text-success"
                                        icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>

                            <div class="col">
                                <a href="{{ route('monthly-budget-summary-report.index') }}">
                                    <x-card title="Waiting" :content="$monthlyBudgetSummaryReportsCounts['waiting']" color="orange" titleColor="text-warning"
                                        icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>

                            <div class="col">
                                <a href="{{ route('monthly-budget-summary-report.index') }}">
                                    <x-card title="Rejected" :content="$monthlyBudgetSummaryReportsCounts['rejected']" color="red" titleColor="text-danger"
                                        contentColor="text-secondary"
                                        icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col">
                    <div class="container">
                        <div class="p-4 pb-0">
                            <h4 class="text-secondary fs-3">Purchase Order Reports</h4>
                        </div>
                        <hr>
                        <div class="container p-2 px-5">
                            <div class="row justify-content-center">
                                <div class="col">
                                    <a href="{{ route('po.dashboard') }}">
                                        <x-card title="Approved" :content="$poCounts['approved']" color="green"
                                            titleColor="text-success"
                                            icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                                    </a>
                                </div>

                                <div class="col">
                                    <a href="{{ route('po.dashboard') }}">
                                        <x-card title="Waiting" :content="$poCounts['waiting']" color="orange"
                                            titleColor="text-warning"
                                            icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                                    </a>
                                </div>

                                <div class="col">
                                    <a href="{{ route('po.dashboard') }}">
                                        <x-card title="Rejected" :content="$poCounts['rejected']" color="red"
                                            titleColor="text-danger" contentColor="text-secondary"
                                            icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="view-2" class="d-none">
        <x-employee-dashboard />

        <div class="mt-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#filteredEmployeesModal" id="viewFilteredEmployeesBtn">
                View Filtered Employees
            </button>

            @include('partials.view-warning-logs-modal')

            @include('partials.add-warning-logs-modal')
        </div>

        <!-- Modal -->
        <div class="modal fade" id="filteredEmployeesModal" tabindex="-1" aria-labelledby="filteredEmployeesModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filteredEmployeesModalLabel">Filtered Employees</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            {{ $dataTable->table() }}
                            {{ $dataTable->scripts() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.view-option').forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                let selectedValue = this.getAttribute('data-value');

                // Update button text
                document.getElementById("viewSelectorBtn").textContent = this.textContent;

                // Hide all views
                document.getElementById("view-1").classList.add("d-none");
                document.getElementById("view-2").classList.add("d-none");

                // Show selected view
                document.getElementById(`view-${selectedValue}`).classList.remove("d-none");
            });
        });
    </script>
@endsection
