@extends('new.layouts.app')

@section('content')
    @php
        $authUser = auth()->user();

        $canEdit =
            in_array($authUser->department->name, ['COMPUTER', 'MAINTENANCE', 'MAINTENANCE MACHINE', 'PERSONALIA']) &&
            !in_array($report->status_laporan, [4, 5]);

        $canUpload = $report->pelapor === $authUser->name || $report->status_laporan === 2;

        $tanggalMulai = \Carbon\Carbon::parse($report->tanggal_mulai);
        $tanggalSelesai = \Carbon\Carbon::parse($report->tanggal_selesai);

        if ($report->tanggal_mulai && $report->tanggal_selesai) {
            if ($tanggalMulai->isSameDay($tanggalSelesai)) {
                $difference = $tanggalMulai->diff($tanggalSelesai);
                $lamaPengerjaan = $difference->format('%h Jam %I Menit');
            } else {
                $totalDays = $tanggalMulai->diffInDays($tanggalSelesai);
                $tanggalMulaiPlusOneDay = (clone $tanggalMulai)->addDay();
                $remainingTime = $tanggalMulaiPlusOneDay->diff($tanggalSelesai);

                $lamaPengerjaan = $totalDays . ' Hari ' . $remainingTime->format('%H Jam %I Menit');
            }
        } else {
            $lamaPengerjaan = '-';
        }
    @endphp

    <div class="max-w-5xl mx-auto px-4 py-6 lg:py-8" x-data="spkDetail()">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <li>
                    <a href="{{ route('spk.index') }}" class="hover:text-slate-700 font-medium">
                        SPK List
                    </a>
                </li>
                <li class="text-slate-400">/</li>
                <li class="font-semibold text-slate-800">
                    Detail
                </li>
            </ol>
        </nav>

        {{-- Header row --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">
                    {{ $report->no_dokumen }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Surat Perintah Kerja – detail laporan & progres pengerjaan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                {{-- Upload related files (Tailwind / Alpine modal) --}}
                @if ($canUpload)
                    @include('partials.upload-files-modal', ['doc_id' => $report->no_dokumen])

                    <button type="button" @click="openUploadFiles = true"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class='bx bx-upload text-base mr-1.5'></i>
                        Upload related files
                    </button>
                @endif
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-4 py-4 sm:px-6 sm:py-5">

                {{-- Edit mode banner --}}
                <div class="mb-4 flex items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs sm:text-sm text-amber-900"
                    x-show="editMode" x-cloak>
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-none" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 2a1 1 0 01.894.553l7 14A1 1 0 0116 18H4a1 1 0 01-.894-1.447l7-14A1 1 0 019 2zm0 4a1 1 0 00-.894.553L6.382 10H9a1 1 0 010 2H5.618l-1.236 2.474A1 1 0 005 16h10a1 1 0 00.894-1.447l-1.236-2.474H11a1 1 0 010-2h2.618l-1.724-3.447A1 1 0 0011 6H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold text-amber-900">Edit mode aktif</p>
                            <p class="text-[11px] leading-snug">
                                Field yang bisa diedit diberi highlight <span class="font-semibold">background biru</span>
                                dan label bertanda
                                <span class="font-semibold">“Editable”</span>.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="toggleEditMode"
                        class="inline-flex items-center rounded-full border border-amber-300 bg-amber-100 px-2.5 py-1 text-[11px] font-medium hover:bg-amber-200">
                        Keluar edit
                    </button>
                </div>

                {{-- Existing Bootstrap modals (keep) --}}
                @include('partials.ask-a-revision-modal')

                @include('partials.confirmation-modal', [
                    'id' => $report->id,
                    'title' => 'Finish this SPK',
                    'body' => "Are you sure want to finish this <strong>$report->no_dokumen</strong>?",
                    'submitButton' =>
                        '<button type="submit" class="btn btn-primary" onclick="document.getElementById(\'formFinishSPK\').submit()">Confirm</button>',
                ])

                <form id="formFinishSPK" action="{{ route('spk.finish', $report->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('PUT')
                </form>

                {{-- Top meta + actions --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-5">

                    {{-- Left: Title + meta --}}
                    <div class="space-y-3 text-xs sm:text-sm text-slate-600">
                        {{-- Title + status badge --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-semibold text-slate-900">
                                Surat Perintah Kerja
                                <span class="ml-1 text-[11px] font-normal text-slate-400">
                                    • {{ $report->no_dokumen }}
                                </span>
                            </h2>

                            {{-- Status pill --}}
                            <div class="inline-flex">
                                @include('partials.spk-status', [
                                    'status' => $report->status_laporan,
                                    'is_urgent' => $report->is_urgent,
                                ])
                            </div>
                        </div>

                        {{-- Meta info as definition list --}}
                        <dl class="grid grid-cols-1 gap-y-1 gap-x-6 sm:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <dt class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">
                                    Tanggal Lapor
                                </dt>
                                <dd class="text-xs sm:text-[13px] text-slate-700">
                                    {{ \Carbon\Carbon::parse($report->tanggal_lapor)->translatedFormat('d F Y H:i:s') }}
                                </dd>
                            </div>

                            <div class="flex items-center gap-2">
                                <dt class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">
                                    Dibuat Pada
                                </dt>
                                <dd class="text-xs sm:text-[13px] text-slate-700">
                                    {{ \Carbon\Carbon::parse($report->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                </dd>
                            </div>

                            <div class="flex items-center gap-2">
                                <dt class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">
                                    Diupdate Pada
                                </dt>
                                <dd class="text-xs sm:text-[13px] text-slate-700">
                                    {{ \Carbon\Carbon::parse($report->updated_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                </dd>
                            </div>

                            @if ($report->tanggal_selesai)
                                <div class="flex items-center gap-2">
                                    <dt class="text-[11px] font-medium text-slate-500 uppercase tracking-wide">
                                        Selesai Pada
                                    </dt>
                                    <dd class="text-xs sm:text-[13px] text-slate-700">
                                        {{ \Carbon\Carbon::parse($report->tanggal_selesai)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Right: Actions --}}
                    <div class="flex flex-col items-stretch sm:items-end gap-2 text-xs sm:text-[13px]">

                        {{-- Pelapor: Ask revision / Finish (status DONE) --}}
                        @if ($report->status_laporan === 4 && $authUser->name === $report->pelapor)
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 shadow-sm w-full sm:w-auto">
                                <p class="mb-2 text-[11px] font-medium uppercase tracking-wide text-slate-500">
                                    Actions for reporter
                                </p>

                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Ask revision --}}
                                    <button type="button" x-on:click="$dispatch('open-ask-revision-{{ $report->id }}')"
                                        class="inline-flex items-center rounded-lg border border-indigo-600 bg-white px-3 py-1.5 text-[11px] font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                        Ask a revision
                                    </button>

                                    <span class="text-[11px] text-slate-400">or</span>

                                    {{-- Finish -> sinkron dengan confirmation-modal id = "finish-{{ $report->id }}" --}}
                                    <button type="button"
                                        x-on:click="$dispatch('open-confirmation-finish-{{ $report->id }}')"
                                        class="inline-flex items-center rounded-lg border border-emerald-600 bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                        Finish SPK
                                    </button>
                                </div>

                                <p class="mt-1 text-[11px] text-slate-500">
                                    Menandai SPK <span class="font-medium">{{ $report->no_dokumen }}</span> sebagai
                                    selesai.
                                </p>
                            </div>
                        @endif

                        {{-- Edit toggle --}}
                        @if ($canEdit)
                            <div class="flex flex-col items-stretch sm:items-end gap-1">
                                <button type="button" id="editButton" @click="toggleEditMode"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-[11px] font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                    :class="editMode
                                        ?
                                        'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700' :
                                        'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M15.586 3.586a2 2 0 00-2.828 0L4 12.343V16h3.657l8.758-8.758a2 2 0 000-2.828z" />
                                    </svg>
                                    <span x-text="editMode ? 'Cancel edit' : 'Edit fields'"></span>
                                </button>

                                <p class="text-[11px] text-slate-500" x-show="!editMode">
                                    Klik <span class="font-medium">Edit fields</span> untuk mengubah isi laporan.
                                </p>
                                <p class="text-[11px] text-indigo-600 flex items-center gap-1" x-show="editMode">
                                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                    Edit mode aktif — hanya field dengan border biru yang bisa diubah.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>


                {{-- FORM --}}
                <form action="{{ route('spk.update', $report->id) }}" method="post" class="space-y-5">
                    @method('PUT')
                    @csrf

                    {{-- BASIC INFO (readonly group) --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-500 flex items-center gap-1">
                                <span>No Dokumen</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M10 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2z" />
                                    </svg>
                                    Read only
                                </span>
                            </label>
                            <input type="text"
                                class="block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                                value="{{ $report->no_dokumen }}" readonly>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-500 flex items-center gap-1">
                                <span>Pelapor</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M10 2a4 4 0 00-4 4v1a4 4 0 108 0V6a4 4 0 00-4-4zM4 14a4 4 0 014-4h4a4 4 0 014 4v1H4v-1z" />
                                    </svg>
                                    Read only
                                </span>
                            </label>
                            <input type="text"
                                class="block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                                value="{{ $report->pelapor }}" readonly>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-500 flex items-center gap-1">
                                <span>Requested By</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd"
                                            d="M5 6a3 3 0 10-2.236 5.06A7.004 7.004 0 0010 18h1a1 1 0 100-2h-1a5.002 5.002 0 00-4.9-4.001A3 3 0 005 6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span x-text="editMode ? 'Editable' : 'Locked until edit'"></span>
                                </span>
                            </label>
                            <input type="text" name="requested_by" id="requested_by"
                                value="{{ $report->requested_by }}"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-500 flex items-center gap-1">
                                <span>From Department</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    Read only
                                </span>
                            </label>
                            <input type="text" value="{{ $report->from_department }}"
                                class="block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                                readonly>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-500 flex items-center gap-1">
                                <span>To Department</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    Read only
                                </span>
                            </label>
                            <input type="text" value="{{ $report->to_department }}"
                                class="block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                                readonly>
                        </div>
                    </div>

                    <hr class="border-slate-200">

                    {{-- LAPORAN & TEKNIS --}}
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="judul_laporan"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Judul Laporan</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                    </svg>
                                    <span x-text="editMode ? 'Editable' : 'Click Edit to modify'"></span>
                                </span>
                            </label>
                            <input type="text" name="judul_laporan" id="judul_laporan"
                                value="{{ $report->judul_laporan }}"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">
                        </div>

                        <div class="space-y-1.5">
                            <label for="keterangan_laporan"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Keterangan Laporan</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                    </svg>
                                    <span x-text="editMode ? 'Editable' : 'Click Edit to modify'"></span>
                                </span>
                            </label>
                            <textarea name="keterangan_laporan" id="keterangan_laporan" rows="4"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 resize-y"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">{{ $report->keterangan_laporan }}</textarea>
                        </div>

                        @if ($report->to_department === 'MAINTENANCE MACHINE')
                            <div class="space-y-1.5">
                                <span class="block text-xs font-medium text-slate-600">
                                    For
                                </span>
                                <div class="flex flex-wrap gap-4 mt-1 text-xs text-slate-700">
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="for" id="inlineRadioMould" value="mould"
                                            @checked(old('for', $report->for ?? '') === 'mould') x-bind:disabled="!editMode">
                                        <span>Mould</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="for" id="inlineRadioMachine" value="machine"
                                            @checked(old('for', $report->for ?? '') === 'machine') x-bind:disabled="!editMode">
                                        <span>Machine</span>
                                    </label>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Pilih target pengerjaan untuk mem-filter daftar PIC.
                                </p>
                            </div>
                        @endif

                        {{-- PIC --}}
                        <div class="space-y-1.5">
                            <label for="pic"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>PIC</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M10 2a4 4 0 00-4 4v1a4 4 0 108 0V6a4 4 0 00-4-4zM4 14a4 4 0 014-4h4a4 4 0 014 4v1H4v-1z" />
                                    </svg>
                                    <span x-text="editMode ? 'Editable (TomSelect)' : 'Click Edit to modify'"></span>
                                </span>
                            </label>
                            <select name="pic" id="pic"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                                disabled>
                                <option value="">--Select PIC--</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->name }}"
                                        {{ isset($report->pic) && $report->pic == $user->name ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tindakan --}}
                        <div class="space-y-1.5">
                            <label for="tindakan"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Tindakan</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                    </svg>
                                    <span x-text="editMode ? 'Editable' : 'Click Edit to modify'"></span>
                                </span>
                            </label>
                            <textarea name="tindakan" id="tindakan" rows="4"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 resize-y"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">{{ $report->tindakan }}</textarea>
                        </div>
                    </div>

                    {{-- REMARKS --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-600">
                                Remarks
                            </label>
                            <div class="border border-slate-200 rounded-xl bg-slate-50/60 px-3 py-2 max-h-52 overflow-y-auto text-xs"
                                :class="remarksExpanded ? 'max-h-none' : 'max-h-52'">
                                @if ($report->spkRemarks->isEmpty())
                                    <p class="text-slate-500">
                                        No remarks available.
                                    </p>
                                @else
                                    <ul class="space-y-2">
                                        @foreach ($report->spkRemarks as $remark)
                                            <li
                                                class="rounded-lg bg-white border border-slate-200 px-3 py-2 text-slate-700">
                                                @include('partials.spk-status', [
                                                    'status' => $remark->status,
                                                    'is_urgent' => $report->is_urgent,
                                                ])
                                                <div class="mt-1">
                                                    <span class="font-semibold text-slate-800">Remark:</span>
                                                    <span class="ml-1">{{ $remark->remarks }}</span>
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-slate-500">
                                                    Date:
                                                    {{ \Carbon\Carbon::parse($remark->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            @if ($report->spkRemarks->count() > 3)
                                <button type="button"
                                    class="mt-1 text-[11px] font-medium text-indigo-600 hover:text-indigo-700"
                                    @click="remarksExpanded = !remarksExpanded"
                                    x-text="remarksExpanded ? 'Show less' : 'Show all remarks'"></button>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-600">
                                Revision Remarks
                            </label>
                            <div class="border border-slate-200 rounded-xl bg-slate-50/60 px-3 py-2 max-h-52 overflow-y-auto text-xs"
                                :class="revisionRemarksExpanded ? 'max-h-none' : 'max-h-52'">
                                @if ($report->revisionRemarks->isEmpty())
                                    <p class="text-slate-500">
                                        No revision remarks available.
                                    </p>
                                @else
                                    <ul class="space-y-2">
                                        @foreach ($report->revisionRemarks as $remark)
                                            <li
                                                class="rounded-lg bg-white border border-slate-200 px-3 py-2 text-slate-700">
                                                @include('partials.spk-status', [
                                                    'status' => $remark->status,
                                                    'is_urgent' => $report->is_urgent,
                                                ])
                                                <div class="mt-1">
                                                    <span class="font-semibold text-slate-800">Remark:</span>
                                                    <span class="ml-1">{{ $remark->remarks }}</span>
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-slate-500">
                                                    Date:
                                                    {{ \Carbon\Carbon::parse($remark->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            @if ($report->revisionRemarks->count() > 3)
                                <button type="button"
                                    class="mt-1 text-[11px] font-medium text-indigo-600 hover:text-indigo-700"
                                    @click="revisionRemarksExpanded = !revisionRemarksExpanded"
                                    x-text="revisionRemarksExpanded ? 'Show less' : 'Show all revision remarks'"></button>
                            @endif
                        </div>
                    </div>

                    {{-- DATES & DURATION --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label for="tanggal_mulai"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Tanggal Mulai</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    Editable
                                </span>
                            </label>
                            <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                value="{{ $report->tanggal_mulai }}"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">
                        </div>

                        <div class="space-y-1.5">
                            <label for="tanggal_estimasi"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Tanggal Estimasi</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    Editable
                                </span>
                            </label>
                            <input type="datetime-local" name="tanggal_estimasi" id="tanggal_estimasi"
                                value="{{ $report->tanggal_estimasi }}"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">
                        </div>

                        <div class="space-y-1.5">
                            <label for="tanggal_selesai"
                                class="flex items-center justify-between text-xs font-medium text-slate-600">
                                <span>Tanggal Selesai</span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="editMode ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500'">
                                    Editable
                                </span>
                            </label>
                            <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                value="{{ $report->tanggal_selesai }}"
                                class="block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2"
                                :class="editMode
                                    ?
                                    'bg-indigo-50/80 border-indigo-300 focus:ring-indigo-500 focus:border-indigo-500' :
                                    'bg-white border-slate-200 focus:ring-slate-200 focus:border-slate-300'"
                                x-bind:readonly="!editMode">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-600">
                            Lama Pengerjaan
                        </label>
                        <input type="text"
                            class="block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-800"
                            value="{{ $lamaPengerjaan }}" readonly>
                    </div>

                    {{-- Save changes --}}
                    @if ($canEdit)
                        <div class="flex justify-end pt-2" x-show="editMode" x-cloak>
                            <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                Save changes
                            </button>
                        </div>
                    @endif
                </form>

                <hr class="my-5 border-slate-200">

                {{-- Autographs --}}
                <div class="mt-2">
                    @include('partials.spk-autographs')
                </div>
            </div>
        </div>

        {{-- Uploaded files section --}}
        <div class="mt-6">
            @include('partials.uploaded-section', [
                'files' => $files,
                'showDeleteButton' => $report->pelapor === $authUser->name || $report->status_laporan === 2,
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('spkDetail', () => ({
                editMode: false,
                openUploadFiles: false,
                remarksExpanded: false,
                revisionRemarksExpanded: false,

                toggleEditMode() {
                    this.editMode = !this.editMode;

                    if (window.picInput) {
                        if (this.editMode) {
                            window.picInput.enable();
                        } else {
                            window.picInput.disable();
                        }
                    }
                },
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // TomSelect for PIC
            const picSelect = document.getElementById('pic');
            if (picSelect && window.TomSelect) {
                window.picInput = new TomSelect('#pic', {
                    plugins: ['dropdown_input'],
                    sortField: {
                        field: 'text',
                        direction: 'asc',
                    },
                    create: true,
                });

                const selectedPIC = @json(old('pic', $report->pic ?? ''));
                if (selectedPIC) {
                    window.picInput.addOption({
                        value: selectedPIC,
                        text: selectedPIC
                    });
                    window.picInput.setValue(selectedPIC);
                }

                // Start disabled (view mode)
                window.picInput.disable();

                @if ($report->to_department === 'MAINTENANCE MACHINE')
                    const mouldOptions = [{
                            value: 'Awaludin',
                            text: 'Awaludin'
                        },
                        {
                            value: 'Sokhib',
                            text: 'Sokhib'
                        },
                        {
                            value: 'Diki',
                            text: 'Diki'
                        },
                        {
                            value: 'Ashari',
                            text: 'Ashari'
                        },
                        {
                            value: 'Teguh',
                            text: 'Teguh'
                        },
                        {
                            value: 'Maulana',
                            text: 'Maulana'
                        },
                    ];

                    const machineOptions = [{
                            value: 'Dumro',
                            text: 'Dumro'
                        },
                        {
                            value: 'Waluyo',
                            text: 'Waluyo'
                        },
                        {
                            value: 'Achamd',
                            text: 'Achamd'
                        },
                        {
                            value: 'Junaidi',
                            text: 'Junaidi'
                        },
                        {
                            value: 'Seto',
                            text: 'Seto'
                        },
                        {
                            value: 'Andri',
                            text: 'Andri'
                        },
                    ];

                    function updatePICOptions(options) {
                        window.picInput.clear(true);
                        window.picInput.clearOptions();
                        options.forEach(option => window.picInput.addOption(option));
                        window.picInput.refreshOptions(false);
                    }

                    const radioMould = document.getElementById('inlineRadioMould');
                    const radioMachine = document.getElementById('inlineRadioMachine');

                    if (radioMould && radioMachine) {
                        radioMould.addEventListener('change', function() {
                            if (this.checked) updatePICOptions(mouldOptions);
                        });

                        radioMachine.addEventListener('change', function() {
                            if (this.checked) updatePICOptions(machineOptions);
                        });

                        const selectedFor = @json(old('for', $report->for ?? ''));
                        if (selectedFor === 'mould') {
                            radioMould.checked = true;
                            updatePICOptions(mouldOptions);
                        } else if (selectedFor === 'machine') {
                            radioMachine.checked = true;
                            updatePICOptions(machineOptions);
                        }
                    }
                @endif
            }
        });
    </script>
@endpush
