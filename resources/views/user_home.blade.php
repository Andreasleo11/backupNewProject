@extends('layouts.app')

@section('content')
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

                    {{ __('Welcome Back Workers') }}
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('header.create') }}" class="btn btn-primary">Verification Report</a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('report.view') }}" class="btn btn-primary">View Report</a>
    </div>
</div>

@endsection
