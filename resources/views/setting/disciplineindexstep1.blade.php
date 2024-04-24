@extends('layouts.app')

@section('content')
<style>
    .animated-button {
    animation: pulse 0.5s infinite;
    background-color: #00ff00; /* Green color */
    color: #ffffff; /* White text color */
    border-radius: 50%;
}

.animated-button:hover {
    background-color: #009900; /* Darker shade of green */
}


@keyframes pulse {
    0% {
        transform: scale(1);
        border-radius: 50%;
    }
    50% {
        transform: scale(1.4);
        border-radius: 50%;
    }
    100% {
        transform: scale(1);
        border-radius: 50%;
    }
}
</style>

<a href="{{ route('update.excel') }}" class="btn btn-primary">Lanjut Step 2 </a>

<h1>Export Table ini dalam bentuk Excel dengan mengklik tombol yang bergerak di tabel<br>
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

    @foreach($employees as $employee)
            @include('partials.edit-discipline-modal')
    @endforeach

{{ $dataTable->scripts() }}

@endsection