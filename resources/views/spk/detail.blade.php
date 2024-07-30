@extends('layouts.app')

@section('content')

    <style>
        .autograph-box {
            border: 1px solid #ccc;
            padding: 10px;
            height: 100px;
            /* Adjust height as needed */
            text-align: center;
            font-size: 14px;
            color: #333;
            background-color: #f9f9f9;
        }
    </style>
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
                @if ($report->status_laporan == 2)
                    <div class="row mt-4 justify-content-center">
                        <div class="col-md-8">
                            <div class="row text-center justify-content-center">
                                <div class="col-md-4">
                                    <label for="pelapor_autograph" class="fw-semibold col-form-label">Pelapor
                                        Autograph</label>
                                    <div class="autograph-box" id="pelapor_autograph">
                                        <img src="{{ asset($report->pelapor . '.png') }}" alt="Pelapor Autograph"
                                            style="height: 100px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="petugas_autograph" class="fw-semibold col-form-label">Petugas
                                        Autograph</label>
                                    <div class="autograph-box" id="petugas_autograph">
                                        <img src="{{ asset($report->pic . '.png') }}" alt="Pelapor Autograph"
                                            style="height: 100px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="depthead_autograph" class="fw-semibold col-form-label">DeptHead
                                        Autograph</label>
                                    <div class="autograph-box" id="depthead_autograph">
                                        <img src="{{ asset($depthead->name . '.png') }}" alt="Pelapor Autograph"
                                            style="height: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif

                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <form action="{{ route('spk.update', $report->id) }}" method="post">
                            @method('PUT')
                            @csrf
                            <div class="card mt-2">
                                <div class="card-body">
                                    <div class="text-end">
                                        @if ($authUser->department->name === 'COMPUTER' || $authUser->department->name === 'MAINTENANCE' || $authUser->department->name === 'PERSONALIA' && $report->tanggal_selesai === null)
                                            <button type="button" class="btn btn-primary" id="editButton">Edit</button>
                                        @endif
                                    </div>
                                    <div class="text-center my-3">
                                        <h2 class="fw-bold">Surat Perintah Kerja Komputer</h2>
                                        <div class="text-secondary">
                                            <div>Pelapor : {{ $report->pelapor }}</div>
                                            <div>Tanggal Lapor :
                                                {{ \Carbon\Carbon::parse($report->tanggal_lapor)->translatedFormat('d F Y H:i:s') }}
                                            </div>
                                            <div>Dibuat Pada :
                                                {{ \Carbon\Carbon::parse($report->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                            </div>
                                            <div>Diupdate Pada :
                                                {{ \Carbon\Carbon::parse($report->updated_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                            </div>
                                            @if ($report->tanggal_selesai !== null)
                                                <div>Selesai pada :
                                                    {{ \Carbon\Carbon::parse($report->updated_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mt-2">
                                            @include('partials.spk-status', [
                                                'status' => $report->status_laporan,
                                            ])
                                        </div>


                                    </div>
                                    <div class="form-group row mt-5">
                                        <label for="no_dokumen" class="fw-semibold col-form-label col">No Dokumen</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="no_dokumen" id="no_dokumen"
                                                value="{{ $report->no_dokumen }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="pelapor" class="fw-semibold col-form-label col">Pelapor</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="pelapor" id="pelapor"
                                                value="{{ $report->pelapor }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="dept" class="fw-semibold col-form-label col">Departemen</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="dept" id="dept" value="{{ $report->dept }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-3">
                                        <label for="deto_departmentpt" class="fw-semibold col-form-label col">To
                                            Departemen</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="to_department" id="to_department"
                                                value="{{ $report->to_department }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="judul_laporan" class="fw-semibold col-form-label col">Judul
                                            Laporan</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="judul_laporan" id="judul_laporan"
                                                value="{{ $report->judul_laporan }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="no_dokumen" class="fw-semibold col-form-label col">Keterangan
                                            Laporan</label>
                                        <div class="col-sm-9 mt-2">
                                            <textarea name="keterangan_laporan" id="keterangan_laporan" class="form-control" rows="5">{{ $report->keterangan_laporan }}
                                    </textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="pic" class="fw-semibold col-form-label col">PIC</label>
                                        <div class="col-sm-9">
                                            <select name="pic" id="pic" class="form-control">
                                                <option value="" disabled selected>{{ $report->pic ?? '-' }}
                                                </option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->name }}"
                                                        {{ isset($report->pic) && $report->pic == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="keterangan_pic" class="fw-semibold col-form-label col">Keterangan
                                            PIC</label>
                                        <div class="col-sm-9 mt-2">
                                            <textarea name="keterangan_pic" id="keterangan_pic" class="form-control" rows="5">{{ $report->keterangan_pic ?? '-' }}
                                    </textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-3">
                                        <label class="fw-semibold col-form-label col">Remarks</label>
                                        <div class="col-sm-9 mt-2">
                                            @if ($report->spkRemarks->isEmpty())
                                                <p>No remarks available.</p>
                                            @else
                                                <ul class="list-group">
                                                    @foreach ($report->spkRemarks as $remark)
                                                        <li class="list-group-item">

                                                            @include('partials.spk-status', [
                                                                'status' => $remark->status,
                                                            ])
                                                            <br>
                                                            <strong>Remark:</strong> {{ $remark->remarks }} <br>
                                                            <strong>Date:</strong>
                                                            {{ \Carbon\Carbon::parse($remark->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_terima" class="fw-semibold col-form-label col">Tanggal
                                            Mulai</label>
                                        <div class="col-sm-9">
                                            <input type="datetime-local" name="tanggal_terima" id="tanggal_terima"
                                                class="form-control" value="{{ $report->tanggal_terima }}">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_estimasi" class="fw-semibold col-form-label col">Tanggal
                                            Estimasi</label>
                                        <div class="col-sm-9">
                                            <input type="datetime-local" name="tanggal_estimasi" id="tanggal_estimasi"
                                                class="form-control" value="{{ $report->tanggal_estimasi }}">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_selesai" class="fw-semibold col-form-label col">Tanggal
                                            Selesai</label>
                                        <div class="col-sm-9">
                                            <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                                class="form-control" value="{{ $report->tanggal_selesai }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end " id="saveChangesButtonContainer">
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
        // Function to handle the edit button click
        handleEditButtonClick();

        function handleEditButtonClick() {
            // Get all inputs and textareas except those with specific IDs
            const ignoredInputs = ['no_dokumen', 'pelapor', 'dept', 'to_department'];
            const inputs = document.querySelectorAll('input, textarea, select');
            const saveChangesButtonContainer = document.getElementById('saveChangesButtonContainer');

            inputs.forEach(input => {
                if (!ignoredInputs.includes(input.id)) {
                    input.readOnly = !input.readOnly;
                    input.disabled = !input.disabled;
                }
            });

            // Toggle the visibility of the Save changes button container
            saveChangesButtonContainer.classList.toggle('d-none');

            // Toggle the button class between btn-outline-primary and btn-primary
            const editButton = document.getElementById('editButton');
            editButton.classList.toggle('btn-outline-primary');
            editButton.classList.toggle('btn-primary');
        }

        // Ensure the DOM is fully loaded before adding event listener
        document.addEventListener('DOMContentLoaded', function() {
            const editButton = document.getElementById('editButton');

            // Check if editButton exists before adding event listener
            if (editButton) {
                editButton.addEventListener('click', handleEditButtonClick);
            } else {
                console.error('Edit button not found. Ensure the editButton ID is correct.');
            }
        });
    </script>
@endpush
