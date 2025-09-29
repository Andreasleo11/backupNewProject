@extends('layouts.app')

@section('content')
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

    <a href="{{ route('reminderindex') }}" class="btn btn-secondary float-right">Kembali </a>
@endsection
