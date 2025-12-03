@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50/60">
        <div class="w-full max-w-3xl px-4 py-8">
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h1 class="text-xl font-semibold text-slate-900">
                        PPS Wizard
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Production Planning Schedule (PPS) berdasarkan data delivery schedule dari SAP.
                    </p>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm leading-relaxed text-slate-700">
                        PPS Wizard digunakan untuk membentuk <span class="font-semibold">Production Planning Schedule</span>
                        yang ditarik berdasarkan data delivery schedule yang di-input pada sistem <span class="font-semibold">SAP</span>.
                    </p>

                    <p class="text-sm leading-relaxed text-slate-700">
                        Wizard ini akan membawa Anda melalui beberapa tahapan yang perlu diperhatikan
                        sampai proses pembentukan PPS selesai.
                    </p>

                    <ul class="mt-2 space-y-1 text-sm text-slate-600 list-disc list-inside">
                        <li>Meninjau delivery schedule dari SAP</li>
                        <li>Melakukan penyesuaian (adjustment) jika diperlukan</li>
                        <li>Generate PPS final untuk kebutuhan produksi</li>
                    </ul>

                    <p class="text-sm text-slate-600 mt-3">
                        Untuk memulai, silakan klik tombol <span class="font-semibold">Lanjut</span> di bawah ini.
                    </p>
                </div>

                <div class="px-6 pb-5 pt-2 flex justify-end border-t border-slate-100">
                    <a href="{{ route('menupps') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                              text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2
                              focus:ring-indigo-500 focus:ring-offset-1">
                        Lanjut
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="ml-2 h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
