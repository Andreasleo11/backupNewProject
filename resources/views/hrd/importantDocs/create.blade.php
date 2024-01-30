@extends('layouts.app')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('hrd.importantDocs')}}">Important Documents</a></li>
      <li class="breadcrumb-item active">Create</li>
    </ol>
</nav>


<h2 class="mb-5">Create Important Document</h2>

<div class="row">
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
                                <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>

                                    @csrf

                                    <div class="form-group">
                                        <label class="fw-medium fs-5 mb-2">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Insert name of the document" required>

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must fill the name of the document</div>

                                        <!-- error message for title -->
                                        @error('name')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Type</label>

                                        <select class="form-select @error('type_id') is-invalid @enderror" name="type_id" aria-label="Default select example" value="{{ old('type') }}" required>
                                            <option selected disabled>--Select document type--</option>

                                            @foreach ($types as $type)
                                                <option value="{{$type->id}}">{{$type->id }}</option>
                                            @endforeach
                                        </select>

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must choose one type</div>

                                        <!-- error message for type -->
                                        @error('type_id')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Expired Date</label>
                                        <input type="date" class= "form-control" name="expired_date" value="{{ old('expired_date') }}" required>

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must fill the date of the document</div>

                                        <!-- error message for expired_date -->
                                        @error('expired_date')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <hr class="mt-5 "/>

                                    <div class="mt-2 d-flex flex-row-reverse">
                                        <button type="submit" class="btn btn-md btn-primary">Save Changes</button>
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

@push('extraJs')
<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>

@endpush
