@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ agreed: false }">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1 text-xs text-slate-500">
                <li>
                    <a href="{{ route('formcuti') }}" class="hover:text-slate-700">Form Cuti</a>
                </li>
                <li class="text-slate-400">/</li>
                <li class="font-medium text-slate-700">
                    Create
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                    Create Form Cuti
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Ajukan cuti dengan mengisi data berikut secara lengkap dan benar.
                </p>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="POST"
                  action="{{ route('formcuti.insert') }}"
                  class="p-4 sm:p-6 space-y-5">
                @csrf

                {{-- Nama & No Karyawan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-medium text-slate-700">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', auth()->user()->name ?? '') }}"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_karyawan" class="block text-xs font-medium text-slate-700">
                            No. Karyawan <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            type="text"
                            id="no_karyawan"
                            name="no_karyawan"
                            value="{{ old('no_karyawan') }}"
                            required
                        >
                        @error('no_karyawan')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Jabatan & Departemen --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jabatan" class="block text-xs font-medium text-slate-700">
                            Jabatan
                        </label>
                        <input
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            type="text"
                            id="jabatan"
                            name="jabatan"
                            value="{{ old('jabatan') }}"
                        >
                        @error('jabatan')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-xs font-medium text-slate-700">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="department"
                            id="department"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                            <option value="" disabled {{ old('department') ? '' : 'selected' }}>
                                Select department..
                            </option>
                            @foreach ($deparments as $deparment)
                                <option value="{{ $deparment->name }}"
                                    {{ old('department', optional(auth()->user()->department)->name) == $deparment->name ? 'selected' : '' }}>
                                    {{ $deparment->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Jenis Cuti & Waktu Cuti --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_cuti" class="block text-xs font-medium text-slate-700">
                            Jenis Cuti <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            id="jenis_cuti"
                            name="jenis_cuti"
                            required
                        >
                            <option value="sakit"  {{ old('jenis_cuti') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin"   {{ old('jenis_cuti') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="tahun"  {{ old('jenis_cuti') == 'tahun' ? 'selected' : '' }}>Cuti Tahunan</option>
                        </select>
                        @error('jenis_cuti')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Waktu Cuti <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex gap-2">
                            <input
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                type="text"
                                id="waktu_cuti"
                                name="waktu_cuti"
                                value="{{ old('waktu_cuti') }}"
                                required
                            >
                            <select
                                class="w-28 rounded-lg border border-slate-300 bg-white px-2.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                id="satuan_waktu_cuti"
                                name="satuan_waktu_cuti"
                                required
                            >
                                <option value="jam"  {{ old('satuan_waktu_cuti') == 'jam' ? 'selected' : '' }}>Jam</option>
                                <option value="hari" {{ old('satuan_waktu_cuti') == 'hari' ? 'selected' : '' }}>Hari</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Contoh: 8 jam, 2 hari, dll.
                        </p>
                        @error('waktu_cuti')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pengganti & Keperluan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="pengganti" class="block text-xs font-medium text-slate-700">
                            Pengganti
                        </label>
                        <input
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            type="text"
                            id="pengganti"
                            name="pengganti"
                            value="{{ old('pengganti') }}"
                        >
                        @error('pengganti')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keperluan" class="block text-xs font-medium text-slate-700">
                            Keperluan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            id="keperluan"
                            name="keperluan"
                            rows="3"
                            required
                        >{{ old('keperluan') }}</textarea>
                        @error('keperluan')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tanggal-tanggal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label for="tanggal_masuk" class="block text-xs font-medium text-slate-700">
                                Tanggal Masuk
                            </label>
                            <input
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                type="date"
                                id="tanggal_masuk"
                                name="tanggal_masuk"
                                value="{{ old('tanggal_masuk') }}"
                            >
                        </div>

                        <div>
                            <label for="tanggal_permohonan" class="block text-xs font-medium text-slate-700">
                                Tanggal Permohonan <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                type="date"
                                id="tanggal_permohonan"
                                name="tanggal_permohonan"
                                value="{{ old('tanggal_permohonan') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label for="mulai_tanggal" class="block text-xs font-medium text-slate-700">
                                Mulai Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                type="date"
                                id="mulai_tanggal"
                                name="mulai_tanggal"
                                value="{{ old('mulai_tanggal') }}"
                                required
                            >
                        </div>

                        <div>
                            <label for="sampai_tanggal" class="block text-xs font-medium text-slate-700">
                                Sampai Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                type="date"
                                id="sampai_tanggal"
                                name="sampai_tanggal"
                                value="{{ old('sampai_tanggal') }}"
                                required
                            >
                        </div>
                    </div>
                </div>

                {{-- Konfirmasi --}}
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <label class="inline-flex items-start gap-2 text-xs text-slate-600 cursor-pointer">
                        <input
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            type="checkbox"
                            id="keterangan_user"
                            name="keterangan_user"
                            value="1"
                            required
                            x-model="agreed"
                        >
                        <span>
                            Saya yang membuat form cuti ini dengan sebenar-benarnya dan bertanggung jawab atas data yang saya isi.
                        </span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('formcuti') }}"
                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        :disabled="!agreed"
                        :class="agreed
                            ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer'
                            : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-xs font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
