@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h4>Import Jabatan Update</h4>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ url('/import-jabatan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Upload Excel/CSV</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
@endsection
