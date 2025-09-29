@extends('layouts.app')

@push('extraCss')
    <style>
        .scrollable-table {
            max-height: 50vh;
            /* 50% of the viewport height */
            overflow-y: auto;
        }

        /* Media query for smaller screens to adjust the height */
        @media (max-width: 768px) {
            .scrollable-table {
                max-height: 70vh;
                /* 70% of the viewport height for small screens */
            }
        }
        }
    </style>
@endpush
@section('content')

    <body>
        <div class="row justify-content-center">
            <div class="col-md-9">
                @php $id = 1 @endphp
                @include('partials.add-defect-category-modal', ['id' => $id])
                <div class="text-end mb-3">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#add-defect-category-modal-{{ $id }}">+ Defect Category</button>
                </div>
                <h1 class="mb-4">Defects Categories</h1>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive scrollable-table">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($defectCategories as $defectCategory)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td style="width: 50%" class="text-center">{{ $defectCategory->name }}</td>
                                            <td>
                                                <button class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#edit-defect-category-modal-{{ $defectCategory->id }}"><i
                                                        class='bx bxs-edit'></i> Edit</button>
                                                @include(
                                                    'partials.edit-defect-category-modal',
                                                    $defectCategory)
                                                {{-- <button class="btn btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#delete-defect-category-confirmation-modal-{{ $defectCategory->id }}"><i
                                                        class='bx bx-trash'></i> Delete</button>
                                                @include(
                                                    'partials.delete-defect-category-modal',
                                                    $defectCategory) --}}
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
