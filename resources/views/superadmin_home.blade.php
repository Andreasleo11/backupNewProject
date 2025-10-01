@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('update.dept') }}" class="btn btn-primary">Update Dept Yayasan</a>
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

                        {{ __('Hello monseiurs') }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
