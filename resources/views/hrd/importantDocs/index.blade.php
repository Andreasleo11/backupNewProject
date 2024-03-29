@extends('layouts.app')

@section('content')
<section class="header">
    <!-- Modal Add Report -->
    @include('partials.add_important_doc')

    <div class="d-flex mb-3 row-flex">
        <div class="h2 p-2 me-auto">Important Documents</div>
        <div>

            {{-- <div class="btn btn-primary" type="submit" data-bs-toggle="modal" data-bs-target="#add-important-doc-modal"> + Add Report</div> --}}

            <a class="btn btn-primary" href="{{ route('hrd.importantDocs.create') }}">+ Add Report</a>
        </div>
    </div>
</section>

@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
@endif

<section class="content">
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0 text-center table-striped">
                <thead>
                    <tr>
                      <th class="fs-5" scope="col">No</th>
                      <th class="fs-5" scope="col">Name</th>
                      <th class="fs-5" scope="col">Type</th>
                      <th class="fs-5" scope="col">Expired Date</th>
                      <th class="fs-5" scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($important_docs as $important_doc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $important_doc->name }}</td>
                            {{-- @if ($important_doc->type_id === 0)
                                <td>Other</td>
                            @endif --}}
                            <td>{{ $important_doc->type->name }}</td> <!-- unsolved using orm laravel -->
                            <td>{{ $important_doc->expired_date }}</td>
                            <td>
                                <form action="{{route('hrd.importantDocs.delete',$important_doc->id)}}" method="POST">
                                    <a href="{{route('hrd.importantDocs.detail', $important_doc->id)}}" class="btn btn-info me-1">Detail</a>
                                    <a href="{{route('hrd.importantDocs.edit', $important_doc->id)}}" class="btn btn-primary me-1">Edit</a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                  </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
