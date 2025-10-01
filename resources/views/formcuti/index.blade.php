@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Form Cuti</h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('formcuti.create') }}" class="btn btn-primary">+ Create</a>
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
                                <th class="fw-semibold fs-5">Doc Num</th>
                                <th class="fw-semibold fs-5">No Karyawan</th>
                                <th class="fw-semibold fs-5">Tanggal Permohonan</th>
                                <th class="fw-semibold fs-5">Action</th>
                                <th class="fw-semibold fs-5">Status</th>
                                <th class="fw-semibold fs-5">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($formcuti as $fc)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $fc->doc_num }}</td>
                                    <td>{{ $fc->no_karyawan }}</td>
                                    <td>{{ $fc->tanggal_permohonan }}</td>
                                    <td>
                                        <a href="{{ route('formcuti.detail', ['id' => $fc->id]) }}"
                                            class="btn btn-secondary">
                                            <i class='bx bx-info-circle'></i> Detail
                                        </a>
                                    </td>
                                    <td>
                                        @if ($fc->is_accept == 1)
                                            <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
                                        @elseif($fc->is_accept == null)
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING</span>
                                        @else
                                            <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
                                        @endif
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
