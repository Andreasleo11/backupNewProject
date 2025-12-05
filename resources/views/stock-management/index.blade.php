@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center text-sm text-gray-500 space-x-1">
                <li>
                    <a href="{{ route('mastertinta.index') }}" class="font-medium text-gray-600 hover:text-indigo-600">
                        Management Stock
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

        {{-- Header + Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Management Stock
            </h1>

            @if (Auth::user()->department->name !== 'MANAGEMENT')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('transaction.list') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        List Transaction
                    </a>

                    <a href="{{ route('testing.request') }}"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                        Request Stock
                    </a>

                    <a href="{{ route('mastertinta.transaction.index') }}"
                        class="inline-flex items-center rounded-md bg-slate-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                        Edit Stock
                    </a>
                </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="mt-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Stock Type ID
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Dept ID
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Stock Code
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Stock Description
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Stock Quantity
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($datas as $data)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                    {{ $data->stockType->name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                    {{ $data->department->dept_no }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap font-mono text-gray-900">
                                    {{ $data->stock_code }}
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    {{ $data->stock_description }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-900">
                                    {{ $data->stock_quantity }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No stock data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
