@extends('layouts.app')

@section('content')

<section class="header">
    <h2>Create Important Document</h2>
</section>

<section class="breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('hrd.home')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{route('hrd.importantDocs.index')}}">Important Documents</a></li>
          <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</section>


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
                                <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation px-3" novalidate>

                                    @csrf

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Type</label>

                                        <select id="typeSelect" class="form-select @error('type_id') is-invalid @enderror" name="type_id" aria-label="Default select example" value="{{ old('type') }}" required>
                                            <option selected disabled value="">--Select document type--</option>

                                            @foreach ($types as $type)
                                                <option value="{{$type->id}}">{{$type->name}}</option>
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

                                    <div class="form-group mt-4" id="otherFormGroup" style="display: none">
                                        <label for="other" class="mb-2">Other Type Name</label>
                                        <input type="text" id="otherInput" name="other" class="form-control">

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must fill the other type name</div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                                        <div class="form-text text-secondary">e.g KITAS Raymond Lay, BPKB Mobil Alphard F 1223 ED</div>

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
                                        <label class="fw-medium fs-5 mb-2">Document ID <span class="text-secondary h6">(Optional)</label>
                                        <input type="text" class="form-control @error('document_id') is-invalid @enderror" name="document_id" value="{{ old('document_id') }}">
                                        <div class="form-text text-secondary">e.g 90S/A8D.89OU</div>

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must fill the name of the document</div>

                                        <!-- error message for title -->
                                        @error('document_id')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Description <span class="text-secondary h6">(Optional)</span></label>
                                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}"></textarea>

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">You must fill the description of the document</div>

                                        <!-- error message for title -->
                                        @error('description')
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

                                    <div class="form-group mt-4">
                                        <label class="fw-medium fs-5 mb-2">Upload file <span class="text-secondary h6">(Optional)</span>{{ csrf_field() }}</label>
                                        <input type="file" id="fileInput" class= "form-control" name="files[]" value="{{ old('files') }}">

                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">Wrong file extensions</div>

                                        <!-- error message for files -->
                                        @error('files')
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

    const otherForm = document.getElementById('otherFormGroup');
    const otherInput = document.getElementById('otherInput');
    const typeSelect = document.getElementById('typeSelect');

    // upload multiple files when select kitas type
    const fileInput = document.getElementById('fileInput');

    typeSelect.addEventListener('change', function() {
        if(typeSelect.value == 1){
            otherForm.style.display = 'block';
            otherInput.setAttribute('required', 'required');
        } else {
            otherForm.style.display = 'none';
            otherInput.removeAttribute('required');
        }

        if(typeSelect.value == 3){
            fileInput.setAttribute('multiple', 'multiple');
        } else {
            fileInput.removeAttribute('multiple');
        }
    });

</script>

@endpush
