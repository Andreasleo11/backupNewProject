@extends('layouts.app')

@section('content')
    <h1>PAGE FOR EVALUATION </h1>

    <form method="POST" action="{{ route('DeleteEvaluation') }}">
        @csrf
        @method('DELETE')
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
                <div class="col-auto">
                    <button type="submit" class="btn btn-danger">Delete Data</button>
                </div>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('UpdateEvaluation') }}" enctype="multipart/form-data">
        @csrf
        <label for="excel_files">Upload Excel files:</label>
        <input type="file" name="excel_files[]" id="excel_files" onchange="displayUploadedFiles()" multiple>
        <br>
        <button type="submit">Submit</button>
    </form>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>

    {{ $dataTable->scripts() }}

    <script type="module">
        $(function() {
            let dataTable = window.LaravelDataTables["evaluationdata-table"];
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
