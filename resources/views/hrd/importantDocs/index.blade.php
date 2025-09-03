@extends('layouts.app')

@section('content')
  {{-- @dd($importantDocs) --}}
  <section class="header">
    <div class="d-flex row-flex">
      <div class="h2 me-auto">Important Documents</div>
      <div>
        <a class="btn btn-primary" href="{{ route('hrd.importantDocs.create') }}">+ Add Document</a>
      </div>
    </div>
  </section>

  <section>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('hrd.home') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Important Documents</li>
      </ol>
    </nav>
  </section>

  @include('partials.alert-success-error')

  @foreach ($importantDocs as $importantDoc)
    @include('partials.delete-confirmation-modal', [
        'id' => $importantDoc->id,
        'route' => 'hrd.importantDocs.delete',
        'title' => 'Delete Confirmation',
        'body' =>
            'Are you sure want to delete <strong>' .
            $importantDoc->name .
            ' ' .
            $importantDoc->document_id .
            '</strong>?',
    ])
  @endforeach
  <section class="content">
    <div class="card mt-5">
      <div class="card-body">
        {{ $dataTable->table() }}
      </div>
    </div>
  </section>
@endsection

@push('extraJs')
  {{ $dataTable->scripts() }}
@endpush
