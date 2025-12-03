@extends('layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-8">
        <div class="max-w-3xl mx-auto px-4">
            {{-- Card utama --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div
                    class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            FINALLL for Second
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Proses PPS untuk Second Process sudah selesai. Klik tombol di bawah untuk kembali ke menu PPS.
                        </p>
                    </div>

                    <a href="{{ route('indexpps') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2
                              text-sm font-semibold text-white shadow-sm hover:bg-emerald-700
                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                        Finish
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
