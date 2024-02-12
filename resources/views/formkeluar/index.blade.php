@extends('layouts.app')

@section('content')

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    {{ __('FORM KELUAR') }}
                </div>
            </div>
        </div>
    </div>  
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('formkeluar.create') }}" class="btn btn-primary">Create Form Keluar </a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('formkeluar.view') }}" class="btn btn-primary">LIST Form Keluar </a>
    </div>
</div>
@endsection