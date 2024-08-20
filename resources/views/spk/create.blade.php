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
                            <div class="row align-items-baseline">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="departmentDropdown"
                                            class="fw-semibold col-form-label col-sm-2">Department <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="dept" id="departmentDropdown" required>
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
                                        <label for="toDepartmentDropdown" class="fw-semibold col-form-label col-sm-2">To
                                            Department <span class="text-danger">*</span></label>
                                        <select class="form-select" name="to_department" id="toDepartmentDropdown" required>
                                            <option value="" selected disabled>Select to department..</option>
                                            <option value="COMPUTER">COMPUTER</option>
                                            <option value="MAINTENANCE">MAINTENANCE</option>
                                            <option value="PERSONALIA">PERSONALIA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="judul_laporan" class="fw-semibold form-label">Judul Laporan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="judul_laporan" id="judul_laporan" value="" required
                                    class="form-control" placeholder="Layar monitor komputer (departemen) bermasalah">
                            </div>
                            <div class="form-group mt-3">
                                <label for="keterangan_laporan" class="fw-semibold form-label">Keterangan Laporan <span
                                        class="text-danger">*</span></label>
                                <textarea name="keterangan_laporan" id="keterangan_laporan" cols="30" rows="10" class="form-control"
                                    placeholder="layar hanya berkedip saja tidak mau menyala padahal sudah dicoba restart" required></textarea>
                            </div>
                            <div class="form-group mt-3">
                                <label for="requested_by" class="fw-semibold form-label">Requested By <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="requested_by" id="requested_by" placeholder="Raymond" required
                                    class="form-control">
                            </div>
                            <div class="form-group mt-3">
                                <label for="requestedByAutograph" class="fw-semibold form-label">Requested By Autograph
                                    <span class="text-danger">*</span></label>
                                <canvas id="signature-pad" class="signature-pad border d-block" width="400"
                                    height="200"></canvas>
                                <button type="button" id="clear-signature" class="btn btn-secondary mt-2">Clear</button>
                                <input type="hidden" name="requested_by_autograph" id="requestedByAutograph">
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

        // Initialize Signature Pad
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById('clear-signature');
        const autographInput = document.getElementById('requestedByAutograph');

        clearButton.addEventListener('click', () => {
            signaturePad.clear();
            autographInput.value = '';
        });

        document.querySelector('#spkForm').addEventListener('submit', (event) => {
            if (!signaturePad.isEmpty()) {
                autographInput.value = signaturePad.toDataURL('image/png');
                console.log(autographInput.value); // Debugging line
            } else {
                autographInput.value = '';
                console.log('No signature drawn'); // Debugging line
            }
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
                default:
                    toDeptCode = 'UNKNOWN';
                    break;
            }

            const type = "SPK";
            const today = new Date();
            const date = today.toISOString().slice(2, 10).replace(/-/g, '');
            const count =
                {{ \App\Models\SuratPerintahKerjaKomputer::whereDate('created_at', \Carbon\Carbon::today())->count() }};
            const lastNumber = String(count).padStart(3, '0');
            const noDokumen = `${toDeptCode}/${type}/${date}/${lastNumber}`;

            document.getElementById('no_dokumen').value = noDokumen;
        });
    </script>
@endpush
