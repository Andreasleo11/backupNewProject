@extends('layouts.app')

@section('content')


<body>
    <div class="row justify-content-center">
        <div class="col-md-9">
            @include('partials.add-defect-category-modal')
            <div class="text-end mb-3">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#add-defect-category-modal">+ Defect Category</button>
            </div>
            <h1 class="mb-4">Defects Categories</h1>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($defectCategories as $defectCategory)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td style="width: 50%" class="text-center">{{ $defectCategory->name }}</td>
                                    <td>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-defect-category-modal-{{ $defectCategory->id }}"><i class='bx bxs-edit' ></i> Edit</button>
                                        @include('partials.edit-defect-category-modal', $defectCategory)
                                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-defect-category-confirmation-modal-{{ $defectCategory->id }}"><i class='bx bx-trash'></i> Delete</button>
                                        @include('partials.delete-defect-category-modal', $defectCategory)
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>




</body>





@endsection
