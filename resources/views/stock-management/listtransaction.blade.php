@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            {{-- Header --}}
            <div class="px-4 py-3 border-b border-gray-100 sm:px-6">
                <h1 class="text-lg font-semibold text-gray-900">
                    Transaction List
                </h1>
            </div>

            <div class="px-4 py-4 sm:px-6 sm:py-5">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route('transaction.list') }}"
                    class="space-y-3 sm:space-y-0 sm:flex sm:items-end sm:justify-between mb-4">

                    <div class="w-full sm:max-w-xs">
                        <label for="stock_id" class="block text-sm font-medium text-gray-700">
                            Stock ID
                        </label>
                        <select id="stock_id" name="stock_id"
                            class="mt-1 block w-full rounded-md px-3 py-2 border-gray-300 shadow-sm text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Stock</option>
                            @foreach ($masterStocks as $masterStock)
                                <option value="{{ $masterStock->id }}"
                                    {{ request('stock_id') == $masterStock->id ? 'selected' : '' }}>
                                    {{ $masterStock->stock_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Filter
                        </button>

                        <a href="{{ route('transaction.list') }}"
                            class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- Transaction Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    ID
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Unique Code
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Stock Code
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Department
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    In Time
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Out Time
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Receiver
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-xs uppercase tracking-wide text-gray-600">
                                    Remark
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($datas as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                        {{ $data->id }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                        {{ $data->unique_code }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap font-mono text-gray-900">
                                        {{ $data->historyTransaction->stock_code }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->deptRelation->name ?? '' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->in_time }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->out_time }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700">
                                        {{ $data->receiver }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        {{ $data->remark }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Optional: pagination --}}
                @if (method_exists($datas, 'links'))
                    <div class="mt-4">
                        {{ $datas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
