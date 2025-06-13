@extends('layouts.app')

@push('extraCss')
    <style>
        .autograph-box {
            width: 200px;
            /* Adjust the width as needed */
            height: 100px;
            /* Adjust the height as needed */
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
            /* Add border for better visibility */
        }

        .rejection-textarea {
            background-color: #ffe6e6;
            border: 1px solid #ff0000;
            font-size: 1rem;
            padding: 10px;
            resize: none;
        }
    </style>
@endpush

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    @include('partials.alert-success-error')
    @include('partials.edit-form-overtime-modal', [
        'prheader' => $header,
        'datas' => $datas,
    ])

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('formovertime.index') }}">Form Overtime</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col text-end">
            @if ($header->status === 1 && $authUser->id === $header->user->id)
                <button data-bs-target="#edit-form-overtime-modal-{{ $header->id }}" data-bs-toggle="modal"
                    class="btn btn-primary"><i class='bx bx-edit'></i> Edit</button>
            @endif
            @if ($header->status === 5 && $authUser->specification->name === 'VERIFICATOR')
                <a href="{{ route('export.overtime', $header->id) }}" class="btn btn-success">Export to Excel</a>
            @endif
        </div>
    </div>

    @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
        <button id="btnPushAll" data-header-id="{{ $header->id }}"
            class="bg-red-600 hover:bg-red-700 text-black font-semibold px-4 py-2 rounded">
            Push All to JPayroll
        </button>

        <!-- Tempat notifikasi -->
        <div id="pushAllResult" class="mt-2 text-sm"></div>
    @endif

    {{-- @include('partials.formovertime-autographs') --}}
    <div class="mt-2 container">
        <div class="d-flex justify-content-around">
            @foreach ($header->approvals as $approval)
                <div>
                    <div class="text-center">
                        <div class="col">
                            <div class="d-flex justify-content-center"></div>
                            <div class="fs-3">{{ ucwords(str_replace('_', ' ', $approval->step->role_slug)) }}
                            </div>
                            @if ($approval->status === 'approved')
                                <span class="badge bg-success">✓ Signed
                                    {{ optional($approval->signed_at)->diffForHumans() }}</span>
                            @elseif ($approval->status === 'rejected')
                                <span class="badge bg-danger">✗ Rejected</span>
                            @endif
                        </div>
                    </div>
                    <div class="border mt-2" style="width:200px; height:100px;">
                        <img src="{{ asset('autographs/' . $approval->signature_path) }}" alt=""
                            class="object-fit-cover">
                    </div>
                    <div class="mt-2">
                        @if ($approval->approver)
                            <div class="text-center">
                                {{-- Signed-at + calendar icon --}}
                                <div class="gap-1">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                    <span class="small text-muted">
                                        {{ optional($approval->signed_at)->timezone('Asia/Jakarta')->format('d-m-Y · H:i') ?? '—' }}

                                    </span>
                                </div>

                                {{-- Approver name + person icon --}}
                                <div class="gap-1">
                                    <i class="bi bi-person-check-fill text-primary"></i>
                                    <span class="fw-semibold">
                                        {{ Str::title($approval->approver->name ?? 'Pending') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            @php
                                $isCurrentStep = optional($header->currentStep())->id === $approval->flow_step_id;
                                // dd($header->currentStep()->id);
                                // dd($approval->flow_step_id);
                                $isPending = $approval->status === 'pending';
                                $allowedByRole = false;
                                $user = auth()->user();

                                switch ($approval->step->role_slug) {
                                    case 'dept_head':
                                        $allowedByRole =
                                            $user->is_head &&
                                            $user->department->name === $approval->form->department->name;
                                        break;
                                    case 'director':
                                        $allowedByRole = $user->specification->name === 'DIRECTOR';
                                        break;
                                    case 'supervisor':
                                        $allowedByRole = $user->specification->name === 'SUPERVISOR';
                                        break;
                                    case 'gm':
                                        $allowedByRole = $user->is_gm;
                                        break;
                                    case 'creator':
                                        $allowedByRole = $approval->form->user_id === $user->id;
                                        break;
                                }

                                $showApprovalButtons = $isCurrentStep && $isPending && $allowedByRole;
                            @endphp

                            <div class="d-flex justify-content-between {{ $showApprovalButtons ? '' : 'd-none' }}">
                                <div class="modal fade" id="rejectModal">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('overtime.reject', $header->id) }}" method="post">
                                                @method('PUT')
                                                @csrf
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5">Reason</h1>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="approval_id" value="{{ $approval->id }}">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" placeholder="Tell us why you rejecting this report..." required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Confirm</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                                <form
                                    action="{{ route('overtime.sign', ['id' => $approval->form->id, 'step_id' => $approval->flow_step_id]) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 container">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <span class="h1 fw-semibold">Form Overtime</span>
                        <br>
                        <div class="fs-6 mt-2">
                            <span class="fs-6 text-secondary">Create Date : </span>
                            {{ \Carbon\Carbon::parse($header->create_date)->format('d-m-Y') }}
                        </div>
                        <div class="fs-6">
                            <span class="fs-6 text-secondary">Created By : </span>
                            {{ $header->user->name }}
                        </div>
                        <div class="fs-6">
                            <span class="fs-6 text-secondary">Department : </span>
                            {{ $header->department->name }} ({{ $header->department->dept_no }})
                        </div>
                        <div class="mt-2">
                            @include('partials.formovertime-status', ['fot' => $header])

                        </div>
                    </div>
                    <hr>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover text-center table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="align-middle">No</th>
                                    <th class="align-middle">NIK</th>
                                    <th class="align-middle">Nama</th>
                                    <th class="align-middle">Job Description</th>
                                    <th class="align-middle">Start Date</th>
                                    <th class="align-middle">Start Time</th>
                                    <th class="align-middle">End Date</th>
                                    <th class="align-middle">End Time</th>
                                    <th class="align-middle">Break (Dalam Menit)</th>
                                    <th class="align-middle">Lama OT</th>
                                    </th>
                                    <th class="align-middle">Remark</th>
                                    @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
                                        <th class="align-middle">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($datas as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->NIK }}</td>
                                        <td>{{ $data->nama }}</td>
                                        <td>{{ $data->job_desc }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->start_date)->format('d-m-Y') }}</td>
                                        <td>{{ $data->start_time }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->end_date)->format('d-m-Y') }}</td>
                                        <td>{{ $data->end_time }}</td>
                                        <td>{{ $data->break }}</td>
                                        <td>
                                            @php
                                                // Parse the start and end datetime
                                                $start = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->start_date . ' ' . $data->start_time,
                                                );
                                                $end = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->end_date . ' ' . $data->end_time,
                                                );

                                                // Calculate the total minutes between start and end
                                                $totalMinutes = $start->diffInMinutes($end);

                                                // Subtract the break time (which is in minutes)
                                                $totalMinutesAfterBreak = $totalMinutes - $data->break;

                                                // Calculate the hours and minutes from the remaining total minutes
                                                $hours = floor($totalMinutesAfterBreak / 60);
                                                $minutes = $totalMinutesAfterBreak % 60;

                                                // Display the result
                                                echo "{$hours} hours {$minutes} minutes";
                                            @endphp
                                        </td>
                                        <td>{{ $data->remarks }}</td>
                                        @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
                                            <td> @include('partials.delete-confirmation-modal', [
                                                'id' => $data->id,
                                                'route' => 'formovertime.destroyDetail',
                                                'title' => 'Delete item detail',
                                                'body' => 'Are you sure want to delete this?',
                                            ])
                                                @if ($data->is_processed == 1 && $data->status === 'Approved')
                                                    <span class="text-success fw-bold">APPROVED</span>
                                                @elseif ($data->status === 'Rejected')
                                                    <span class="text-danger fw-bold">REJECTED</span>
                                                @else
                                                    <button class="btn btn-success btn-sm"
                                                        onclick="handleOvertimeAction({{ $data->id }}, 'approve')">
                                                        Approve
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="handleOvertimeAction({{ $data->id }}, 'reject')">
                                                        Reject
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12">No Data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($header->is_approve === 0)
        <div class="alert alert-danger mt-3" role="alert">
            <h4 class="alert-heading">Alasan Ditolak</h4>
            <textarea class="form-control-plaintext rounded-2 border border-danger p-2" rows="5" readonly>
                {{ $header->description }}</textarea>
        </div>
    @endif

    <script>
        function handleOvertimeAction(detailId, actionType) {
            if (!['approve', 'reject'].includes(actionType)) {
                alert('Aksi tidak valid.');
                return;
            }

            if (!confirm(`Yakin ingin ${actionType === 'approve' ? 'menyetujui' : 'menolak'} lembur ini?`)) {
                return;
            }

            fetch(`/push-overtime-detail/${detailId}?action=${actionType}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Berhasil diproses.');
                        location.reload(); // Refresh agar data update
                    } else {
                        alert(data.message || 'Gagal memproses.');
                        console.error(data);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat proses.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btnPushAll');
            if (!btn) return; // Kalau tombol tidak ada, jangan lanjutin

            btn.addEventListener('click', function() {
                const headerId = this.dataset.headerId;

                if (!confirm(
                        "Apakah Anda yakin ingin mem-push semua data detail yang belum ditolak (Rejected)?"
                    )) {
                    return;
                }

                // Buat loader jika ada
                const loader = document.getElementById('pushAllLoader');
                const result = document.getElementById('pushAllResult');

                if (loader) loader.classList.remove('hidden');
                if (result) result.innerText = '';

                fetch(`/overtime/push-all/${headerId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (loader) loader.classList.add('hidden');

                        if (data.success) {
                            result.innerHTML =
                                `<span class="text-green-600 font-semibold">✅ ${data.message}</span>`;
                        } else {
                            result.innerHTML =
                                `<span class="text-red-600 font-semibold">❌ ${data.message}</span>`;
                        }
                    })
                    .catch(error => {
                        if (loader) loader.classList.add('hidden');
                        result.innerHTML =
                            `<span class="text-red-600 font-semibold">❌ Terjadi kesalahan saat memproses.</span>`;
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@endsection
