@extends('layouts.app')

@section('content')
    <style>
        .scrollable-container {
            max-height: 200px;
            /* Adjust the height as needed */
            overflow-y: auto;
            /* Adds vertical scrollbar */
            transition: max-height 0.3s ease;
            /* Smooth transition */
        }

        .scrollable-container.expanded {
            max-height: none;
            /* Removes the height limit when expanded */
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
                @if ($report->pelapor === $authUser->name || $report->status_laporan === 2)
                    @include('partials.upload-files-modal', ['doc_id' => $report->no_dokumen])
                    <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                        <i class='bx bx-upload'></i> Upload
                    </button>
                @endif
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
                                    'body' => "Are you sure want to finish this <strong>$report->no_dokumen</strong>?",
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
                                    @if ($report->status_laporan === 4)
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#ask-a-revision-modal-{{ $report->id }}"
                                            class="btn btn-outline-primary ">Ask a Revision</button>
                                        <span class="px-2">or</span>
                                        <button type="button" class="btn btn-outline-success "
                                            data-bs-target="#confirmation-modal-{{ $report->id }}"
                                            data-bs-toggle="modal">Finish</button>
                                        <span>this SPK
                                            (<strong>{{ $report->no_dokumen }}</strong>).</span>
                                        <hr>
                                    @endif
                                    <div class="text-center my-3">
                                        <h2 class="fw-bold">Surat Perintah Kerja</h2>
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
                                                'is_urgent' => $report->is_urgent,
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
                                        <label for="from_department" class="fw-semibold col-form-label col">From
                                            Department</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="from_department" id="from_department"
                                                value="{{ $report->from_department }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="to_department" class="fw-semibold col-form-label col">To
                                            Department</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="to_department" id="to_department"
                                                value="{{ $report->to_department }}"
                                                class="form-control-plaintext bg-secondary-subtle py-2 ps-3 rounded-2"
                                                readonly>
                                        </div>
                                    </div>
                                    <hr>
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
                                    @if ($report->to_department === 'MAINTENANCE MOULDING')
                                        <div class="form-group mt-3 row">
                                            <label for="inlineRadio"
                                                class="fw-semibold col col-form-label fw-semibold">For </label>

                                            <div class="col-sm-9 mt-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="for"
                                                        id="inlineRadioMol" value="mol">
                                                    <label class="form-check-label" for="inlineRadioMol">Mol</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="for"
                                                        id="inlineRadioMachine" value="machine">
                                                    <label class="form-check-label"
                                                        for="inlineRadioMachine">Machine</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                                        <label for="tindakan" class="fw-semibold col-form-label col">Keterangan
                                            PIC</label>
                                        <div class="col-sm-9 mt-2">
                                            <textarea name="tindakan" id="tindakan" class="form-control" rows="5">{{ $report->tindakan }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label class="fw-semibold col-form-label col">Remarks</label>
                                        <div class="col-sm-9 mt-2">
                                            @if ($report->spkRemarks->isEmpty())
                                                <p>No remarks available.</p>
                                            @else
                                                <div id="remarks-container" class="scrollable-container">
                                                    <ul class="list-group">
                                                        @foreach ($report->spkRemarks as $remark)
                                                            <li class="list-group-item">
                                                                @include('partials.spk-status', [
                                                                    'status' => $remark->status,
                                                                    'is_urgent' => $report->is_urgent,
                                                                ])
                                                                <br>
                                                                <strong>Remark:</strong> {{ $remark->remarks }} <br>
                                                                <strong>Date:</strong>
                                                                {{ \Carbon\Carbon::parse($remark->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <button type="button" id="toggle-remarks" class="btn btn-link">Show
                                                    All
                                                    Remarks</button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label class="fw-semibold col-form-label col">Revision Remarks</label>
                                        <div class="col-sm-9 mt-2">
                                            @if ($report->revisionRemarks->isEmpty())
                                                <p>No revision remarks available.</p>
                                            @else
                                                <div id="revision-remarks-container" class="scrollable-container">
                                                    <ul class="list-group">
                                                        @foreach ($report->revisionRemarks as $remark)
                                                            <li class="list-group-item">
                                                                @include('partials.spk-status', [
                                                                    'status' => $remark->status,
                                                                    'is_urgent' => $report->is_urgent,
                                                                ])
                                                                <br>
                                                                <strong>Remark:</strong> {{ $remark->remarks }} <br>
                                                                <strong>Date:</strong>
                                                                {{ \Carbon\Carbon::parse($remark->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <button type="button" id="toggle-revision-remarks"
                                                    class="btn btn-link mt-2">Show All
                                                    Revision Remarks</button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group row mt-3">
                                        <label for="tanggal_mulai" class="fw-semibold col-form-label col">Tanggal
                                            Mulai</label>
                                        <div class="col-sm-9">
                                            <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                                class="form-control" value="{{ $report->tanggal_mulai }}">
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
                                    <div class="form-group mt-3 row">
                                        <label for="lama pengerjaan" class="fw-semibold col-form-label col">Lama
                                            Pengerjaan</label>
                                        <div class="col-sm-9">
                                            @php
                                                $tanggalMulai = \Carbon\Carbon::parse($report->tanggal_mulai);
                                                $tanggalSelesai = \Carbon\Carbon::parse($report->tanggal_selesai);

                                                if ($tanggalMulai->isSameDay($tanggalSelesai)) {
                                                    // Calculate the exact time difference
                                                    $difference = $tanggalMulai->diff($tanggalSelesai);
                                                    $lamaPengerjaan = $difference->format('%h Jam %I Menit');
                                                } else {
                                                    // Calculate the total difference
                                                    $totalDays = $tanggalMulai->diffInDays($tanggalSelesai);
                                                    $tanggalMulaiPlusOneDay = $tanggalMulai->addDay();
                                                    $remainingTime = $tanggalMulaiPlusOneDay->diff($tanggalSelesai);

                                                    $lamaPengerjaan =
                                                        $totalDays .
                                                        ' Hari ' .
                                                        $remainingTime->format('%H Jam %I Menit');
                                                }
                                            @endphp
                                            <input type="text" name="lama_pengerjaan" id="lama_pengerjaan"
                                                class="form-control-plaintext" readonly
                                                value="{{ $report->tanggal_mulai && $report->tanggal_selesai ? $lamaPengerjaan : '-' }}">
                                        </div>
                                    </div>
                                    <div class="mt-3 text-end " id="saveChangesButtonContainer">
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                                <hr>
                                <div class="mt-4">
                                    @include('partials.spk-autographs')
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.uploaded-section', [
        'files' => $files,
        'showDeleteButton' => $report->pelapor === $authUser->name || $report->status_laporan === 2,
    ])

@endsection
@push('extraJs')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleRemarks = document.getElementById('toggle-remarks');
            const remarksContainer = document.getElementById('remarks-container');

            if (toggleRemarks) {
                toggleRemarks.addEventListener('click', function() {
                    if (remarksContainer.classList.contains('expanded')) {
                        remarksContainer.classList.remove('expanded');
                        toggleRemarks.textContent = 'Show All Remarks';
                    } else {
                        remarksContainer.classList.add('expanded');
                        toggleRemarks.textContent = 'Show Less';
                    }
                });
            }

            const toggleRevisionRemarks = document.getElementById('toggle-revision-remarks');
            const revisionRemarksContainer = document.getElementById('revision-remarks-container');

            if (toggleRevisionRemarks) {
                toggleRevisionRemarks.addEventListener('click', function() {
                    if (revisionRemarksContainer.classList.contains('expanded')) {
                        revisionRemarksContainer.classList.remove('expanded');
                        toggleRevisionRemarks.textContent = 'Show All Revision Remarks';
                    } else {
                        revisionRemarksContainer.classList.add('expanded');
                        toggleRevisionRemarks.textContent = 'Show Less';
                    }
                });
            }
        });

        // Function to handle the edit button click
        handleEditButtonClick();

        function handleEditButtonClick() {
            // Get all inputs and textareas except those with specific IDs
            const toggleableInputs = [
                'judul_laporan', 'keterangan_laporan', 'pic', 'tindakan', 'tanggal_mulai', 'tanggal_estimasi',
                'tanggal_selesai', 'inlineRadioMol', 'inlineRadioMachine'
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
