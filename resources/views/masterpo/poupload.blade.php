@extends('layouts.app')

@section('content')
<div class="container">

    <h1>Upload PO File</h1>

    <div class="card shadow-sm p-4">
        <form action="{{ route('pdf.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- PO Number Input -->
            <div class="form-group mb-3">
                <label for="po_number" class="form-label">PO Number</label>
                <input type="number" name="po_number" id="po_number" class="form-control" required>
            </div>

            <!-- PDF File Input -->
            <div class="form-group mb-3">
                <label for="pdf_file" class="form-label">Choose PDF File</label>
                <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Upload PDF</button>
        </form>
    </div>
</div>
@endsection
