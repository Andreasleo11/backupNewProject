@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl px-4 py-6 mx-auto space-y-6">
        {{-- Page header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-slate-900">
                Upload Daily Report
            </h1>
            <p class="text-sm text-slate-500">
                Unggah file laporan harian (Excel / CSV) untuk diproses dan ditinjau sebelum disimpan ke sistem.
            </p>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                x-data="{ open: true }"
                x-show="open"
                x-transition
                class="flex items-start justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                <div class="flex flex-col gap-1">
                    <span class="font-medium">Berhasil</span>
                    <p>{{ session('success') }}</p>
                </div>
                <button
                    type="button"
                    class="text-xs font-medium text-emerald-700 hover:text-emerald-900"
                    @click="open = false"
                >
                    Tutup
                </button>
            </div>
        @elseif (session('error'))
            <div
                x-data="{ open: true }"
                x-show="open"
                x-transition
                class="flex items-start justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
            >
                <div class="flex flex-col gap-1">
                    <span class="font-medium">Terjadi Kesalahan</span>
                    <p>{{ session('error') }}</p>
                </div>
                <button
                    type="button"
                    class="text-xs font-medium text-rose-700 hover:text-rose-900"
                    @click="open = false"
                >
                    Tutup
                </button>
            </div>
        @endif

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4 sm:p-6">
                <form action="{{ route('daily-report.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="report_file" class="block text-sm font-medium text-slate-700">
                            File Laporan
                        </label>

                        <input
                            type="file"
                            name="report_file"
                            id="report_file"
                            accept=".xlsx,.csv,.txt"
                            required
                            class="block w-full text-sm text-slate-900
                                   file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700
                                   hover:file:bg-indigo-100
                                   rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm
                                   focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60
                                   @error('report_file') border-rose-500 focus:border-rose-500 focus:ring-rose-500/60 @enderror"
                        >

                        @error('report_file')
                            <p class="mt-1 text-xs font-medium text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="text-xs text-slate-500">
                            Format yang didukung:
                            <span class="font-medium">.xlsx, .csv, .txt</span>.
                            Pastikan struktur kolom sudah sesuai template yang digunakan.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm
                                   hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 focus:ring-offset-slate-50
                                   disabled:cursor-not-allowed disabled:bg-indigo-300"
                        >
                            <span>Upload dan Preview</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
