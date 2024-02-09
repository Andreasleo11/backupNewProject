@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Other head elements -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    {{ __('PR SECTION') }}
                </div>
            </div>
        </div>
    </div>  
</div>



<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.create') }}" class="btn btn-primary">Create PR </a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.view') }}" class="btn btn-primary">LIST PR </a>
    </div>
</div>


<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.monthly') }}" class="btn btn-primary">GENERATE MONTHLY PR </a>
    </div>
</div>


<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.monthlyprlist') }}" class="btn btn-primary">MONTHLY PR LIST</a>
    </div>
</div>


<div style="width: 30%; margin: auto;">
        <canvas id="departmentChart" width="10" height="10 "></canvas>
    </div>

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


</body>
</html>

@endsection