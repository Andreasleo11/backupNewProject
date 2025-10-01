@extends('layouts.app')

@section('content')

    <section aria-label="header">
        <h2 class="mb-5">Important Docs</h2>
    </section>
    @if ($importantDocs->isNotEmpty())
        <section aria-label="content">
            <div class="container">

                <section aria-label="cards">
                    <div class="row justify-content-center mb-5 align-items-center">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div>
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="container">
                                <h3 class="mb-3 text-center">Expired Documents</h3>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-striped mb-0 text-center">
                                                <thead>
                                                    <tr>
                                                        <th class="fs-5 align-middle py-3" scope="col">No</th>
                                                        <th class="fs-5 align-middle py-3" scope="col">Name</th>
                                                        <th class="fs-5 align-middle py-3" scope="col">Expired Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($importantDocs as $importantDoc)
                                                        <tr>
                                                            <td class="align-middle">{{ $loop->iteration }}</td>
                                                            <td class="align-middle">{{ $importantDoc->name }}</td>
                                                            <td class="align-middle">
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
                        </div>
                    </div>
                </section>
            </div>
        </section>
    @else
        <div class="text-center h4 text-secondary">
            No data
        </div>
    @endif

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
