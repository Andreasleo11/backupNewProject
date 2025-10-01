@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-body p-0">
                        <h1>Delivery Menu for second
                        </h1>

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

                        <a href="{{ route('secondprocess4') }}" class="btn btn-secondary float-right"> Lanjut</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
