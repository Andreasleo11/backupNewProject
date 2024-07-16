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
                <li class="breadcrumb-item"><a href="{{ route('spk.index') }}">SPK List</a>
                </li>
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

        <div class="rol">
            <div class="col">
                <form action="" method="post">
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="no_dokumen" class="col-form-label col-sm-2">No Dokumen</label>
                                <div class="col-sm-10">
                                    <input type="text" name="no_dokumen" id="no_dokumen" value="testnodokumenrandom"
                                        readonly class="form-control bg-secondary-subtle">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="pelapor" class="col-form-label col-sm-2">Pelapor</label>
                                <div class="col-sm-10">
                                    <input type="text" name="pelapor" id="pelapor" value="testnodokumenrandom" readonly
                                        class="form-control bg-secondary-subtle">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="pelapor" class="col-form-label col-sm-2">Tanggal Lapor</label>
                                <div class="col-sm-10">
                                    <input type="date" name="pelapor" id="pelapor" value="" readonly
                                        class="form-control bg-secondary-subtle">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="departmentDropdown" class="col-form-label col-sm-2">Department</label>
                                <select class="form-select" name="dept" id="departmentDropdown" required>
                                    <option value="" selected disabled>Select from department..</option>
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
                            <div class="form-group mt-3">
                                <label for="keterangan_laporan" class="form-label">Judul Laporan</label>
                                <input type="text" name="judul_laporan" id="judul_laporan" value="" required
                                    class="form-control">
                            </div>
                            <div class="form-group mt-3">
                                <label for="keterangan_laporan" class="form-label">Keterangan Laporan</label>
                                <textarea name="keterangan_laporan" id="keterangan_laporan" cols="30" rows="10" class="form-control" required></textarea>
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
    </script>
@endpush
