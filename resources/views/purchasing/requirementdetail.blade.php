@extends('layouts.app')

@section('content')
    <h1> isi tabel detail</h1>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>

    </section>

    {{ $dataTable->scripts() }}

    <a href="{{ route('purchasingrequirement.index') }}" class="btn btn-secondary float-right">Kembali
    </a>
@endsection
