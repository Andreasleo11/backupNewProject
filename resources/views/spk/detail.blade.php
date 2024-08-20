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
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="card mt-2">
                            <div class="card-body">
                                @include('partials.ask-a-revision-modal')

                                @include('partials.confirmation-modal', [
                                    'id' => $report->id,
                                    'title' => 'Finish this SPK',
                                    'body' => 'Are you sure?',
                                    'submitButton' =>
                                        '<button type="submit" class="btn btn-primary" onclick="document.getElementById(\'formFinishSPK\').submit()">Confirm</button>',
                                ])
                                <form id="formFinishSPK" action="{{ route('spk.finish', $report->id) }}" method="POST"
                                    class="d-none">
                                    @csrf
                                    @method('PUT')
                                </form>

                                <form action="{{ route('spk.update', $report->id) }}" method="post">
                                    @method('PUT')
                                    @csrf
                                    <div class="text-end">
                                        @if (
                                            ($authUser->department->name == 'COMPUTER' ||
                                                $authUser->department->name == 'MAINTENANCE' ||
                                                $authUser->department->name == 'PERSONALIA') &&
                                                ($report->status_laporan !== 3 && $report->status_laporan !== 4))
                                            <button type="button" class="btn btn-primary" id="editButton">Edit</button>
                                        @endif
                                    </div>
                                    @if ($report->status_laporan === 3)
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#ask-a-revision-modal-{{ $report->id }}"
                                            class="btn btn-outline-primary">Ask a Revision</button>


                                        <button type="button" class="btn btn-primary"
                                            data-bs-target="#confirmation-modal-{{ $report->id }}"
                                            data-bs-toggle="modal">Finish</button>
                                    @endif
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
                                            @if ($report->tanggal_selesai)
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
                                        <label for="requested_by" class="fw-semibold col-form-label col">Requested
                                            By</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="requested_by" id="requested_by"
                                                value="{{ $report->requested_by }}"
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
                                            <textarea name="keterangan_laporan" id="keterangan_laporan" class="form-control" rows="5">{{ $report->keterangan_laporan }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="pic" class="fw-semibold col-form-label col">PIC</label>
                                        <div class="col-sm-9">
                                            <select name="pic" id="pic" class="form-select">
                                                <option value="" disabled selected>--Select PIC--
                                                </option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->name }}"
                                                        {{ isset($report->pic) && $report->pic == $user->name ? 'selected' : '' }}>
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
                                            <textarea name="keterangan_pic" id="keterangan_pic" class="form-control" rows="5">{{ $report->keterangan_pic }}</textarea>
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
                                        <label class="fw-semibold col-form-label col">Revision Remarks</label>
                                        <div class="col-sm-9 mt-2">
                                            @if ($report->revisionRemarks->isEmpty())
                                                <p>No revision remarks available.</p>
                                            @else
                                                <ul class="list-group">
                                                    @foreach ($report->revisionRemarks as $remark)
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
                                    <div class="mt-3 text-end " id="saveChangesButtonContainer">
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>

                                <div class="mt-5">
                                    @include('partials.spk-autographs')
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
        // Function to handle the edit button click
        handleEditButtonClick();

        function handleEditButtonClick() {
            // Get all inputs and textareas except those with specific IDs
            const toggleableInputs = [
                'judul_laporan', 'keterangan_laporan', 'pic', 'keterangan_pic', 'tanggal_terima', 'tanggal_estimasi',
                'tanggal_selesai'
            ];
            const inputs = document.querySelectorAll('input:not([type=hidden]), textarea, select');
            const saveChangesButtonContainer = document.getElementById('saveChangesButtonContainer');

            inputs.forEach(input => {
                if (toggleableInputs.includes(input.id)) {
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
