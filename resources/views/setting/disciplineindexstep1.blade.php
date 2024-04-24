@extends('layouts.app')

@section('content')
<style>
    .animated-button {
    animation: pulse 0.5s infinite;
    background-color: #00ff00; /* Green color */
    color: #ffffff; /* White text color */
    border-radius: 50%;
}

.animated-button:hover {
    background-color: #009900; /* Darker shade of green */
}


@keyframes pulse {
    0% {
        transform: scale(1);
        border-radius: 50%;
    }
    50% {
        transform: scale(1.4);
        border-radius: 50%;
    }
    100% {
        transform: scale(1);
        border-radius: 50%;
    }
}
</style>

<a href="{{ route('update.excel') }}" class="btn btn-primary">Lanjut Step 2 </a>

<div class="col-auto">
    <div class="row align-items-center">
    <div class="col-auto">
    <div class="form-label">Filter Bulan</div>
        </div>
            <div class="col-auto">
                <select name="filter_status" id="status-filter" class="form-select">
                     <option value="01" selected>January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
             </div>
        </div>
    </div>

<h1>Export Table ini dalam bentuk Excel dengan mengklik tombol yang bergerak di tabel<br>
</h1>

<section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>

    @foreach($employees as $employee)
            @include('partials.edit-discipline-modal')
    @endforeach

{{ $dataTable->scripts() }}

<script type="module">
    $(function() {
        let dataTable = window.LaravelDataTables["disciplinetable-table"];
        $('#status-filter').change(function() {
            let selectedMonth = $(this).val();
            console.log("Selected month:", selectedMonth); // Output the selected month to console

            // Extract the month part from the date format (yyyy-mm-dd)
            let formattedMonth = selectedMonth.padStart(2, '0'); // Pad single-digit months with 0
            console.log("Formatted month:", formattedMonth);

            // Filter by month column
            dataTable.column(4).search('-' + formattedMonth + '-', true, false).draw();
        });
    });  
</script>
@endsection