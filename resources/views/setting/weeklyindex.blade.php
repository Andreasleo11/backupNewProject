@extends('layouts.app')

@section('content')
    <h1>WEEKLY EVALUATION </h1>

    <form method="POST" action="{{ route('WeeklyUpdateEvaluation') }}" enctype="multipart/form-data">
        @csrf
        <label for="excel_files">Upload Excel files:</label>
        <input type="file" name="excel_files[]" id="excel_files" onchange="displayUploadedFiles()" multiple>
        <br>
        <button type="submit">Submit</button>
    </form>
@endsection
