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
                <li class="breadcrumb-item"><a href="{{ route('spk.index') }}">SPK</a>
                </li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col">
                <h2 class="fw-bold">{{ $report->no_dokumen }}</h2>
            </div>
            <div class="col text-end">
                {{-- Upcoming feature? --}}
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form action="{{ route('spk.update', $report->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card mt-2">
                                <div class="card-body">
                                    <div class="text-end">
                                        <button type="button" class="btn btn-outline-primary" id="editButton">Edit</button>
                                    </div>
                                    <div class="text-center my-3">
                                        <h2 class="fw-bold">Surat Perintah Kerja Komputer</h2>
                                        <div class="text-secondary">
                                            <div>Pelapor : {{ $report->pelapor }}</div>
                                            <div class="mb-2">Tanggal Lapor : @formatDate($report->tanggal_lapor)</div>
                                        </div>
                                        @include('partials.spk-status')
                                    </div>
                                    <div class="form-group row mt-5">
                                        <label for="no_dokumen" class="fw-semibold col-form-label col">No Dokumen</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="no_dokumen" id="no_dokumen"
                                                value="{{ $report->no_dokumen }}" readonly disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="pelapor" class="fw-semibold col-form-label col">Pelapor</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="pelapor" id="pelapor"
                                                value="{{ $report->pelapor }}" readonly disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="dept" class="fw-semibold col-form-label col">Departemen</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="dept" id="dept" value="{{ $report->dept }}"
                                                readonly disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="judul_laporan" class="fw-semibold col-form-label col">Judul
                                            Laporan</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="judul_laporan" id="judul_laporan"
                                                value="{{ $report->judul_laporan }}" readonly disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="no_dokumen" class="fw-semibold col-form-label col">Keterangan
                                            Laporan</label>
                                        <div class="col-sm-9 mt-2">
                                            <textarea name="keterangan_laporan" id="keterangan_laporan" readonly disabled class="form-control" rows="5">{{ $report->keterangan_laporan }}
                                    </textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="pic" class="fw-semibold col-form-label col">PIC</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="pic" id="pic"
                                                value="{{ $report->pic ?? '-' }}" readonly disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="keterangan_pic" class="fw-semibold col-form-label col">Keterangan
                                            PIC</label>
                                        <div class="col-sm-9 mt-2">
                                            <textarea name="keterangan_pic" id="keterangan_pic" readonly disabled class="form-control" rows="5">{{ $report->keterangan_pic ?? '-' }}
                                    </textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_selesai" class="fw-semibold col-form-label col">Tanggal
                                            Selesai</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="tanggal_selesai" id="tanggal_selesai"
                                                value="{{ $report->tanggal_selesai ?? '-' }}" readonly disabled
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_estimasi" class="fw-semibold col-form-label col">Tanggal
                                            Estimasi</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="tanggal_estimasi" id="tanggal_estimasi"
                                                value="{{ $report->tanggal_estimasi ?? '-' }}" readonly disabled
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end d-none" id="saveChangesButtonContainer">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('extraJs')
    <script>
        document.getElementById('editButton').addEventListener('click', function() {
            // Get all inputs and textareas
            const inputs = document.querySelectorAll('input, textarea');
            const saveChangesButtonContainer = document.getElementById('saveChangesButtonContainer');

            // Toggle readonly and disabled attributes from inputs and textareas
            inputs.forEach(input => {
                if (input.hasAttribute('readonly') || input.hasAttribute('disabled')) {
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('readonly', true);
                    input.setAttribute('disabled', true);
                }
            });

            // Toggle the visibility of the Save changes button container
            saveChangesButtonContainer.classList.toggle('d-none');

            // Toggle the button class between btn-outline-primary and btn-primary
            this.classList.toggle('btn-outline-primary');
            this.classList.toggle('btn-primary');
        });
    </script>
@endpush
