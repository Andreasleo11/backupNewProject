@extends('new.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Form Request Trials
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar permintaan trial beserta status approval-nya.
                </p>
            </div>

            <a href="{{ route('pe.trial') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm
                      hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                + Add Request Trial
            </a>
        </div>

        {{-- Card tabel --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="p-3 md:p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">ID</th>
                                <th class="px-3 py-2">Customer</th>
                                <th class="px-3 py-2">Part Name</th>
                                <th class="px-3 py-2">Model</th>
                                <th class="px-3 py-2 text-center">Action</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($trial as $report)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-3 py-2 align-middle">
                                        <span class="text-xs font-medium text-slate-900">
                                            {{ $report->id }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <span class="text-sm text-slate-800">
                                            {{ $report->customer }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <span class="text-sm text-slate-800">
                                            {{ $report->part_name }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <span class="text-sm text-slate-800">
                                            {{ $report->model }}
                                        </span>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-3 py-2 align-middle text-center">
                                        <a href="{{ route('trial.detail', ['id' => $report->id]) }}"
                                           class="inline-flex items-center rounded-full border border-slate-300 bg-white px-3 py-1.5
                                                  text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                                  focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                                            View Details
                                        </a>
                                    </td>

                                    {{-- Status pill --}}
                                    <td class="px-3 py-2 align-middle text-center">
                                        @if (
                                            $report->autograph_1 &&
                                            $report->autograph_2 &&
                                            $report->autograph_3 &&
                                            $report->autograph_4 &&
                                            $report->autograph_6)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1
                                                       text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                DONE
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1
                                                       text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-500/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                NOT DONE
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500">
                                        Belum ada request trial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
