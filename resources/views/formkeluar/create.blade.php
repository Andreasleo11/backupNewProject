@extends('new.layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ agreed: false }">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1 text-xs text-slate-500">
                <li>
                    <a href="{{ route('formkeluar') }}" class="hover:text-slate-700">Form Keluar</a>
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
                    Create Form Keluar
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Ajukan izin keluar dengan mengisi data berikut secara lengkap dan benar.
                </p>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="POST"
                  action="{{ route('formkeluar.insert') }}"
                  class="p-4 sm:p-6 space-y-5">
                @csrf

                {{-- Name & No Karyawan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            value="{{ old('name', auth()->user()->name ?? '') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
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
                            type="text"
                            id="no_karyawan"
                            name="no_karyawan"
                            required
                            value="{{ old('no_karyawan') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
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
                            type="text"
                            id="jabatan"
                            name="jabatan"
                            value="{{ old('jabatan') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                        @error('jabatan')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-xs font-medium text-slate-700">
                            Departemen <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="department"
                            name="department"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                            <option value="" disabled {{ old('department') ? '' : 'selected' }}>
                                Pilih departemen..
                            </option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->name }}"
                                    {{ old('department', optional(auth()->user()->department)->name) == $department->name ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Waktu Keluar & Pengganti --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Waktu Keluar <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex gap-2">
                            <input
                                type="text"
                                id="waktu_keluar"
                                name="waktu_keluar"
                                required
                                value="{{ old('waktu_keluar') }}"
                                placeholder="Contoh: 2"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                            <select
                                id="satuan_waktu_keluar"
                                name="satuan_waktu_keluar"
                                required
                                class="w-28 rounded-lg border border-slate-300 bg-white px-2.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                                <option value="jam" {{ old('satuan_waktu_keluar') == 'jam' ? 'selected' : '' }}>Jam</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Masukkan lama izin keluar, misalnya <span class="font-medium">2 jam</span>.
                        </p>
                    </div>

                    <div>
                        <label for="pengganti" class="block text-xs font-medium text-slate-700">
                            Pengganti
                        </label>
                        <input
                            type="text"
                            id="pengganti"
                            name="pengganti"
                            value="{{ old('pengganti') }}"
                            placeholder="Nama rekan yang menggantikan tugas"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                        @error('pengganti')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Alasan & Keperluan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="alasan_izin_keluar" class="block text-xs font-medium text-slate-700">
                            Alasan Izin
                        </label>
                        <textarea
                            id="alasan_izin_keluar"
                            name="alasan_izin_keluar"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            placeholder="Contoh: Mengurus administrasi, kontrol ke dokter, dll.">{{ old('alasan_izin_keluar') }}</textarea>
                        @error('alasan_izin_keluar')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keperluan" class="block text-xs font-medium text-slate-700">
                            Keperluan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="keperluan"
                            name="keperluan"
                            required
                            rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            placeholder="Jelaskan keperluan izin keluar dengan singkat dan jelas.">{{ old('keperluan') }}</textarea>
                        @error('keperluan')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tanggal & Jam --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label for="tanggal_masuk" class="block text-xs font-medium text-slate-700">
                                Tanggal Masuk
                            </label>
                            <input
                                type="date"
                                id="tanggal_masuk"
                                name="tanggal_masuk"
                                value="{{ old('tanggal_masuk') }}"
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                        </div>

                        <div>
                            <label for="tanggal_permohonan" class="block text-xs font-medium text-slate-700">
                                Tanggal Permohonan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="tanggal_permohonan"
                                name="tanggal_permohonan"
                                value="{{ old('tanggal_permohonan') }}"
                                required
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label for="jam_keluar" class="block text-xs font-medium text-slate-700">
                                Jam Keluar <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="time"
                                id="jam_keluar"
                                name="jam_keluar"
                                value="{{ old('jam_keluar') }}"
                                required
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                        </div>

                        <div>
                            <label for="jam_kembali" class="block text-xs font-medium text-slate-700">
                                Jam Kembali <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="time"
                                id="jam_kembali"
                                name="jam_kembali"
                                value="{{ old('jam_kembali') }}"
                                required
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                            >
                        </div>
                    </div>
                </div>

                {{-- Confirmation --}}
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <label class="inline-flex items-start gap-2 text-xs text-slate-600 cursor-pointer">
                        <input
                            type="checkbox"
                            id="keterangan_user"
                            name="keterangan_user"
                            value="1"
                            required
                            x-model="agreed"
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>
                            Saya yang membuat form keluar ini dengan sebenar-benarnya dan bertanggung jawab atas data
                            yang saya isi.
                        </span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('formkeluar') }}"
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
