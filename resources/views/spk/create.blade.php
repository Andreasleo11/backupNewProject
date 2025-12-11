@extends('new.layouts.app')

@section('content')
    @include('partials.alert-success-error')

    @php
        $authUser = auth()->user();
    @endphp

    <div
        class="max-w-5xl mx-auto px-4 py-6 lg:py-8 spk-create"
        x-data="spkCreate({
            countToday: {{ \App\Models\SuratPerintahKerja::whereDate('created_at', \Carbon\Carbon::today())->count() + 1 }}
        })"
        x-init="init()"
    >
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
                    Create
                </li>
            </ol>
        </nav>

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">
                    Create SPK
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Isi detail laporan secara lengkap untuk mempercepat proses tindak lanjut.
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                {{-- Reserved for future actions --}}
            </div>
        </div>

        <form
            action="{{ route('spk.input') }}"
            method="post"
            enctype="multipart/form-data"
            id="spkForm"
            class="space-y-5"
        >
            @csrf

            {{-- Header info card --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-800">
                    Informasi Laporan
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="no_dokumen" class="block text-xs font-medium text-slate-600">
                            No Dokumen
                        </label>
                        <input
                            type="text"
                            name="no_dokumen"
                            id="no_dokumen"
                            x-model="form.no_dokumen"
                            readonly
                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <p class="text-[11px] text-slate-400">
                            Nomor dokumen akan dibuat otomatis berdasarkan departemen tujuan & tanggal hari ini.
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="pelapor" class="block text-xs font-medium text-slate-600">
                            Pelapor
                        </label>
                        <input
                            type="text"
                            name="pelapor"
                            id="pelapor"
                            value="{{ $username }}"
                            readonly
                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>

                    <div class="space-y-1.5 md:col-span-2 md:max-w-xs">
                        <label for="tanggallapor" class="block text-xs font-medium text-slate-600">
                            Tanggal Lapor
                        </label>
                        <input
                            type="datetime-local"
                            name="tanggallapor"
                            id="tanggallapor"
                            x-model="form.tanggallapor"
                            readonly
                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                </div>
            </div>

            {{-- Details card --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6 space-y-5">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            Detail Permintaan
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Tentukan asal & tujuan departemen, tingkat urgensi, dan ringkasan masalah.
                        </p>
                    </div>
                </div>

                {{-- Departments --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="departmentDropdown" class="block text-xs font-medium text-slate-600">
                            From Department <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            name="from_department"
                            id="departmentDropdown"
                            required
                        >
                            <option value="" disabled>--Select from department--</option>
                            @foreach ($departments as $department)
                                @if ($department->id === $authUser->department->id)
                                    <option value="{{ $department->name }}" selected>
                                        {{ $department->name }}
                                    </option>
                                @elseif ($department->name === 'PERSONALIA' && auth()->user()->is_head === 1)
                                    {{-- Skip --}}
                                @else
                                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="toDepartmentDropdown" class="block text-xs font-medium text-slate-600">
                            To Department <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            name="to_department"
                            id="toDepartmentDropdown"
                            required
                            @change="handleToDepartmentChange($event)"
                        >
                            <option value="" selected disabled>Select to department..</option>
                            <option value="COMPUTER">COMPUTER</option>
                            <option value="MAINTENANCE">MAINTENANCE</option>
                            <option value="MAINTENANCE MACHINE">MAINTENANCE MACHINE</option>
                            <option value="PERSONALIA">PERSONALIA</option>
                        </select>
                    </div>
                </div>

                {{-- Requested by + urgent + type --}}
                <div class="grid gap-4 md:grid-cols-3 items-start">
                    <div class="space-y-1.5 md:col-span-1">
                        <label for="requested_by" class="block text-xs font-medium text-slate-600">
                            Requested By <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="requested_by"
                            id="requested_by"
                            placeholder="e.g. Raymond"
                            required
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>

                    {{-- Type (only for Maintenance / Maintenance Machine) --}}
                    <div
                        class="space-y-1.5 md:col-span-1"
                        x-show="showTypeFields"
                        x-cloak
                    >
                        <span class="block text-xs font-medium text-slate-600">
                            Type
                        </span>
                        <div class="flex flex-wrap gap-3 pt-1">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                <input
                                    class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    type="radio"
                                    name="type"
                                    id="inlineRadioMade"
                                    value="made"
                                >
                                <span>Made</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                <input
                                    class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    type="radio"
                                    name="type"
                                    id="inlineRadioRepair"
                                    value="repair"
                                >
                                <span>Repair</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                <input
                                    class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    type="radio"
                                    name="type"
                                    id="inlineRadioModify"
                                    value="modify"
                                >
                                <span>Modify</span>
                            </label>
                        </div>
                    </div>

                    {{-- Urgent --}}
                    <div class="space-y-1.5 md:col-span-1">
                        <span class="block text-xs font-medium text-slate-600">
                            Is Urgent? <span class="text-red-500">*</span>
                        </span>
                        <div class="flex items-center gap-4 pt-1">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                <input
                                    class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    type="radio"
                                    name="is_urgent"
                                    id="inlineRadioYes"
                                    value="yes"
                                >
                                <span>Yes</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                <input
                                    class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    type="radio"
                                    name="is_urgent"
                                    id="inlineRadioNo"
                                    value="no"
                                    checked
                                >
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Part / Machine info - only for Maintenance Machine --}}
                <div
                    class="grid gap-4 md:grid-cols-3 mt-2"
                    x-show="showPartFields"
                    x-cloak
                >
                    <div class="space-y-1.5">
                        <label for="part_no" class="block text-xs font-medium text-slate-600">
                            Part No <span class="font-normal text-slate-400">(Optional)</span>
                        </label>
                        <input
                            type="text"
                            name="part_no"
                            id="part_no"
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label for="part_name" class="block text-xs font-medium text-slate-600">
                            Part Name <span class="font-normal text-slate-400">(Optional)</span>
                        </label>
                        <input
                            type="text"
                            name="part_name"
                            id="part_name"
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label for="machine" class="block text-xs font-medium text-slate-600">
                            Machine <span class="font-normal text-slate-400">(Optional)</span>
                        </label>
                        <input
                            type="text"
                            name="machine"
                            id="machine"
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                </div>

                {{-- Title & description --}}
                <div class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <label for="judul_laporan" class="block text-xs font-medium text-slate-600">
                            Judul Laporan <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="judul_laporan"
                            id="judul_laporan"
                            required
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g. Layar monitor komputer (departemen) bermasalah"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label for="keterangan_laporan" class="block text-xs font-medium text-slate-600">
                            Keterangan Laporan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="keterangan_laporan"
                            id="keterangan_laporan"
                            rows="6"
                            required
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"
                            placeholder="e.g. layar hanya berkedip saja tidak mau menyala padahal sudah dicoba restart"
                        ></textarea>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="pt-2 space-y-2">
                    <label for="attachments" class="block text-xs font-medium text-slate-600">
                        Attachments
                    </label>

                    <div
                        class="border border-dashed border-slate-300 rounded-xl bg-slate-50/60 px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-slate-700">
                                Unggah foto/gambar pendukung
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Format gambar (JPG, PNG, dll). Anda dapat memilih beberapa file sekaligus.
                            </p>
                        </div>
                        <div>
                            <label
                                for="attachments"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 cursor-pointer"
                            >
                                Browse files
                            </label>
                            <input
                                type="file"
                                name="attachments[]"
                                id="attachments"
                                multiple
                                accept="image/*"
                                class="hidden"
                                x-ref="attachments"
                                @change="handleFiles($event)"
                            >
                        </div>
                    </div>

                    <div
                        id="attachment-previews"
                        class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-4"
                    >
                        <template x-for="(file, index) in files" :key="index">
                            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                                <div class="aspect-video bg-slate-100 overflow-hidden">
                                    <img
                                        :src="file.preview"
                                        alt="Attachment Preview"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="p-2 flex items-center justify-between gap-1">
                                    <p class="truncate text-[11px] text-slate-600" x-text="file.name"></p>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-[10px] font-medium text-rose-600 hover:bg-rose-100"
                                        @click="removeFile(index)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a
                    href="{{ route('spk.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                >
                    Submit
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('spkCreate', (config) => ({
                countToday: config.countToday || 1,
                form: {
                    no_dokumen: '',
                    tanggallapor: '',
                },
                showPartFields: false,
                showTypeFields: false,
                files: [],

                init() {
                    this.form.tanggallapor = this.currentDateTime();
                },

                currentDateTime() {
                    const d = new Date();
                    const pad = (n) => n.toString().padStart(2, '0');
                    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
                },

                handleToDepartmentChange(event) {
                    const toDepartment = event.target.value;
                    let toDeptCode = 'UNKNOWN';

                    if (toDepartment === 'COMPUTER') toDeptCode = 'CP';
                    else if (toDepartment === 'PERSONALIA') toDeptCode = 'HRD';
                    else if (toDepartment === 'MAINTENANCE') toDeptCode = 'MT';
                    else if (toDepartment === 'MAINTENANCE MACHINE') toDeptCode = 'MM';

                    const type = 'SPK';
                    const today = new Date();
                    const date = today.toISOString().slice(2, 10).replace(/-/g, '');
                    const lastNumber = String(this.countToday).padStart(3, '0');

                    this.form.no_dokumen = `${toDeptCode}/${type}/${date}/${lastNumber}`;

                    this.showPartFields = toDepartment === 'MAINTENANCE MACHINE';
                    this.showTypeFields = toDepartment === 'MAINTENANCE' || toDepartment === 'MAINTENANCE MACHINE';
                },

                handleFiles(event) {
                    const inputFiles = Array.from(event.target.files);
                    this.files = [];

                    inputFiles.forEach((file) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.files.push({
                                file,
                                name: file.name,
                                preview: e.target.result,
                            });
                            this.syncFileInput();
                        };
                        reader.readAsDataURL(file);
                    });

                    if (inputFiles.length === 0) {
                        this.syncFileInput();
                    }
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                    this.syncFileInput();
                },

                syncFileInput() {
                    const dt = new DataTransfer();
                    this.files.forEach((f) => dt.items.add(f.file));
                    if (this.$refs.attachments) {
                        this.$refs.attachments.files = dt.files;
                    }
                },
            }));
        });

        // TomSelect init (kept from your original logic)
        document.addEventListener('DOMContentLoaded', () => {
            if (window.TomSelect) {
                new TomSelect('#departmentDropdown', {
                    plugins: ['dropdown_input'],
                    sortField: {
                        field: 'text',
                        direction: 'asc',
                    },
                });
            }
        });
    </script>
@endpush
