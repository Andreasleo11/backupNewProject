@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        @include('partials.alert-success-error')

        <div class="card shadow-lg">
            <div class="card-body">
                <h4 class="my-2 fw-bold text-primary">Import Annual Leave Quota</h4>
                <div class="border-bottom border-primary mt-3 mb-4 col-md-5"></div>
                <form action="{{ route('import.annual-leave-quota') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload Excel File</label>
                        <input type="file" class="form-control border-primary" name="file" id="file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload & Update
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
