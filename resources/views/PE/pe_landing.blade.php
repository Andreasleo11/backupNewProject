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

            {{ __('PE PAGE') }}
          </div>
        </div>
      </div>
    </div>

  </div>

  {{--
<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('pe.trial') }}" class="btn btn-primary">FORM REQUEST TRIAL</a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('pe.formlist') }}" class="btn btn-primary">LIST FORM REQUEST</a>
    </div>
</div> --}}
@endsection
