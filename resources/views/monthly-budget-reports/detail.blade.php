@extends('new.layouts.app')

@section('content')
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-4 space-y-4">

        {{-- Breadcrumb --}}
        <nav class="text-xs text-slate-500" aria-label="breadcrumb">
            <ol class="flex flex-wrap items-center gap-1">
                <li>
                    <a href="{{ route('monthly-budget-reports.index') }}"
                       class="inline-flex items-center text-slate-500 hover:text-slate-700">
                        Monthly Budget Reports
                    </a>
                </li>
                <li>/</li>
                <li class="text-slate-700 font-medium">Detail</li>
            </ol>
        </nav>

        {{-- Autograph section --}}
        <section class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/70 p-4">
            @include('partials.monthly-budget-report-autograph')
        </section>

        {{-- Report card --}}
        <section aria-label="report">
            <div class="mt-4">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/70">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">
                                Monthly Budget Report
                            </h2>
                            <p class="text-xs text-slate-500">
                                Detail report & approval status
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            @if (
                                ($authUser->id === $report->user->id && !$report->created_autograph) ||
                                    ($authUser->is_head && !$report->is_known_autograph))
                                <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5
                                          text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                    <i class="bx bx-edit mr-1 text-sm"></i>
                                    <span>Edit</span>
                                </a>

                                @include('partials.delete-confirmation-modal', [
                                    'id' => $report->id,
                                    'route' => 'monthly-budget-reports.delete',
                                    'title' => 'Delete report confirmation',
                                    'body' =>
                                        "Are you sure want to delete this report with id <strong>$report->id</strong>?",
                                ])

                                <button type="button"
                                        class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-xs
                                               font-semibold text-white shadow-sm hover:bg-rose-700"
                                        data-bs-toggle="modal"
                                        data-bs-target="#delete-confirmation-modal-{{ $report->id }}">
                                    <i class="bx bx-trash-alt mr-1 text-sm"></i>
                                    <span>Delete</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="px-4 py-5 space-y-5">
                        {{-- Header info --}}
                        <div class="text-center space-y-2">
                            <h1 class="text-lg font-bold text-slate-900">
                                Monthly Budget Report
                            </h1>

                            @php
                                $reportDate = \Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');
                            @endphp

                            <div class="text-xs text-slate-600 space-y-1">
                                <div>
                                    From Department :
                                    <span class="font-semibold">
                                        {{ $report->department->name }} ({{ $report->dept_no }})
                                    </span>
                                </div>
                                <div>
                                    Created By :
                                    <span class="font-semibold">{{ $report->user->name }}</span>
                                </div>
                                <div>
                                    Report date :
                                    <span class="font-semibold">
                                        {{ $report->report_date }} ({{ $monthYear }})
                                    </span>
                                </div>
                                <div class="pt-1">
                                    @include('partials.monthly-budget-report-status', [
                                        'status' => $report->status,
                                        'isCancel' => $report->is_cancel,
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr class="text-[11px] font-semibold text-slate-600">
                                            <th class="px-3 py-2 text-left">Name</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-3 py-2 text-left">Spec</th>
                                            @endif
                                            <th class="px-3 py-2 text-center">UoM</th>
                                            @if ($report->dept_no == 363)
                                                <th class="px-3 py-2 text-right">Last Recorded Stock</th>
                                                <th class="px-3 py-2 text-right">Usage Per Month</th>
                                            @endif
                                            <th class="px-3 py-2 text-right">Quantity</th>
                                            <th class="px-3 py-2 text-left">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($report->details as $detail)
                                            <tr class="hover:bg-slate-50/80">
                                                <td class="px-3 py-2 text-xs text-slate-800">
                                                    {{ $detail->name }}
                                                </td>

                                                @if ($report->dept_no == 363)
                                                    <td class="px-3 py-2 text-xs text-slate-700">
                                                        {{ $detail->spec }}
                                                    </td>
                                                @endif

                                                <td class="px-3 py-2 text-xs text-center text-slate-700">
                                                    {{ $detail->uom }}
                                                </td>

                                                @if ($report->dept_no == 363)
                                                    <td class="px-3 py-2 text-xs text-right text-slate-700">
                                                        {{ $detail->last_recorded_stock }}
                                                    </td>
                                                    <td class="px-3 py-2 text-xs text-right text-slate-700">
                                                        {{ $detail->usage_per_month }}
                                                    </td>
                                                @endif

                                                <td class="px-3 py-2 text-xs text-right text-slate-800">
                                                    {{ $detail->quantity }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-slate-700">
                                                    {{ $detail->remark }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $report->dept_no == 363 ? '7' : '4' }}"
                                                    class="px-3 py-6 text-center text-xs text-slate-500">
                                                    No data
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
