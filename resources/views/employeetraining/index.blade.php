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

                    {{ __('Employee Training List ') }}
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('training.create') }}" class="btn btn-primary">Create</a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('training.view') }}" class="btn btn-primary">List</a>
    </div>
</div>


<a href="http://116.254.114.93:8080/edp/users/register-complaint.php" > CLICK UNTUK SPK </a>

@endsection