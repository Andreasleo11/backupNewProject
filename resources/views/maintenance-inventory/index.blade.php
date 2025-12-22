@extends('new.layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ showUsernameModal: false }" x-cloak>
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
                    List
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                    Maintenance Inventory Reports
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Lihat dan kelola laporan maintenance per periode dan tahun.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 sm:justify-end">
                <button type="button" @click="showUsernameModal = true"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Username Statuses
                </button>

                <a href="{{ route('maintenance.inventory.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    New Report
                </a>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="mb-4 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <form method="GET" action="{{ route('maintenance.inventory.index') }}"
                class="px-4 py-4 sm:px-6 sm:py-5 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Filter
                    </h2>
                    @if (request()->filled('periode') || request()->filled('year'))
                        <a href="{{ route('maintenance.inventory.index') }}"
                            class="text-xs font-medium text-red-600 hover:text-red-700">
                            Clear filters
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Periode --}}
                    <div>
                        <label for="periode" class="block text-sm font-medium text-gray-700">
                            Periode
                        </label>
                        <select name="periode" id="periode"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All</option>
                            <option value="1" {{ request('periode') == 1 ? 'selected' : '' }}>
                                1 (Januari – April)
                            </option>
                            <option value="2" {{ request('periode') == 2 ? 'selected' : '' }}>
                                2 (Mei – Agustus)
                            </option>
                            <option value="3" {{ request('periode') == 3 ? 'selected' : '' }}>
                                3 (September – Desember)
                            </option>
                        </select>
                    </div>

                    {{-- Year --}}
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">
                            Year
                        </label>
                        <select name="year" id="year"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                       focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
                                <option value="{{ $i }}"
                                    {{ request('year', date('Y')) == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Button --}}
                    <div class="flex items-end">
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-4 sm:px-6 sm:py-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    ID</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Nomor Dokumen</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Username</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Periode</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Revision Date</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($reports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 align-top text-gray-700">
                                        {{ $report->id }}
                                    </td>
                                    <td class="px-3 py-2 align-top text-gray-900 font-medium">
                                        {{ $report->no_dokumen }}
                                    </td>
                                    <td class="px-3 py-2 align-top text-gray-700">
                                        {{ $report->master->username }}
                                    </td>
                                    <td class="px-3 py-2 align-top text-gray-700">
                                        {{ $report->periode_caturwulan }}
                                    </td>
                                    <td class="px-3 py-2 align-top text-gray-700">
                                        {{ $report->revision_date }}
                                    </td>
                                    <td class="px-3 py-2 align-top text-right">
                                        <div class="inline-flex gap-2">
                                            <a href="{{ route('maintenance.inventory.show', $report->id) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                Detail
                                            </a>
                                            <a href="{{ route('maintenance.inventory.edit', $report->id) }}"
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                        No data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Username Status Modal (Alpine + Tailwind) --}}
        <div x-show="showUsernameModal" x-transition.opacity x-transition.scale
            @keydown.escape.window="showUsernameModal = false"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
            <div @click.away="showUsernameModal = false"
                class="w-full max-w-md rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900" id="usernameStatusModalLabel">
                        Username Statuses
                    </h2>
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-transparent p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="showUsernameModal = false">
                        <span class="sr-only">Close</span>
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414
                                         1.414L11.414 10l4.293 4.293a1 1 0 01-1.414
                                         1.414L10 11.414l-4.293 4.293a1 1 0
                                         01-1.414-1.414L8.586 10 4.293 5.707a1 1 0
                                         010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-3 max-h-80 overflow-y-auto">
                    @if (count($usernameStatuses))
                        <ul class="divide-y divide-gray-100">
                            @foreach ($usernameStatuses as $username => $status)
                                <li class="flex items-center justify-between py-2">
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ $username }}
                                    </span>
                                    @if ($status === 'yes')
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Yes
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            No
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">
                            Tidak ada data username status.
                        </p>
                    @endif
                </div>

                <div class="flex justify-end px-4 py-3 border-t border-gray-100">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="showUsernameModal = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
