@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl px-4 py-6 mx-auto space-y-5">
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
                    Detail
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Employee Training Details
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Detailed information for this training record.
                </p>
            </div>

            <a href="{{ route('employee_trainings.index') }}"
               class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Back to List
            </a>
        </div>

        {{-- Card --}}
        <div class="overflow-hidden bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="p-6 space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Employee details --}}
                    <section class="space-y-2">
                        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase">
                            Employee Details
                        </h2>
                        <div class="p-4 space-y-1 border rounded-xl border-slate-200 bg-slate-50/60">
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">Name:</span>
                                <span class="ml-1 text-slate-800">{{ $training->employee->name }}</span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">NIK:</span>
                                <span class="ml-1 text-slate-800">{{ $training->employee->nik }}</span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">Department:</span>
                                <span class="ml-1 text-slate-800">{{ $training->employee->dept_code }}</span>
                            </p>
                        </div>
                    </section>

                    {{-- Training details --}}
                    <section class="space-y-2">
                        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase">
                            Training Details
                        </h2>
                        <div class="p-4 space-y-1 border rounded-xl border-slate-200 bg-slate-50/60">
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">Description:</span>
                                <span class="ml-1 text-slate-800">{{ $training->description }}</span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">Last Training Date:</span>
                                <span class="ml-1 text-slate-800">
                                    {{ \Carbon\Carbon::parse($training->last_training_at)->format('d-m-Y') }}
                                </span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium text-slate-600">Evaluated:</span>
                                @if ($training->evaluated)
                                    <span class="inline-flex items-center px-2 py-0.5 ml-2 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 ml-2 text-xs font-semibold rounded-full bg-rose-50 text-rose-700 ring-1 ring-rose-200">
                                        No
                                    </span>
                                @endif
                            </p>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-end gap-2 px-6 py-3 bg-slate-50 border-t border-slate-200">
                <a href="{{ route('employee_trainings.edit', $training->id) }}"
                   class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1">
                    Edit
                </a>

                <form action="{{ route('employee_trainings.destroy', $training->id) }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this training record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center rounded-xl border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-1">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
