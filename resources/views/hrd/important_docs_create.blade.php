@extends('layouts.app')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('hrd.importantDocs')}}">Important Documents</a></li>
      <li class="breadcrumb-item active">Create</li>
    </ol>
</nav>


<div class="row">
    <div class="col text-center">
        <div class="h3 mt-4 mb-4 fw-medium">
            Important Document Form
        </div>
        <div class="container text-start col col-lg-7 col-md-10">
            <div class="card">
                <div class="row">
                    <div class="col">
                        <div class="card border-0 shadow-sm rounded">
                            <div class="card-body">
                                <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data">

                                    @csrf

                                    <div class="form-group">
                                        <label class="fw-medium fs-5 mb-2">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Insert name of the document">

                                        <!-- error message for title -->
                                        @error('name')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Type</label>

                                        <select class="form-control @error('type') is-invalid @enderror" name="type" aria-label="Default select example" value="{{ old('type') }}">
                                            <option selected>Select document type</option>

                                            <!-- TODO: Should be dynamic -->

                                            <option value="0">Zero</option>
                                            <option value="1">One</option>
                                            <option value="2">Two</option>
                                        </select>

                                        <!-- error message for type -->
                                        @error('type')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Expired Date</label>
                                        <input type="date" class= "form-control" name="expired_date" value="{{ old('expired_date') }}" >

                                        <!-- error message for expired_date -->
                                        @error('expired_date')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <hr class="mt-5 "/>

                                    <div class="mt-2 d-flex flex-row-reverse">
                                        <button type="submit" class="btn btn-md btn-primary">Simpan</button>
                                        <button type="reset" class="btn btn-md btn-warning me-3">Clear</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
