@extends('new.layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
        $showCreateButton = $authUser->department->name !== 'MANAGEMENT';
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

        {{-- Header + primary actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1">
                        <li>
                            <span class="text-slate-400">SPK</span>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li class="font-medium text-slate-700">List</li>
                    </ol>
                </nav>

                <h1 class="text-xl font-semibold text-slate-900">SPK List</h1>
                <p class="mt-1 text-xs text-slate-500">
                    Monitor and follow up SPK (service order) reports across departments.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('spk.monthlyreport') }}"
                   class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    Monthly report
                </a>

                @if ($showCreateButton)
                    <a href="{{ route('spk.create') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        New report
                    </a>
                @endif
            </div>
        </div>

        {{-- Filter panel --}}
        <div x-data="spkFilter()" class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <form method="GET"
                  action="{{ route('spk.index') }}"
                  class="p-4 space-y-3">

                <div class="flex flex-wrap gap-3">
                    {{-- Column --}}
                    <div class="w-full sm:w-48">
                        <label for="filter_column" class="block text-xs font-medium text-slate-600 mb-1">
                            Filter column
                        </label>
                        <select id="filter_column"
                                name="filter_column"
                                x-model="column"
                                class="block py-2 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select column --</option>
                            <option value="no_dokumen">No. Dokumen</option>
                            <option value="pelapor">Pelapor</option>
                            <option value="tanggal_lapor">Tanggal Lapor</option>
                            <option value="judul_laporan">Judul Laporan</option>
                            <option value="pic">PIC</option>
                        </select>
                    </div>

                    {{-- Action --}}
                    <div class="w-full sm:w-44">
                        <label for="filter_action" class="block text-xs font-medium text-slate-600 mb-1">
                            Action
                        </label>
                        <select id="filter_action"
                                name="filter_action"
                                x-model="action"
                                class="block py-2 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select action --</option>
                            <option value="contains">Contains</option>
                            <option value="equals">Equals</option>
                            <option value="between" :disabled="!isDateColumn">Between</option>
                            <option value="greater_than" :disabled="!isDateColumn">Greater than</option>
                            <option value="less_than" :disabled="!isDateColumn">Less than</option>
                        </select>
                    </div>

                    {{-- Value 1 --}}
                    <div class="flex-1 min-w-[180px]">
                        <label for="filter_value" class="block text-xs font-medium text-slate-600 mb-1">
                            Filter value
                        </label>
                        <input id="filter_value"
                               name="filter_value"
                               x-model="value"
                               :type="isDateColumn ? 'date' : 'text'"
                               class="block py-2 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Value 2 (for between) --}}
                    <div class="flex-1 min-w-[180px]" x-show="showSecondValue" x-cloak>
                        <label for="filter_value_2" class="block text-xs font-medium text-slate-600 mb-1">
                            Filter value (to)
                        </label>
                        <input id="filter_value_2"
                               name="filter_value_2"
                               x-model="value2"
                               :type="isDateColumn ? 'date' : 'text'"
                               class="block py-2 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            Apply filter
                        </button>

                        @if (request()->filled('filter_column') ||
                             request()->filled('filter_action') ||
                             request()->filled('filter_value') ||
                             request()->filled('filter_value_2'))
                            <a href="{{ route('spk.index') }}"
                               class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Active filter summary --}}
                @if (request()->anyFilled(['filter_column', 'filter_action', 'filter_value', 'filter_value_2']))
                    <p class="text-[11px] text-slate-400 mt-1">
                        Showing results filtered by
                        <span class="font-semibold text-slate-600">
                            {{ request('filter_column') }}
                            {{ request('filter_action') }}
                            "{{ request('filter_value') }}"
                            @if (request('filter_value_2'))
                                – "{{ request('filter_value_2') }}"
                            @endif
                        </span>
                    </p>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">No. Dokumen</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Pelapor</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Requested by</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Tanggal Lapor</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Judul Laporan</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">PIC</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2 align-top font-mono text-[13px] text-slate-800">
                                    {{ $report->no_dokumen }}
                                </td>
                                <td class="px-4 py-2 align-top text-slate-700">
                                    {{ $report->pelapor }}
                                </td>
                                <td class="px-4 py-2 align-top text-slate-700">
                                    {{ $report->requested_by }}
                                </td>
                                <td class="px-4 py-2 align-top whitespace-nowrap text-slate-700">
                                    @formatDate($report->tanggal_lapor)
                                </td>
                                <td class="px-4 py-2 align-top max-w-xs">
                                    <div class="line-clamp-2 text-sm text-slate-800">
                                        {{ $report->judul_laporan }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 align-top text-slate-700">
                                    {{ $report->pic ?? 'Not assigned' }}
                                </td>
                                <td class="px-4 py-2 align-top">
                                    @include('partials.spk-status', [
                                        'status' => $report->status_laporan,
                                        'is_urgent' => $report->is_urgent,
                                    ])
                                </td>
                                <td class="px-4 py-2 align-top text-right">
                                    @include('partials.spk-actions')
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No reports found for the current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-4 py-3 flex items-center justify-end">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function spkFilter() {
            return {
                column: @json(request('filter_column')),
                action: @json(request('filter_action')),
                value: @json(request('filter_value')),
                value2: @json(request('filter_value_2')),

                get isDateColumn() {
                    return this.column === 'tanggal_lapor';
                },
                get showSecondValue() {
                    return this.isDateColumn && this.action === 'between';
                },
            };
        }
    </script>
@endpush
