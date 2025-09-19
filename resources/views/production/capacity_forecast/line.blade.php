@extends('layouts.app')

@section('content')
  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1"> Capacity By Forecast (LINE SECTION) </h1>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="card mt-5">
      <div class="card-body p-0">
        <div class="table-responsive">
          {{ $dataTable->table() }}
        </div>
      </div>
    </div>
    <a href="{{ route('capacityforecastindex') }}" class="btn btn-secondary float-right"> Back</a>
  </section>

  {{ $dataTable->scripts() }}
@endsection
