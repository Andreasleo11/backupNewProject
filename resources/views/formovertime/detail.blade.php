@extends('layouts.app')
@section('title', 'Detail Form Overtime - ' . env('APP_NAME'))
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

    @include('partials.formovertime-autographs')

    @if ($header->is_approve === 0)
        <div class="container my-4">
            <div class="alert alert-danger shadow border-0 position-relative">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-x-circle-fill fs-3 me-2 text-danger"></i>
                    <h5 class="mb-0 fw-bold text-danger">Form Rejected</h5>
                </div>
                <hr class="my-2">

                <div class="bg-white border-start border-4 border-danger rounded-3 p-3 mt-3">
                    <p class="mb-0 text-secondary fw-semibold">Reason:</p>
                    <div class="text-dark lh-base" style="white-space: pre-wrap;">
                        {{ $header->description ?? 'No reason provided.' }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-5 container">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <span class="h1 fw-semibold">Form Overtime</span>
                        <br>
                        <div><span class="text-secondary">ID: </span> {{ $header->id }}</div>
                        <div class="fs-6 mt-1">
                            <span class="fs-6 text-secondary">Created At : </span>
                            {{ \Carbon\Carbon::parse($header->created_at)->format('d-m-Y') }}
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
                                    <th class="align-middle">Name</th>
                                    <th class="align-middle">Overtime Date</th>
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
                                    @else
                                        <th class="align-middle">Status Jpayroll</th>
                                     
                                    @endif
                                       <th class="align-middle">Reason</th>
                                       <th class="align-middle">Voucher</th>
                                        <th class="align-middle">In Date</th>
                                        <th class="align-middle">In Time</th>
                                        <th class="align-middle">Out Date</th>
                                        <th class="align-middle">Out Time</th>
                                        <th class="align-middle">Nett Hour</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($datas as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->NIK }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->overtime_date ? \Carbon\Carbon::parse($data->overtime_date)->format('d-m-Y') : '-' }} </td>
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
                                        @else
                                            <td>
                                                @if ($data->status === 'Approved')
                                                    <span class="text-success fw-bold">APPROVED</span>
                                                @elseif ($data->status === 'Rejected')
                                                    <span class="text-danger fw-bold">REJECTED</span>
                                                @else
                                                    <span class="text-warning fw-bold">PENDING</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            {{ $data->reason ?? '-' }}
                                        </td>
                                       <td>{{ optional($data->actualOvertimeDetail)->voucher ?? '-' }}</td>
                                        <td>
                                            {{ optional($data->actualOvertimeDetail)->in_date 
                                                ? \Carbon\Carbon::parse($data->actualOvertimeDetail->in_date)->format('d-m-Y') 
                                                : '-' }}
                                        </td>
                                        <td>{{ optional($data->actualOvertimeDetail)->in_time ?? '-' }}</td>
                                        <td>
                                            {{ optional($data->actualOvertimeDetail)->out_date 
                                                ? \Carbon\Carbon::parse($data->actualOvertimeDetail->out_date)->format('d-m-Y') 
                                                : '-' }}
                                        </td>
                                        <td>{{ optional($data->actualOvertimeDetail)->out_time ?? '-' }}</td>
                                        <td>{{ optional($data->actualOvertimeDetail)->nett_overtime ?? '-' }}</td>
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
