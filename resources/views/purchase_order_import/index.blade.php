@extends('layouts.app')
@section('content')
    <h2 class="fs-4 fw-semibold mb-4">Upload Excel File</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('purchase_orders.import.import') }}" method="POST" enctype="multipart/form-data"
        class="p-4 border rounded shadow-sm bg-light">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Choose Excel File</label>
            <input type="file" name="file" id="file" class="form-control">
            @error('file')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
@endsection
