@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('spk.index') }}">SPK List</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col">
                <h2 class="fw-bold">SPK Create</h2>
            </div>
            <div class="col text-end">
                {{-- Upcoming feature? --}}
            </div>
        </div>

        <div class="row">
            <div class="col">
                <form action="{{ route('spk.input') }}" method="post" enctype="multipart/form-data" id="spkForm">
                    @csrf
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="no_dokumen" class="fw-semibold col-form-label col-sm-2">No Dokumen</label>
                                <div class="col-sm-10">
                                    <input type="text" name="no_dokumen" id="no_dokumen" readonly
                                        class="form-control bg-secondary-subtle">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="pelapor" class="fw-semibold col-form-label col-sm-2">Pelapor</label>
                                <div class="col-sm-10">
                                    <input type="text" name="pelapor" id="pelapor" value="{{ $username }}" readonly
                                        class="form-control bg-secondary-subtle">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="tanggallapor" class="fw-semibold col-form-label col-sm-2">Tanggal Lapor</label>
                                <div class="col-sm-10">
                                    <input type="datetime-local" name="tanggallapor" id="tanggallapor" readonly
                                        class="form-control bg-secondary-subtle">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Details</h4>
                            <hr>

                            <div class="row mt-3">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="departmentDropdown" class="fw-semibold col-form-label">From
                                            Department <span class="text-danger">*</span></label>
                                        <select class="form-select" name="from_department" id="departmentDropdown" required>
                                            <option value="" selected disabled>--Select from department--</option>
                                            @foreach ($departments as $department)
                                                @if ($department->id === $authUser->department->id)
                                                    <option value="{{ $department->name }}" selected>{{ $department->name }}
                                                    </option>
                                                @elseif ($department->name === 'HRD' || $department->name === 'DIRECTOR')
                                                @else
                                                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="toDepartmentDropdown" class="fw-semibold col-form-label">To
                                            Department <span class="text-danger">*</span></label>
                                        <select class="form-select" name="to_department" id="toDepartmentDropdown" required>
                                            <option value="" selected disabled>Select to department..</option>
                                            <option value="COMPUTER">COMPUTER</option>
                                            <option value="MAINTENANCE">MAINTENANCE</option>
                                            <option value="MAINTENANCE MOULDING">MAINTENANCE MOULDING</option>
                                            <option value="PERSONALIA">PERSONALIA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="requested_by" class="fw-semibold form-label ">Requested By
                                            <span class="text-danger">*</span></label>
                                        <input type="text" name="requested_by" id="requested_by"
                                            placeholder="e.g. Raymond" required class="form-control">
                                    </div>
                                </div>
                                <div class="col d-none type-field">
                                    <div class="form-group">
                                        <div>
                                            <label for="inlineRadio" class="form-label fw-semibold">Type</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="type"
                                                id="inlineRadioMade" value="made">
                                            <label class="form-check-label" for="inlineRadioMade">Made</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="type"
                                                id="inlineRadioRepair" value="repair">
                                            <label class="form-check-label" for="inlineRadioRepair">Repair</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="type"
                                                id="inlineRadioModify" value="modify">
                                            <label class="form-check-label" for="inlineRadioModify">Modify</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <div>
                                            <label for="inlineRadio" class="form-label fw-semibold">Is Urgent? <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_urgent"
                                                id="inlineRadioYes" value="yes">
                                            <label class="form-check-label" for="inlineRadioYes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_urgent"
                                                id="inlineRadioNo" value="no" checked>
                                            <label class="form-check-label" for="inlineRadioNo">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 d-none part-fields">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="part_no" class="fw-semibold col-form-label">Part No
                                            <span class="text-secondary fw-normal">(Optional)</span></label>
                                        <input type="text" name="part_no" id="part_no" class="form-control">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="part_name" class="fw-semibold col-form-label">Part
                                            Name <span class="text-secondary fw-normal">(Optional)</span></label>
                                        <input type="text" name="part_name" id="part_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="machine" class="fw-semibold col-form-label">Machine <span
                                                class="text-secondary fw-normal">(Optional)</span></label>
                                        <input type="text" name="machine" id="machine" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <label for="judul_laporan" class="fw-semibold form-label">Judul Laporan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="judul_laporan" id="judul_laporan" value="" required
                                    class="form-control"
                                    placeholder="e.g. Layar monitor komputer (departemen) bermasalah">
                            </div>
                            <div class="form-group mt-3">
                                <label for="keterangan_laporan" class="fw-semibold form-label">Keterangan Laporan <span
                                        class="text-danger">*</span></label>
                                <textarea name="keterangan_laporan" id="keterangan_laporan" cols="30" rows="10" class="form-control"
                                    placeholder="e.g. layar hanya berkedip saja tidak mau menyala padahal sudah dicoba restart" required></textarea>
                            </div>
                            <div class="form-group mt-3">
                                <label for="attachments" class="fw-semibold form-label">Attachments</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control"
                                    multiple accept="image/*">
                                <div id="attachment-previews" class="mt-3 row"></div>
                            </div>

                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('extraJs')
    <script type="module">
        // Initialize TomSelect for dropdown
        new TomSelect('#departmentDropdown', {
            plugins: ['dropdown_input'],
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const dd = String(today.getDate()).padStart(2, '0');
            const hh = String(today.getHours()).padStart(2, '0');
            const min = String(today.getMinutes()).padStart(2, '0');
            const ss = String(today.getSeconds()).padStart(2, '0');
            const formattedToday = `${yyyy}-${mm}-${dd}T${hh}:${min}:${ss}`;
            document.getElementById('tanggallapor').value = formattedToday;
        });

        document.getElementById('toDepartmentDropdown').addEventListener('change', function() {
            const toDepartment = this.value;
            let toDeptCode = '';

            switch (toDepartment) {
                case 'COMPUTER':
                    toDeptCode = 'CP';
                    break;
                case 'PERSONALIA':
                    toDeptCode = 'HRD';
                    break;
                case 'MAINTENANCE':
                    toDeptCode = 'MT';
                    break;
                case 'MAINTENANCE MOULDING':
                    toDeptCode = 'MM';
                    break;
                default:
                    toDeptCode = 'UNKNOWN';
                    break;
            }

            const type = "SPK";
            const today = new Date();
            const date = today.toISOString().slice(2, 10).replace(/-/g, '');
            const count =
                {{ \App\Models\SuratPerintahKerja::whereDate('created_at', \Carbon\Carbon::today())->count() + 1 }};
            const lastNumber = String(count).padStart(3, '0');
            const noDokumen = `${toDeptCode}/${type}/${date}/${lastNumber}`;

            document.getElementById('no_dokumen').value = noDokumen;

            // Toggle part number, part name, and machine fields based on department selection
            const partFields = document.querySelector('.part-fields');
            const forFields = document.querySelector('.for-fields');
            if (toDepartment === 'MAINTENANCE MOULDING') {
                partFields.classList.remove('d-none');
            } else {
                partFields.classList.add('d-none');
            }

            const typeFields = document.querySelector('.type-field');
            if (toDepartment === 'MAINTENANCE' || toDepartment === 'MAINTENANCE MOULDING') {
                typeFields.classList.remove('d-none');
            } else {
                typeFields.classList.add('d-none');
            }

        });

        document.addEventListener('DOMContentLoaded', (event) => {
            const attachmentInput = document.getElementById('attachments');
            const previewContainer = document.getElementById('attachment-previews');
            let files = [];

            attachmentInput.addEventListener('change', function(event) {
                files = Array.from(event.target.files);
                renderPreviews(files);
            });

            function renderPreviews(files) {
                previewContainer.innerHTML = ''; // Clear existing previews

                files.forEach((file, i) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.classList.add('col-md-3', 'mb-3');
                        previewDiv.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" alt="Attachment Preview" class="card-img-top">
                        <div class="card-body p-2 text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-image" data-index="${i}">
                                Remove
                            </button>
                        </div>
                    </div>
                `;
                        previewContainer.appendChild(previewDiv);
                    };

                    reader.readAsDataURL(file);
                });

                // Adding a slight delay to ensure DOM updates before adding listeners
                setTimeout(addRemoveListeners, 30);
            }

            function addRemoveListeners() {
                const removeButtons = previewContainer.querySelectorAll('.remove-image');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));

                        files.splice(index, 1); // Remove the selected file

                        updateFileInput(files);
                        renderPreviews(files); // Re-render previews with updated file list
                    });
                });
            }

            function updateFileInput(files) {
                const dataTransfer = new DataTransfer();
                files.forEach(file => {
                    dataTransfer.items.add(file); // Add remaining files to DataTransfer object
                });

                attachmentInput.files = dataTransfer.files; // Update the file input element
            }
        });
    </script>
@endpush
