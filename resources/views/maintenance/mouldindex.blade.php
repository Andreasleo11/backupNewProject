@extends('layouts.app')

@section('content')
  <h1>Index untuk MouldDown</h1>

  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1">Mould Down</h1>
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
    @include('partials.add-new-moulddown-modal')
    <a class="btn btn-secondary float-right" data-bs-target="#add-new-mould" data-bs-toggle="modal"> add
    </a>
  </section>

  {{ $dataTable->scripts() }}
@endsection
