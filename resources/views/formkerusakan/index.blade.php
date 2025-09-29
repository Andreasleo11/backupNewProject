@extends('layouts.app')

@section('content')
    <h1>Form Kerusakan</h1>

    <!-- Button to trigger the modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">
        Tambah Laporan Kerusakan
    </button>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerReportModal">
        Generate Customer Report
    </button>

    <!-- Modal -->
    <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDataModalLabel">LAPORAN KERUSAKAN DAN PERMINTAAN PERBAIKAN
                        BARANG MILIK PELANGGAN / PENYEDIA EKSTERNAL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('laporan-kerusakan.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="customer" class="form-label">Customer</label>
                            <input type="text" class="form-control" id="customer" name="customer" required>
                        </div>
                        <div class="mb-3">
                            <label for="release_date" class="form-label">Release Date</label>
                            <input type="date" class="form-control" id="release_date" name="release_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                            <label for="proses" class="form-label">Proses</label>
                            <input type="text" class="form-control" id="proses" name="proses" required>
                        </div>
                        <div class="mb-3">
                            <label for="masalah" class="form-label">Masalah</label>
                            <input type="text" class="form-control" id="masalah" name="masalah" required>
                        </div>
                        <div class="mb-3">
                            <label for="sebab" class="form-label">Sebab</label>
                            <input type="text" class="form-control" id="sebab" name="sebab" required>
                        </div>
                        <div class="mb-3">
                            <label for="penanggulangan" class="form-label">Penanggulangan</label>
                            <input type="text" class="form-control" id="penanggulangan" name="penanggulangan" required>
                        </div>
                        <div class="mb-3">
                            <label for="pic" class="form-label">PIC</label>
                            <input type="text" class="form-control" id="pic" name="pic" required>
                        </div>
                        <div class="mb-3">
                            <label for="target" class="form-label">Target</label>
                            <input type="text" class="form-control" id="target" name="target" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customerReportModal" tabindex="-1" aria-labelledby="customerReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerReportModalLabel">Select Customer for Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="{{ route('laporan-kerusakan.report') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="customer" class="form-label">Customer</label>
                            <select class="form-select" name="customer" id="customer" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer }}">{{ $customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="release_date" class="form-label">Release Date</label>
                            <select class="form-select" name="release_date" id="release_date" required>
                                @foreach ($release_dates as $release_date)
                                    <option value="{{ $release_date }}">{{ $release_date }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-5">Summary of Reports</h2>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Release Date</th>
                <th>Document Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summaries as $index => $summary)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $summary->customer }}</td>
                    <td>{{ $summary->release_date }}</td>
                    <td>{{ $summary->doc_num }}</td>
                    <td>
                        <a href="{{ route('laporan-kerusakan.show', $summary->id) }}" class="btn btn-info btn-sm">View
                            Details</a>
                        <form action="{{ route('laporan-kerusakan.destroy', $summary->id) }}" method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this report?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
