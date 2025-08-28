@extends('layouts.app')

@section('content')
  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1">Form Keluar List</h1>
      </div>
      <div class="col-auto">
        <a href="{{ route('formkeluar.create') }}" class="btn btn-primary">+ Create</a>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="card mt-5">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-hover table-striped text-center mb-0">
            <thead>
              <tr>
                <th class="fw-semibold fs-5">No</th>
                <th class="fw-semibold fs-5">Nama</th>
                <th class="fw-semibold fs-5">Doc Num</th>
                <th class="fw-semibold fs-5">Action</th>
                <th class="fw-semibold fs-5">Status</th>
                <th class="fw-semibold fs-5">Description</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($formkeluar as $fk)
                <tr class="align-middle">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $fk->name }}</td>
                  <td>{{ $fk->doc_num }}</td>
                  <td>
                    <a href="{{ route('formkeluar.detail', ['id' => $fk->id]) }}"
                      class="btn btn-secondary">
                      <i class='bx bx-info-circle'></i> Detail
                    </a>
                  </td>
                  <td>
                    <!-- TODO: Implement the status -->
                  </td>
                  <td>
                    {{ $fk->alasan_izin_keluar }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
@endsection
