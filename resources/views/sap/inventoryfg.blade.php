@extends('layouts.app')

@section('content')
  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1">LIST INVENTORY FG </h1>
      </div>
    </div>
  </section>

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
@endsection

@push('extraJs')
@endpush
