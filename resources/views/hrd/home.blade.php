@extends('new.layouts.app')

@section('page-title', 'Important Documents')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        @if ($importantDocs->isNotEmpty())
            <div class="grid lg:grid-cols-12 gap-6 items-start">
                {{-- Pie Chart --}}
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <canvas id="pieChart" class="w-full"></canvas>
                    </div>
                </div>

                {{-- Expired Documents Table --}}
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 pb-0">
                            <h3 class="text-lg font-semibold text-slate-900 text-center">Expired Documents</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-center divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">No</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Name</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Expired Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($importantDocs as $importantDoc)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 text-slate-900">{{ $loop->iteration }}</td>
                                            <td class="px-4 py-3 text-slate-900">{{ $importantDoc->name }}</td>
                                            <td class="px-4 py-3 text-slate-900">
                                                {{ \Carbon\Carbon::parse($importantDoc->expired_date)->format('d-m-Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-xl text-slate-500">No data</p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@push('extraJs')
    <script>
        const ctx = document.getElementById('pieChart');

        // Get the data passed from the controller
        var importantDocsCount = {{ $importantDocs->count() }};
        var importantDocs2Count = {{ $importantDocs2->count() }};

        const data = {
            labels: ['Less than equal 2 months', 'Greater than 2 months'],
            datasets: [{
                label: 'Jumlah',
                data: [importantDocsCount, importantDocs2Count],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                ],
                hoverOffset: 4
            }]
        };

        const config = {
            type: 'pie',
            data: data,
        };


        new Chart(ctx, config);
    </script>
@endpush
