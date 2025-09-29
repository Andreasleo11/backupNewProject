@extends('layouts.app')

@section('content')
    <section class="header">
        <h2 class="">Edit Important Document</h2>
    </section>

    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hrd.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd.importantDocs.index') }}">Important
                        Documents</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </section>

    <section class="content">
        <div class="row mt-5">
            <div class="col text-center">
                <div class="h3 mt-4 mb-4 fw-lighter">
                    Important Document Form
                </div>
                <div class="container text-start col col-lg-7 col-md-10">
                    <div class="card">
                        <div class="row">
                            <div class="col">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-body">
                                        <form action="{{ route('hrd.importantDocs.update', $importantDoc->id) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group">
                                                <label class="fw-medium fs-5 mb-2">Name</label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                                    value="{{ $importantDoc->name }}"
                                                    placeholder="Insert name of the document">

                                                <!-- error message for title -->
                                                @error('name')
                                                    <div class="alert alert-danger mt-2">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-4">
                                                <label class="fw-medium fs-5 mb-2">Type</label>

                                                <select class="form-control @error('type_id') is-invalid @enderror"
                                                    name="type_id">
                                                    <option value = "{{ $importantDoc->type_id }}" selected>
                                                        {{ $importantDoc->type->name }}</option>

                                                    @foreach ($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>

                                                <!-- error message for type -->
                                                @error('type_id')
                                                    <div class="alert alert-danger mt-2">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-4">
                                                <label class="fw-medium fs-5 mb-2">Expired Date</label>
                                                <input type="date" class= "form-control" name="expired_date"
                                                    value="{{ $importantDoc->expired_date }}">

                                                <!-- error message for expired_date -->
                                                @error('expired_date')
                                                    <div class="alert alert-danger mt-2">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <hr class="mt-5 " />

                                            <div class="mt-2 d-flex flex-row-reverse">
                                                <button type="submit" class="btn btn-md btn-primary">Save Changes</button>
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
    </section>
@endsection
