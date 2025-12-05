@extends('new.layouts.app')

@section('content')

    <div x-data="{ openModal: false }" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('testing.request') }}" class="mb-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                {{-- Filter by Stock --}}
                <div>
                    <label for="filterStock" class="block text-sm font-medium text-gray-700">
                        Filter by Stock
                    </label>
                    <select id="filterStock" name="stock_id"
                        class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                               focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Stocks</option>
                        @foreach ($masterStocks as $masterStock)
                            <option value="{{ $masterStock->id }}"
                                {{ request('stock_id') == $masterStock->id ? 'selected' : '' }}>
                                {{ $masterStock->stock_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter by Department --}}
                <div>
                    <label for="filterDepartment" class="block text-sm font-medium text-gray-700">
                        Filter by Department
                    </label>
                    <select id="filterDepartment" name="dept_id"
                        class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                               focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('dept_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter by Month --}}
                <div>
                    <label for="filterMonth" class="block text-sm font-medium text-gray-700">
                        Filter by Month
                    </label>
                    <input id="filterMonth" type="month" name="month" value="{{ request('month') }}"
                        class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2 md:justify-end">
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Apply Filters
                    </button>

                    @if (request()->hasAny(['stock_id', 'dept_id', 'month']))
                        <a href="{{ route('testing.request') }}"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Button trigger modal --}}
        <div class="mb-4">
            <button type="button" @click="openModal = true"
                class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                Add Stock Request
            </button>
        </div>

        {{-- Modal for adding Stock Request (Alpine + Tailwind) --}}
        <div x-cloak x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div @click.away="openModal = false" class="w-full max-w-lg rounded-lg bg-white shadow-xl">
                <form method="POST" action="{{ route('stockrequest.store') }}">
                    @csrf
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-900">
                            Add Stock Request
                        </h2>
                        <button type="button" @click="openModal = false"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <span class="sr-only">Close</span>
                            ✕
                        </button>
                    </div>

                    <div class="px-4 py-4 space-y-3">
                        {{-- Stock Master --}}
                        <div>
                            <label for="masterStock" class="block text-sm font-medium text-gray-700">
                                Stock Master
                            </label>
                            <select id="masterStock" name="masterStock" required
                                class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($masterStocks as $masterStock)
                                    <option value="{{ $masterStock->id }}">
                                        {{ $masterStock->stock_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Department --}}
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700">
                                Department
                            </label>
                            <select id="department" name="department" required
                                class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stock Requested --}}
                        <div>
                            <label for="stockRequest" class="block text-sm font-medium text-gray-700">
                                Stock Requested
                            </label>
                            <input id="stockRequest" type="number" name="stockRequest" required
                                class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Month --}}
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700">
                                Month
                            </label>
                            <input id="month" type="date" name="month" required
                                class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Remark --}}
                        <div>
                            <label for="remark" class="block text-sm font-medium text-gray-700">
                                Remark
                            </label>
                            <input id="remark" type="text" name="remark"
                                class="mt-1 px-3 py-2 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-gray-200">
                        <button type="button" @click="openModal = false"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900">
                            Close
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="mt-4 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">
                    Stock Requests
                </h2>
            </div>

            <div class="overflow-x-auto">
                @if ($datas->isEmpty())
                    <p class="px-4 py-6 text-center text-sm text-gray-500">
                        No data
                    </p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Stock Type
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Stock Name
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Dept No Request
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Dept Name Request
                                </th>
                                <th
                                    class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Stock Requested
                                </th>
                                <th
                                    class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Available Stock
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Month
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Remark
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($datas as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                        {{ $data->stockRelation->stockType->name }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap font-mono text-gray-900">
                                        {{ $data->stockRelation->stock_code }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->deptRelation->dept_no }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->deptRelation->name }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-gray-900">
                                        {{ $data->request_quantity }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-gray-900">
                                        {{ $data->quantity_available }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->month }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        {{ $data->remark }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

@endsection
