@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl px-4 py-6 mx-auto space-y-4">
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
                    List
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Employee Trainings
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage and monitor employee training records.
                </p>
            </div>

            <a href="{{ route('employee_trainings.create') }}"
               class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Add New Training
            </a>
        </div>

        {{-- DataTable --}}
        <div class="overflow-hidden bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="p-3">
                {{ $dataTable->table([
                    'class' => 'min-w-full text-sm text-left text-slate-700',
                ], true) }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
