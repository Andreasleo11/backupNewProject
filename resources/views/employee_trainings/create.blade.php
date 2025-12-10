@extends('new.layouts.app')

@section('content')
    <div class="max-w-3xl px-4 py-6 mx-auto space-y-5">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-sm">
            <ol class="flex flex-wrap gap-1 text-slate-500">
                <li>
                    <a href="{{ route('employee_trainings.index') }}"
                       class="hover:text-slate-700">
                        Employee Trainings
                    </a>
                </li>
                <li aria-hidden="true" class="text-slate-400">/</li>
                <li class="font-medium text-slate-700">
                    Add Training
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="space-y-1">
            <h1 class="text-xl font-semibold text-slate-900">
                Add Training
            </h1>
            <p class="text-sm text-slate-500">
                Create a new training record for an employee.
            </p>
        </div>

        {{-- Form card --}}
        <div class="overflow-hidden bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="p-6">
                <x-employee-training-form
                    :action="route('employee_trainings.store')"
                    :employees="$employees"
                    submit-label="Save"
                />
            </div>
        </div>
    </div>
@endsection
