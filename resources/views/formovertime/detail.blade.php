@extends('new.layouts.app')
@section('title', 'Detail Form Overtime')

@section('content')
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        $approvedCount = $datas->where('status', 'Approved')->count();
        $rejectedCount = $datas->where('status', 'Rejected')->count();
        $pendingCount = $datas->filter(fn($d) => !in_array($d->status, ['Approved', 'Rejected']))->count();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-6" x-data="overtimeDetailPage()" x-init="init()">

        {{-- ====== EDIT MODAL (FULLSCREEN) ====== --}}
        @include('partials.edit-form-overtime-modal', [
            'prheader' => $header,
            'datas' => $datas,
        ])

        {{-- ====== TOP BAR: BREADCRUMB + ACTIONS ====== --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1 text-xs sm:text-sm text-slate-500">
                        <li>
                            <a href="{{ route('overtime.index') }}"
                                class="inline-flex items-center gap-1 hover:text-slate-700">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Form Overtime</span>
                            </a>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li class="font-semibold text-slate-700">Detail</li>
                    </ol>
                </nav>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($header->status === 'waiting-creator' && $authUser->hasRole('super-admin'))
                    <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-overtime-edit-modal-{{ $header->id }}'))"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="bi bi-pencil-square text-[13px]"></i>
                        <span>Edit header & items</span>
                    </button>
                @endif

                @if ($header->status === 'waiting-director' && $authUser->specification->name === 'VERIFICATOR')
                    <a href="{{ route('export.overtime', $header->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs sm:text-sm font-medium text-emerald-700 hover:bg-emerald-100">
                        <i class="bi bi-file-earmark-excel text-xs"></i>
                        <span>Export to Excel</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ====== PUSH ALL TO JPAYROLL (VERIFICATOR ONLY) ====== --}}
        @if ($header->status === 'approved' && $authUser->specification->name === 'VERIFICATOR')
            <div class="mb-4 rounded-2xl border border-rose-100 bg-rose-50/70 p-4 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                            <i class="bi bi-upload text-base"></i>
                        </div>
                        <div class="text-sm">
                            <p class="font-semibold text-rose-700">Push all overtime details to JPayroll</p>
                            <p class="text-xs text-rose-600">
                                Hanya detail yang tidak berstatus
                                <span class="font-semibold">Rejected</span> yang akan dipush.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-1">
                        <button type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-rose-700 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="pushAllLoading" @click="pushAll({{ $header->id }})">
                            <span x-show="!pushAllLoading" class="inline-flex items-center gap-1.5">
                                <i class="bi bi-arrow-right-circle text-xs"></i>
                                <span>Push All to JPayroll</span>
                            </span>
                            <span x-show="pushAllLoading" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                                </svg>
                                <span>Processing...</span>
                            </span>
                        </button>
                        <p class="text-xs" :class="pushAllMessage.startsWith('✅') ? 'text-emerald-600' : 'text-rose-600'"
                            x-text="pushAllMessage"></p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ====== REJECTED ALERT (HEADER) ====== --}}
        @if ($header->status === 'rejected')
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <i class="bi bi-x-circle-fill text-xl text-rose-500"></i>
                    <h2 class="text-sm sm:text-base font-semibold text-rose-700">Form Rejected</h2>
                </div>
                <div class="h-px w-full bg-rose-100 mb-2"></div>
                <div class="mt-1 rounded-xl border-l-4 border-rose-400 bg-white/80 px-3 py-3 text-sm">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-rose-500">Reason</p>
                    <p class="whitespace-pre-wrap text-slate-700">
                        {{ $header->description ?? 'No reason provided.' }}
                    </p>
                </div>
            </div>
        @endif

        {{-- ====== APPROVAL & SIGNATURE STRIP ====== --}}
        @include('partials.overtime-form-autographs')

        {{-- ====== MAIN CARD ====== --}}
        <div class="mt-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-4 sm:px-6 sm:py-5">

                {{-- TITLE + STATUS BADGE --}}
                <div class="mb-4 flex flex-col items-center gap-2 text-center">
                    <h1 class="text-lg font-semibold text-slate-800 sm:text-xl">Form Overtime</h1>
                    <x-overtime-form-status-badge :status="$header->status" />
                </div>

                {{-- HEADER SUMMARY + QUICK STATS (friendlier) --}}
                <div class="mb-5 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 sm:p-5">
                    <div class="grid gap-4 md:grid-cols-3 md:items-stretch">
                        {{-- Left: header info (2/3) --}}
                        <div class="md:col-span-2 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        Form info
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        Basic information about this overtime form.
                                    </p>
                                </div>

                                <span
                                    class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold text-white shadow-sm">
                                    ID #{{ $header->id }}
                                </span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 text-xs sm:text-sm">
                                {{-- Created by --}}
                                <div class="rounded-xl bg-white/80 px-3 py-2.5 shadow-sm ring-1 ring-slate-100">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                                        Created by
                                    </p>
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $header->user->name }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $header->created_at->timezone('Asia/Jakarta')->format('d-m-Y · H:i') }}
                                    </p>
                                </div>

                                {{-- Department --}}
                                <div class="rounded-xl bg-white/80 px-3 py-2.5 shadow-sm ring-1 ring-slate-100">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                                        Department
                                    </p>
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $header->department->name }}
                                    </p>
                                </div>

                                {{-- After hour flag --}}
                                <div class="rounded-xl bg-white/80 px-3 py-2.5 shadow-sm ring-1 ring-slate-100">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 mb-1">
                                        After hour overtime
                                    </p>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold
                        {{ $header->is_after_hour ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-700' }}">
                                        @if ($header->is_after_hour)
                                            <span class="text-xs">🌙</span>
                                            <span>Yes, after working hours</span>
                                        @else
                                            <span class="text-xs">☀️</span>
                                            <span>No, normal working time</span>
                                        @endif
                                    </span>
                                </div>

                                {{-- Optional: you can use this slot for Branch or something else later --}}
                                <div
                                    class="rounded-xl bg-white/40 px-3 py-2.5 border border-dashed border-slate-200 text-[11px] text-slate-400 flex items-center justify-center">
                                    Additional info can go here (e.g. Branch, Design, etc.)
                                </div>
                            </div>
                        </div>

                        {{-- Right: quick stats (1/3) --}}
                        <div class="flex flex-col justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    Detail summary
                                </p>
                                <p class="text-xs text-slate-500">
                                    Quick overview of all overtime lines in this form.
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs sm:text-sm">
                                {{-- Approved --}}
                                <div class="rounded-xl bg-emerald-50 px-2 py-2.5 shadow-sm ring-1 ring-emerald-100">
                                    <p
                                        class="flex items-center justify-center gap-1 text-[11px] font-medium text-emerald-700">
                                        <span>✅</span><span>Approved</span>
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-emerald-800">
                                        {{ $approvedCount }}
                                    </p>
                                </div>

                                {{-- Rejected --}}
                                <div class="rounded-xl bg-rose-50 px-2 py-2.5 shadow-sm ring-1 ring-rose-100">
                                    <p class="flex items-center justify-center gap-1 text-[11px] font-medium text-rose-700">
                                        <span>❌</span><span>Rejected</span>
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-rose-800">
                                        {{ $rejectedCount }}
                                    </p>
                                </div>

                                {{-- Pending --}}
                                <div class="rounded-xl bg-amber-50 px-2 py-2.5 shadow-sm ring-1 ring-amber-100">
                                    <p
                                        class="flex items-center justify-center gap-1 text-[11px] font-medium text-amber-700">
                                        <span>⏳</span><span>Pending</span>
                                    </p>
                                    <p class="mt-1 text-lg font-bold text-amber-800">
                                        {{ $pendingCount }}
                                    </p>
                                </div>
                            </div>

                            {{-- Legend --}}
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span class="font-semibold text-slate-600">Legend:</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Approved
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span> Rejected
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full bg-amber-400"></span> Pending
                                </span>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Legend --}}
                <div class="mb-3 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                    <span class="font-semibold mr-1">Legend:</span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[11px] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Row = Approved
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full border border-rose-100 bg-rose-50 px-2 py-0.5 text-[11px] text-rose-700">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span> Row = Rejected
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full border border-amber-100 bg-amber-50 px-2 py-0.5 text-[11px] text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span> Row = Pending
                    </span>
                </div>

                <div class="h-px w-full bg-slate-100 mb-3"></div>

                {{-- ====== DETAILS DESKTOP TABLE (md+) ====== --}}
                <div class="hidden md:block">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <tr class="text-center">
                                    <th class="px-2 py-2">No</th>
                                    <th class="px-2 py-2">NIK</th>
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-2 py-2">Overtime Date</th>
                                    <th class="px-3 py-2 text-left">Job Description</th>
                                    <th class="px-2 py-2">Start Date</th>
                                    <th class="px-2 py-2">Start Time</th>
                                    <th class="px-2 py-2">End Date</th>
                                    <th class="px-2 py-2">End Time</th>
                                    <th class="px-2 py-2">Break (Minutes)</th>
                                    <th class="px-2 py-2">Lama OT</th>
                                    <th class="px-3 py-2 text-left">Remark</th>

                                    @if ($header->status === 'approved' && $authUser->specification->name === 'VERIFICATOR')
                                        <th class="px-2 py-2">Action</th>
                                    @else
                                        <th class="px-2 py-2">Status JPayroll</th>
                                    @endif

                                    <th class="px-3 py-2 text-left">Reason</th>
                                    <th class="px-2 py-2">Voucher</th>
                                    <th class="px-2 py-2">In Date</th>
                                    <th class="px-2 py-2">In Time</th>
                                    <th class="px-2 py-2">Out Date</th>
                                    <th class="px-2 py-2">Out Time</th>
                                    <th class="px-2 py-2">Nett Hour</th>

                                    @if ($authUser->id === $header->user_id)
                                        <th class="px-2 py-2">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($datas as $data)
                                    @php
                                        $rowBg = match ($data->status) {
                                            'Approved' => 'bg-emerald-50/60',
                                            'Rejected' => 'bg-rose-50/60',
                                            default => 'bg-amber-50/60',
                                        };
                                    @endphp
                                    <tr class="text-center text-[11px] {{ $rowBg }} hover:bg-slate-50">
                                        <td class="px-2 py-2">{{ $loop->iteration }}</td>
                                        <td class="px-2 py-2">{{ $data->NIK }}</td>
                                        <td class="px-3 py-2 text-left font-medium text-slate-800">{{ $data->name }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ $data->overtime_date ? \Carbon\Carbon::parse($data->overtime_date)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-left text-slate-700">{{ $data->job_desc }}</td>
                                        <td class="px-2 py-2">
                                            {{ \Carbon\Carbon::parse($data->start_date)->format('d-m-Y') }}
                                        </td>
                                        <td class="px-2 py-2">{{ $data->start_time }}</td>
                                        <td class="px-2 py-2">
                                            {{ \Carbon\Carbon::parse($data->end_date)->format('d-m-Y') }}
                                        </td>
                                        <td class="px-2 py-2">{{ $data->end_time }}</td>
                                        <td class="px-2 py-2">{{ $data->break }}</td>
                                        <td class="px-2 py-2">
                                            @php
                                                $start = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->start_date . ' ' . $data->start_time,
                                                );
                                                $end = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->end_date . ' ' . $data->end_time,
                                                );
                                                $totalMinutes = $start->diffInMinutes($end);
                                                $totalMinutesAfterBreak = $totalMinutes - $data->break;
                                                $hours = floor($totalMinutesAfterBreak / 60);
                                                $minutes = $totalMinutesAfterBreak % 60;
                                                echo "{$hours}h {$minutes}m";
                                            @endphp
                                        </td>
                                        <td class="px-3 py-2 text-left text-slate-700">{{ $data->remarks }}</td>

                                        {{-- VERIFICATOR VIEW --}}
                                        @if ($header->status === 'approved' && $authUser->specification->name === 'VERIFICATOR')
                                            <td class="px-2 py-2 text-left">
                                                @include('partials.delete-confirmation-modal', [
                                                    'id' => $data->id,
                                                    'route' => 'formovertime.destroyDetail',
                                                    'title' => 'Delete item detail',
                                                    'body' => 'Are you sure want to delete this?',
                                                ])

                                                @if ($data->is_processed == 1 && $data->status === 'Approved')
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                        <i class="bi bi-check-circle"></i> Approved
                                                    </span>
                                                @elseif ($data->status === 'Rejected')
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                                        <i class="bi bi-x-circle"></i> Rejected
                                                    </span>
                                                @else
                                                    <div class="flex flex-col gap-1">
                                                        <div class="flex flex-wrap gap-1">
                                                            <button type="button"
                                                                class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700"
                                                                @click="handleDetailAction({{ $data->id }}, 'approve')">
                                                                Approve
                                                            </button>
                                                            <button type="button"
                                                                class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-rose-700"
                                                                @click="handleDetailAction({{ $data->id }}, 'reject')">
                                                                Reject
                                                            </button>
                                                        </div>

                                                        @if ($data->status !== 'Rejected')
                                                            <form method="POST"
                                                                action="{{ route('overtime-detail.reject-server-side', $data->id) }}"
                                                                class="inline"
                                                                onsubmit="return confirm('Are you sure you want to reject this overtime detail? (Server side)');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="text-[11px] font-medium text-rose-600 underline-offset-2 hover:underline">
                                                                    Reject (Server side)
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @else
                                            {{-- NORMAL STATUS BADGE --}}
                                            <td class="px-2 py-2">
                                                @if ($data->status === 'Approved')
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                        <i class="bi bi-check-circle"></i> Approved
                                                    </span>
                                                @elseif ($data->status === 'Rejected')
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                                        <i class="bi bi-x-circle"></i> Rejected
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                                        <i class="bi bi-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="px-3 py-2 text-left text-slate-700">
                                            {{ $data->reason ?? '-' }}
                                        </td>

                                        {{-- ACTUAL OVERTIME (JPAYROLL SYNC) --}}
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->voucher ?? '-' }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->in_date
                                                ? \Carbon\Carbon::parse($data->actualOvertimeDetail->in_date)->format('d-m-Y')
                                                : '-' }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->in_time ?? '-' }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->out_date
                                                ? \Carbon\Carbon::parse($data->actualOvertimeDetail->out_date)->format('d-m-Y')
                                                : '-' }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->out_time ?? '-' }}
                                        </td>
                                        <td class="px-2 py-2">
                                            {{ optional($data->actualOvertimeDetail)->nett_overtime ?? '-' }}
                                        </td>

                                        {{-- CREATOR-ONLY DELETE --}}
                                        @if ($authUser->id === $header->user_id)
                                            <td class="px-2 py-2">
                                                <form method="POST"
                                                    action="{{ route('formovertime.destroyDetail', $data->id) }}"
                                                    class="inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this overtime detail?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-md border border-rose-200 px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">
                                                        <i class="bi bi-trash"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="21" class="px-4 py-6 text-center text-xs text-slate-500">
                                            <div class="flex flex-col items-center gap-1">
                                                <i class="bi bi-inbox text-xl"></i>
                                                <span>No Data</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ====== MOBILE CARD VIEW (< md) ====== --}}
                <div class="space-y-3 md:hidden mt-3">
                    @forelse ($datas as $data)
                        @php
                            [$cardBg, $borderColor, $pillBg, $pillText] = match ($data->status) {
                                'Approved' => [
                                    'bg-emerald-50',
                                    'border-emerald-200',
                                    'bg-emerald-100',
                                    'text-emerald-700',
                                ],
                                'Rejected' => ['bg-rose-50', 'border-rose-200', 'bg-rose-100', 'text-rose-700'],
                                default => ['bg-amber-50', 'border-amber-200', 'bg-amber-100', 'text-amber-700'],
                            };
                        @endphp

                        <div class="rounded-2xl border {{ $borderColor }} {{ $cardBg }} p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-medium text-slate-500">#{{ $loop->iteration }}</p>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $data->name }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $data->NIK }}</p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $pillBg }} {{ $pillText }}">
                                        @if ($data->status === 'Approved')
                                            <i class="bi bi-check-circle"></i>
                                        @elseif ($data->status === 'Rejected')
                                            <i class="bi bi-x-circle"></i>
                                        @else
                                            <i class="bi bi-clock"></i>
                                        @endif
                                        <span>{{ $data->status ?? 'Pending' }}</span>
                                    </span>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        OT Date:
                                        <span class="font-medium">
                                            {{ $data->overtime_date ? \Carbon\Carbon::parse($data->overtime_date)->format('d-m-Y') : '-' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                                <div class="space-y-1">
                                    <div>
                                        <p class="font-medium text-slate-500">Job Desc</p>
                                        <p class="text-slate-700">{{ $data->job_desc }}</p>
                                    </div>

                                    <div>
                                        <p class="font-medium text-slate-500">Start</p>
                                        <p>
                                            {{ \Carbon\Carbon::parse($data->start_date)->format('d-m-Y') }}
                                            · {{ $data->start_time }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="font-medium text-slate-500">End</p>
                                        <p>
                                            {{ \Carbon\Carbon::parse($data->end_date)->format('d-m-Y') }}
                                            · {{ $data->end_time }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <div>
                                        <p class="font-medium text-slate-500">Break</p>
                                        <p>{{ $data->break }} minutes</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-500">Lama OT</p>
                                        <p>
                                            @php
                                                $start = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->start_date . ' ' . $data->start_time,
                                                );
                                                $end = \Carbon\Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $data->end_date . ' ' . $data->end_time,
                                                );
                                                $totalMinutes = $start->diffInMinutes($end);
                                                $totalMinutesAfterBreak = $totalMinutes - $data->break;
                                                $hours = floor($totalMinutesAfterBreak / 60);
                                                $minutes = $totalMinutesAfterBreak % 60;
                                                echo "{$hours}h {$minutes}m";
                                            @endphp
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-500">Remark</p>
                                        <p class="text-slate-700">{{ $data->remarks }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Reason + JPayroll info --}}
                            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                                <div class="space-y-1">
                                    <p class="font-medium text-slate-500">Reason</p>
                                    <p class="text-slate-700">
                                        {{ $data->reason ?? '-' }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="font-medium text-slate-500">JPayroll</p>
                                    <p>Voucher: {{ optional($data->actualOvertimeDetail)->voucher ?? '-' }}</p>
                                    <p>
                                        In:
                                        @if (optional($data->actualOvertimeDetail)->in_date)
                                            {{ \Carbon\Carbon::parse($data->actualOvertimeDetail->in_date)->format('d-m-Y') }}
                                            · {{ $data->actualOvertimeDetail->in_time }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    <p>
                                        Out:
                                        @if (optional($data->actualOvertimeDetail)->out_date)
                                            {{ \Carbon\Carbon::parse($data->actualOvertimeDetail->out_date)->format('d-m-Y') }}
                                            · {{ $data->actualOvertimeDetail->out_time }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    <p>Nett: {{ optional($data->actualOvertimeDetail)->nett_overtime ?? '-' }}</p>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                {{-- Verificator actions --}}
                                @if ($header->status === 'approved' && $authUser->specification->name === 'VERIFICATOR')
                                    <div class="flex flex-wrap items-center gap-1">
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $data->id,
                                            'route' => 'formovertime.destroyDetail',
                                            'title' => 'Delete item detail',
                                            'body' => 'Are you sure want to delete this?',
                                        ])

                                        @if ($data->is_processed == 1 && $data->status === 'Approved')
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                <i class="bi bi-check-circle"></i> Approved
                                            </span>
                                        @elseif ($data->status === 'Rejected')
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                                <i class="bi bi-x-circle"></i> Rejected
                                            </span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700"
                                                    @click="handleDetailAction({{ $data->id }}, 'approve')">
                                                    Approve
                                                </button>
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-rose-700"
                                                    @click="handleDetailAction({{ $data->id }}, 'reject')">
                                                    Reject
                                                </button>

                                                @if ($data->status !== 'Rejected')
                                                    <form method="POST"
                                                        action="{{ route('overtime-detail.reject-server-side', $data->id) }}"
                                                        class="inline"
                                                        onsubmit="return confirm('Are you sure you want to reject this overtime detail? (Server side)');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-[11px] font-medium text-rose-600 underline-offset-2 hover:underline">
                                                            Reject (Server side)
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Normal badge --}}
                                    <div>
                                        @if ($data->status === 'Approved')
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                <i class="bi bi-check-circle"></i> Approved
                                            </span>
                                        @elseif ($data->status === 'Rejected')
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                                                <i class="bi bi-x-circle"></i> Rejected
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Creator delete --}}
                                @if ($authUser->id === $header->user_id)
                                    <form method="POST" action="{{ route('formovertime.destroyDetail', $data->id) }}"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this overtime detail?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">
                                            <i class="bi bi-trash"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div
                            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-xs text-slate-500">
                            <i class="bi bi-inbox text-2xl mb-1"></i>
                            <span>No Data</span>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

    {{-- ====== Alpine JS STATE & ACTIONS ====== --}}
    <script>
        function overtimeDetailPage() {
            return {
                mobile: window.innerWidth < 768,
                pushAllLoading: false,
                pushAllMessage: '',

                init() {
                    window.addEventListener('resize', () => {
                        this.mobile = window.innerWidth < 768;
                    });
                },

                async pushAll(headerId) {
                    if (!confirm("Apakah Anda yakin ingin mem-push semua data detail yang belum ditolak (Rejected)?")) {
                        return;
                    }

                    this.pushAllLoading = true;
                    this.pushAllMessage = 'Processing...';

                    try {
                        const res = await fetch(`/overtime/push-all/${headerId}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.pushAllMessage = `✅ ${data.message ?? 'Berhasil diproses.'}`;
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.pushAllMessage = `❌ ${data.message ?? 'Gagal memproses.'}`;
                        }
                    } catch (e) {
                        console.error(e);
                        this.pushAllMessage = '❌ Terjadi kesalahan saat memproses.';
                    } finally {
                        this.pushAllLoading = false;
                    }
                },

                async handleDetailAction(detailId, actionType) {
                    if (!['approve', 'reject'].includes(actionType)) {
                        alert('Aksi tidak valid.');
                        return;
                    }

                    if (!confirm(`Yakin ingin ${actionType === 'approve' ? 'menyetujui' : 'menolak'} lembur ini?`)) {
                        return;
                    }

                    try {
                        const res = await fetch(`/push-overtime-detail/${detailId}?action=${actionType}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const data = await res.json();

                        if (data.success) {
                            alert(data.message || 'Berhasil diproses.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal memproses.');
                            console.error(data);
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan saat proses.');
                    }
                },
            };
        }
    </script>
@endsection
