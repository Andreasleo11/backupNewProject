@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4 text-primary">📤 Upload Daily Report</h2>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('daily-report.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="report_file" class="form-label">
                            Upload File Laporan (Excel / CSV)
                        </label>
                        <input type="file" name="report_file" id="report_file" accept=".xlsx,.csv,.txt" required
                            class="form-control @error('report_file') is-invalid @enderror">

                        @error('report_file')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            ⬆️ Upload & Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
