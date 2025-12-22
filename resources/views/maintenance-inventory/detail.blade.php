<!-- resources/views/maintenance-inventory/show.blade.php -->
@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1 text-sm text-gray-500">
                <li>
                    <a href="{{ route('maintenance.inventory.index') }}"
                        class="font-medium text-gray-600 hover:text-indigo-600">
                        Maintenance Inventory Reports
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="font-medium text-gray-900">
                    Detail
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                    Maintenance Inventory Report
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan laporan dan detail pengecekan untuk master inventory terkait.
                </p>
            </div>

            <div class="flex gap-2 sm:justify-end">
                <a href="{{ route('maintenance.inventory.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Back to list
                </a>
            </div>
        </div>

        {{-- Report Summary Card --}}
        <div class="mb-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">
                    Report Details
                </h2>
                <span
                    class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                    ID: {{ $report->id }}
                </span>
            </div>
            <div class="px-4 py-4 sm:px-6 sm:py-5">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Document Number</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $report->no_dokumen }}
                        </dd>
                    </div>

                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Master Inventory</dt>
                        <dd class="font-medium text-gray-900">
                            {{-- kalau ada relasi master --}}
                            @if ($report->relationLoaded('master') || $report->master ?? false)
                                {{ $report->master->username ?? '-' }}
                                @if (!empty($report->master->ip_address))
                                    <span class="text-xs text-gray-500">— {{ $report->master->ip_address }}</span>
                                @endif
                            @else
                                ID: {{ $report->master_id }}
                            @endif
                        </dd>
                    </div>

                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Revision Date</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $report->revision_date ?? '-' }}
                        </dd>
                    </div>

                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Periode Caturwulan</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $report->periode_caturwulan ?? '-' }}
                        </dd>
                    </div>

                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Created At</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $report->created_at }}
                        </dd>
                    </div>

                    <div class="space-y-0.5">
                        <dt class="text-gray-500">Updated At</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $report->updated_at }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Detail Maintenance Reports --}}
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">
                Detail Maintenance Reports
            </h2>
            @if (!$report->detail->isEmpty())
                <p class="text-xs text-gray-500">
                    Total items: {{ $report->detail->count() }}
                </p>
            @endif
        </div>

        @if ($report->detail->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-white px-4 py-6 sm:px-6 text-center">
                <p class="text-sm font-medium text-gray-700">
                    No details available.
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Laporan ini belum memiliki item pengecekan detail.
                </p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500">
                        Tabel berikut menampilkan kondisi masing-masing kategori, checker, dan remark.
                    </p>
                </div>
                <div class="px-4 py-3 sm:px-6 sm:py-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Category Name
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Condition
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Checked By
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Remark
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Created At
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Updated At
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($report->detail as $detail)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 align-top text-gray-900 font-medium">
                                            {{ $detail->typecategory->name ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            @php
                                                $cond = $detail->condition;
                                            @endphp
                                            @if ($cond === 'good')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Good
                                                </span>
                                            @elseif ($cond === 'bad')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                    Bad
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500">
                                                    {{ $cond ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 align-top text-gray-800">
                                            {{ $detail->checked_by ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 align-top text-gray-700">
                                            {{ $detail->remark ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 py-2 align-top text-gray-600 text-xs">
                                            {{ $detail->created_at }}
                                        </td>
                                        <td class="px-3 py-2 align-top text-gray-600 text-xs">
                                            {{ $detail->updated_at }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
