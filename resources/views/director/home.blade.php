@extends('new.layouts.app')

@section('page-title', 'Director Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        @include('partials.alert-success-error')

        {{-- View Selector --}}
        <div class="flex justify-end">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold inline-flex items-center gap-2">
                    <span id="viewSelectorBtn">Select View</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-10">
                    <a class="block px-4 py-2 text-slate-700 hover:bg-slate-100 view-option" data-value="1" href="#" @click="open = false">Dashboard</a>
                    <a class="block px-4 py-2 text-slate-700 hover:bg-slate-100 view-option" data-value="2" href="#" @click="open = false">Dashboard HRIS</a>
                </div>
            </div>
        </div>

        {{-- Main Dashboard --}}
        <div id="view-1">
            <div class="grid md:grid-cols-2 gap-6">
                {{-- QA/QC Reports --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="p-6 pb-0">
                        <h3 class="text-xl font-light text-slate-600">QA/QC Reports</h3>
                    </div>
                    <hr class="my-4">
                    <div class="px-6 pb-6">
                        <div class="grid grid-cols-3 gap-4">
                            <a href="{{ route('director.qaqc.index') }}" class="block">
                                <x-card title="Approved" :content="$reportCounts['approved']" color="green" titleColor="text-green-600"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('director.qaqc.index') }}" class="block">
                                <x-card title="Waiting" :content="$reportCounts['waiting']" color="orange" titleColor="text-amber-600"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('director.qaqc.index') }}" class="block">
                                <x-card title="Rejected" :content="$reportCounts['rejected']" color="red" titleColor="text-red-600"
                                    contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Purchase Requests --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="p-6 pb-0">
                        <h3 class="text-xl text-slate-600">Purchase Requests</h3>
                    </div>
                    <hr class="my-4">
                    <div class="px-6 pb-6">
                        <div class="grid grid-cols-3 gap-4">
                            <a href="{{ route('director.pr.index') }}" class="block">
                                <x-card title="Approved" :content="$purchaseRequestCounts['approved']" color="green" titleColor="text-green-600"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('director.pr.index') }}" class="block">
                                <x-card title="Waiting" :content="$purchaseRequestCounts['waiting']" color="orange" titleColor="text-amber-600"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('director.pr.index') }}" class="block">
                                <x-card title="Rejected" :content="$purchaseRequestCounts['rejected']" color="red" titleColor="text-red-600"
                                    contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mt-6">
                {{-- Monthly Budget Reports --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="p-6 pb-0">
                        <h3 class="text-xl font-light text-slate-600">Monthly Budget Reports</h3>
                    </div>
                    <hr class="my-4">
                    <div class="px-6 pb-6">
                        <div class="grid grid-cols-3 gap-4">
                            <a href="{{ route('monthly.budget.report.index') }}" class="block">
                                <x-card title="Approved" :content="$monthlyBudgetReportsCounts['approved']" color="green" titleColor="text-green-600"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('monthly.budget.report.index') }}" class="block">
                                <x-card title="Waiting" :content="$monthlyBudgetReportsCounts['waiting']" color="orange" titleColor="text-amber-600"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('monthly.budget.report.index') }}" class="block">
                                <x-card title="Rejected" :content="$monthlyBudgetReportsCounts['rejected']" color="red" titleColor="text-red-600"
                                    contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Monthly Budget Summary Reports --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="p-6 pb-0">
                        <h3 class="text-xl text-slate-600">Monthly Budget Summary Reports</h3>
                    </div>
                    <hr class="my-4">
                    <div class="px-6 pb-6">
                        <div class="grid grid-cols-3 gap-4">
                            <a href="{{ route('monthly-budget-summary-report.index') }}" class="block">
                                <x-card title="Approved" :content="$monthlyBudgetSummaryReportsCounts['approved']" color="green" titleColor="text-green-600"
                                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('monthly-budget-summary-report.index') }}" class="block">
                                <x-card title="Waiting" :content="$monthlyBudgetSummaryReportsCounts['waiting']" color="orange" titleColor="text-amber-600"
                                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                            </a>
                            <a href="{{ route('monthly-budget-summary-report.index') }}" class="block">
                                <x-card title="Rejected" :content="$monthlyBudgetSummaryReportsCounts['rejected']" color="red" titleColor="text-red-600"
                                    contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Order Reports --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm mt-6">
                <div class="p-6 pb-0">
                    <h3 class="text-xl text-slate-600">Purchase Order Reports</h3>
                </div>
                <hr class="my-4">
                <div class="px-6 pb-6">
                    <div class="grid grid-cols-3 gap-4">
                        <a href="{{ route('po.dashboard') }}" class="block">
                            <x-card title="Approved" :content="$poCounts['approved']" color="green" titleColor="text-green-600"
                                icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
                        </a>
                        <a href="{{ route('po.dashboard') }}" class="block">
                            <x-card title="Waiting" :content="$poCounts['waiting']" color="orange" titleColor="text-amber-600"
                                icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
                        </a>
                        <a href="{{ route('po.dashboard') }}" class="block">
                            <x-card title="Rejected" :content="$poCounts['rejected']" color="red" titleColor="text-red-600"
                                contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- HRIS Dashboard --}}
        <div id="view-2" class="hidden">
            <x-employee-dashboard />

            <div class="mt-4">
                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium" data-bs-toggle="modal"
                    data-bs-target="#filteredEmployeesModal" id="viewFilteredEmployeesBtn">
                    View Filtered Employees
                </button>

                @include('partials.view-warning-logs-modal')
                @include('partials.add-warning-logs-modal')
            </div>
        </div>
    </div>

        {{-- 
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
        --}}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.view-option').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    let selectedValue = this.getAttribute('data-value');

                    // Update button text
                    document.getElementById("viewSelectorBtn").textContent = this.textContent;

                    // Hide all views
                    document.getElementById("view-1").classList.add("hidden");
                    document.getElementById("view-2").classList.add("hidden");

                    // Show selected view
                    document.getElementById(`view-${selectedValue}`).classList.remove("hidden");
                });
            });
        });
    </script>
@endsection
