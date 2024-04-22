@extends('layouts.app')

@section('content')

<h1>PAGE FOR EVALUATION </h1>


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
@endsection