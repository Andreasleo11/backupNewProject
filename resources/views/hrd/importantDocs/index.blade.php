@extends('layouts.app')

@section('content')

{{-- @dd($importantDocs) --}}
<section class="header">
    <div class="d-flex row-flex">
        <div class="h2 me-auto">Important Documents</div>
        <div>
            <a class="btn btn-primary" href="{{ route('hrd.importantDocs.create') }}">+ Add Report</a>
        </div>
    </div>
</section>

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('hrd.home')}}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Important Documents</li>
        </ol>
    </nav>
</section>

@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <p>{{ $message }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($importantDocs->isNotEmpty())
<section class="content">
    <div class="card mt-5">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0 text-center table-striped">
                <thead>
                    <tr>
                      <th class="fs-5 align-middle py-3" scope="col">No</th>
                      <th class="fs-5 align-middle py-3" scope="col">Document ID</th>
                      <th class="fs-5 align-middle py-3" scope="col">Name</th>
                      <th class="fs-5 align-middle py-3" scope="col">Type</th>
                      <th class="fs-5 align-middle py-3" scope="col">Expired Date</th>
                      <th class="fs-5 align-middle py-3" scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($importantDocs as $importantDoc)
                        <tr>
                            <td class="align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">{{ $importantDoc->document_id }}</td>
                            <td class="align-middle">{{ $importantDoc->name }}</td>
                            <td class="align-middle">{{ $importantDoc->type->name }}</td>
                            @php
                                $expiryThreshold = Carbon\Carbon::now()->addMonths(2);
                                $date = Carbon\Carbon::parse($importantDoc->expired_date);
                                $daysDifference = Carbon\Carbon::now()->diffInDays(Carbon\Carbon::parse($importantDoc->expired_date));
                            @endphp
                            <td class="align-middle {{ ($date < $expiryThreshold) ? "text-danger": "" }}">
                                {{ $date->format('d-m-Y') }}
                            </td>
                            <td class="align-middle">
                                <form action="{{route('hrd.importantDocs.delete',$importantDoc->id)}}" method="POST">
                                    <a href="{{route('hrd.importantDocs.detail', $importantDoc->id)}}" class="btn btn-secondary me-1">
                                        <div class="col d-flex align-middle">
                                            <box-icon name='info-circle' color="white" class="pb-1"></box-icon>
                                            <span class="ms-1">Detail</span>
                                        </div>
                                    </a>
                                    <a href="{{route('hrd.importantDocs.edit', $importantDoc->id)}}" class="btn btn-primary me-1">
                                        <div class="col d-flex">
                                            <box-icon name='edit' color="white" class="pb-1"></box-icon>
                                            <span class="ms-1">Edit</span>
                                        </div>
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <div class="col d-flex">
                                            <box-icon name='trash' color="white" class="pb-1"></box-icon>
                                            <span class="ms-1">Delete</span>
                                        </div>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                  </tbody>
            </table>
        </div>
    </div>
</section>
@else
<div class="text-center h4 text-secondary">
    No data
</div>
@endif


@endsection
