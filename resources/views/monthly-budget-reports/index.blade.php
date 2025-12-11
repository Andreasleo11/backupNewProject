@extends('new.layouts.app')

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        $showCreateButton = false;
        if (!$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'MANAGEMENT') {
            $showCreateButton = true;
        }
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="max-w-6xl mx-auto px-4 py-4 space-y-4">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="text-sm">
            <ol class="flex flex-wrap items-center gap-1 text-slate-500">
                <li>
                    <a href="{{ route('monthly-budget-reports.index') }}" class="hover:text-slate-700 hover:underline">
                        Monthly Budget Reports
                    </a>
                </li>
                <li class="text-slate-400">/</li>
                <li class="font-medium text-slate-700">
                    List
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex flex-wrap items-center gap-3 justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Monthly Budget Report
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Monitor and manage your department’s monthly budget reports.
                </p>
            </div>

            @if ($showCreateButton)
                <a href="{{ route('monthly-budget-reports.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <i class="bx bx-plus mr-1 text-[0.9rem]"></i>
                    New Report
                </a>
            @endif
        </div>

        {{-- Table card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">
                    Reports List
                </h3>
                <span class="text-[11px] text-slate-500">
                    Total: <span class="font-semibold">{{ $reports->total() }}</span> reports
                </span>
            </div>

            <div class="px-2 py-2 overflow-x-auto">
                <table class="min-w-full text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2 text-left">Doc. Number</th>
                            <th class="px-3 py-2 text-left">Dept No</th>
                            <th class="px-3 py-2 text-left">Report Date</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            @php
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $formatedDate = $reportDate->format('F Y');
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-3 py-2 align-middle font-medium text-slate-900">
                                    {{ $report->doc_num }}
                                </td>
                                <td class="px-3 py-2 align-middle text-slate-700">
                                    {{ $report->dept_no }}
                                </td>
                                <td class="px-3 py-2 align-middle text-slate-700 whitespace-nowrap">
                                    @formatDate($report->report_date)
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    @include('partials.monthly-budget-report-status', [
                                        'status' => $report->status,
                                        'isCancel' => $report->is_cancel,
                                    ])
                                </td>
                                <td class="px-3 py-2 align-middle text-right">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        {{-- Detail --}}
                                        <a href="{{ route('monthly-budget-reports.show', $report->id) }}"
                                            class="inline-flex items-center rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                            <i class='bx bx-info-circle mr-1 text-[0.9rem]'></i>
                                            <span class="hidden sm:inline">Detail</span>
                                        </a>

                                        @if (
                                            ($authUser->id === $report->user->id && !$report->created_autograph) ||
                                                ($authUser->is_head && !$report->is_known_autograph))
                                            {{-- Edit --}}
                                            <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-[11px] font-medium text-white shadow-sm hover:bg-indigo-700">
                                                <i class='bx bx-edit mr-1 text-[0.9rem]'></i>
                                                <span class="hidden sm:inline">Edit</span>
                                            </a>

                                            {{-- Delete (modal trigger) --}}
                                            @include('partials.delete-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => 'monthly-budget-reports.delete',
                                                'title' => 'Delete report confirmation',
                                                'body' => "Are you sure want to delete this report with id <strong>{$report->id}</strong>?",
                                                'buttonLabel' => 'Delete',
                                            ])
                                        @elseif (!$report->is_cancel && !$report->is_known_autograph)
                                            {{-- Cancel (modal trigger) --}}
                                            @include('partials.cancel-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => route('monthly-budget-reports.cancel', $report->id),
                                                'title' => 'Cancel Confirmation',
                                                'buttonLabel' => 'Cancel',
                                            ])
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bx bx-file-blank text-2xl"></i>
                                        <div class="font-semibold">No data</div>
                                        <p class="text-xs">
                                            There are no monthly budget reports yet.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-end">
                <div class="text-xs text-slate-500">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
