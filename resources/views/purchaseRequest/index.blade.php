@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row d-flex">
    <div class="col">
        <h1 class="h1">Purchase Requisition List</h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('purchaserequest.create') }}" class="btn btn-primary">Create PR </a>
    </div>
</div>

<section class="header">
    <div class="row">
        <div class="col">

        </div>
    </div>
</section>


<section class="content">
<div class="card mt-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped text-center mb-0">
                <thead>
                    <tr>
                        <th class="fw-semibold fs-5">No</th>
                        <th class="fw-semibold fs-5">Date PR</th>
                        <th class="fw-semibold fs-5">To Department</th>
                        <th class="fw-semibold fs-5">PR No </th>
                        <th class="fw-semibold fs-5">Supplier</th>
                        <th class="fw-semibold fs-5">Action</th>
                        <th class="fw-semibold fs-5">Status</th>
                        <th class="fw-semibold fs-5">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseRequests as $pr)
                        <tr class="align-middle">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pr->date_pr }}</td>
                            <td>{{ $pr->to_department }}</td>
                            <td>{{ $pr->pr_no }}</td>
                            <td>{{ $pr->supplier }}</td>
                            <td>
                                <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="btn btn-secondary">
                                    <i class='bx bx-info-circle' ></i> Detail
                                </a>
                            </td>
                            <td>
                                @if($pr->status === 0)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR PREPARATION</span>

                                @elseif($pr->status === 1)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT HEAD</span>

                                @elseif($pr->status === 2)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR VERIFICATION</span>
                                @elseif($pr->attachment === null)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ATTACHMENT</span>
                                @elseif($pr->status === 3)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DIRECTOR</span>
                                @elseif($pr->status === 4)
                                    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
                                @elseif($pr->status === 5)
                                <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>

                                @endif
                            </td>
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No Data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>


{{-- <div style="width: 30%;" class="m-auto mt-4">
    <canvas id="departmentChart" width="10" height="10 "></canvas>
</div> --}}

@endsection

@push('extraJs')
<script>
    // Access data passed from the controller
    var labels = {!! $labels !!};
    var counts = {!! $counts !!};

    // Render the chart
    var ctx = document.getElementById('departmentChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Purchase Requests by Department',
                data: counts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    min:0,
                    max:10
                }
            }
        }
    });
    </script>
@endpush
