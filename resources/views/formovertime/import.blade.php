@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50/60 px-4">
        <div class="w-full max-w-xl">
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-md border border-slate-200 p-6 sm:p-7">
                {{-- Header --}}
                <div class="flex items-start gap-3 mb-5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 flex-shrink-0">
                        📥
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-slate-900">
                            Upload Excel Actual Overtime
                        </h2>
                        <p class="mt-1 text-xs sm:text-sm text-slate-500">
                            Pilih file Excel actual lembur sesuai template yang sudah ditentukan, lalu klik
                            <span class="font-medium text-slate-700">Upload</span>.
                        </p>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('actual.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="file" class="block text-xs font-medium text-slate-700 mb-1.5">
                            File Excel Actual Overtime <span class="text-rose-500">*</span>
                        </label>

                        {{-- Pretty file input --}}
                        <div
                            class="relative flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center hover:border-indigo-400 hover:bg-indigo-50/40 transition">
                            <input
                                id="file"
                                type="file"
                                name="file"
                                accept=".xlsx,.xls"
                                required
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            >

                            <div class="text-3xl mb-2">
                                📄
                            </div>
                            <p class="text-xs sm:text-sm font-medium text-slate-800">
                                Klik untuk memilih file Excel
                            </p>
                            <p class="mt-1 text-[11px] sm:text-xs text-slate-500">
                                Format yang didukung: <span class="font-mono text-slate-600">.xlsx</span>,
                                <span class="font-mono text-slate-600">.xls</span>
                            </p>
                        </div>
                    </div>

                    {{-- Tips / info kecil --}}
                    <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2.5">
                        <p class="text-[11px] sm:text-xs text-slate-500">
                            ✅ Pastikan kolom dan format data sesuai dengan template actual overtime yang digunakan sistem.<br>
                            ✅ Jika terjadi error, cek kembali header kolom, format tanggal, dan numeric jam lembur.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <a href="{{ url()->previous() }}"
                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-1.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
