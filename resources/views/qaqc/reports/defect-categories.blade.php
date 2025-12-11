@extends('new.layouts.app')

@push('head')
    <style>
        .scrollable-table {
            max-height: 50vh;
            /* 50% of the viewport height */
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .scrollable-table {
                max-height: 70vh;
                /* 70% of the viewport height for small screens */
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-9">

                {{-- Modal "Add Defect Category" (global, tapi id unik) --}}
                @php $id = 1; @endphp
                <div class="modal fade" id="add-defect-category-modal-{{ $id }}" tabindex="-1"
                    aria-labelledby="addDefectCategoryModalLabel-{{ $id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white">
                            <form method="POST" action="{{ route('qaqc.defectcategory.store') }}">
                                @csrf

                                {{-- Header --}}
                                <div class="modal-header border-0 px-4 py-3 sm:px-6 sm:py-4">
                                    <h5 class="modal-title text-base sm:text-lg font-semibold text-gray-900"
                                        id="addDefectCategoryModalLabel-{{ $id }}">
                                        Add New Defect Category
                                    </h5>
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-full p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        data-bs-dismiss="modal" aria-label="Close">
                                        <span class="sr-only">Close</span>
                                        &times;
                                    </button>
                                </div>

                                {{-- Body --}}
                                <div class="modal-body px-4 py-3 sm:px-6 sm:py-5">
                                    <div class="mb-3">
                                        <label for="defect-category-name-{{ $id }}"
                                            class="block text-sm font-medium text-gray-700">
                                            Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="defect-category-name-{{ $id }}" name="name"
                                            value="{{ old('name') }}" required placeholder="e.g. Nubmark, Scratch, Bubble"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      px-3 py-2
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                        <p class="mt-1 text-xs text-gray-500">
                                            Nama kategori defect yang akan muncul di form report.
                                        </p>
                                        @error('name')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div
                                    class="modal-footer border-0 px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-end gap-3">
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add Category
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h4 mb-1">Defect Categories</h1>
                        <p class="text-muted mb-0 small">
                            Kelola daftar kategori defect yang dipakai di Verification / QAQC report.
                        </p>
                    </div>

                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#add-defect-category-modal-{{ $id }}">
                        <i class='bx bx-plus'></i>
                        <span class="d-none d-sm-inline">Add Category</span>
                    </button>
                </div>

                {{-- Card: Table --}}
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive scrollable-table">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">#</th>
                                        <th class="text-center" style="width: 50%;">Name</th>
                                        <th class="text-center" style="width: 200px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($defectCategories as $defectCategory)
                                        <tr>
                                            <td class="text-center">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="text-center">
                                                {{ $defectCategory->name }}
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#edit-defect-category-modal-{{ $defectCategory->id }}">
                                                    <i class='bx bxs-edit'></i>
                                                    <span class="d-none d-sm-inline">Edit</span>
                                                </button>

                                                <div class="modal fade"
                                                    id="edit-defect-category-modal-{{ $defectCategory->id }}"
                                                    tabindex="-1"
                                                    aria-labelledby="editDefectCategoryModalLabel-{{ $defectCategory->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div
                                                            class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white">
                                                            <form method="POST"
                                                                action="{{ route('qaqc.defectcategory.update', $defectCategory->id) }}">
                                                                @csrf
                                                                @method('PUT')

                                                                {{-- Header --}}
                                                                <div
                                                                    class="modal-header border-0 px-4 py-3 sm:px-6 sm:py-4">
                                                                    <h5 class="modal-title text-base sm:text-lg font-semibold text-gray-900"
                                                                        id="editDefectCategoryModalLabel-{{ $defectCategory->id }}">
                                                                        Edit Defect Category
                                                                    </h5>
                                                                    <button type="button"
                                                                        class="inline-flex items-center justify-center rounded-full p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                                        data-bs-dismiss="modal" aria-label="Close">
                                                                        <span class="sr-only">Close</span>
                                                                        &times;
                                                                    </button>
                                                                </div>

                                                                {{-- Body --}}
                                                                <div class="modal-body px-4 py-3 sm:px-6 sm:py-5">
                                                                    <div class="mb-3">
                                                                        <label
                                                                            for="defect-category-edit-name-{{ $defectCategory->id }}"
                                                                            class="block text-sm font-medium text-gray-700">
                                                                            Name <span class="text-red-500">*</span>
                                                                        </label>
                                                                        <input type="text"
                                                                            id="defect-category-edit-name-{{ $defectCategory->id }}"
                                                                            name="name"
                                                                            value="{{ old('name', $defectCategory->name) }}"
                                                                            required
                                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                                                                                px-3 py-2
                                                                                focus:border-indigo-500 focus:ring-indigo-500
                                                                                @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                                                        @error('name')
                                                                            <p class="mt-1 text-xs text-red-600">
                                                                                {{ $message }}</p>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                {{-- Footer --}}
                                                                <div
                                                                    class="modal-footer border-0 px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-end gap-3">
                                                                    <button type="button"
                                                                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                                        data-bs-dismiss="modal">
                                                                        Close
                                                                    </button>
                                                                    <button type="submit"
                                                                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                        Save Changes
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class='bx bx-error-circle fs-3 d-block mb-2'></i>
                                                <span class="d-block fw-semibold">Belum ada defect category.</span>
                                                <span class="small d-block">Klik tombol “Add Category” untuk menambahkan
                                                    kategori baru.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
