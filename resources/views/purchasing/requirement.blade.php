@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1>Purchasing Requirement Section</h1>
            </div>
        </div>
    </section>

    <div class="mb-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal:</label>
        <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="tanggal_akhir" class="form-label">Tanggal akhir:</label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" required>
    </div>

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

    <a href="{{ route('purchasingrequirement.detail') }}" class="btn btn-secondary float-right">Detail</a>
@endsection
