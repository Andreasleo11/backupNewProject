@extends('new.layouts.app')

@push('head')
    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb + Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <nav class="mb-1" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1 text-xs text-slate-500">
                        <li>
                            <a href="{{ route('formcuti') }}" class="hover:text-slate-700">Form Cuti</a>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li class="font-medium text-slate-700">
                            Detail
                        </li>
                    </ol>
                </nav>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                    Detail Form Cuti
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Doc No: <span class="font-medium text-slate-700">{{ $formcuti->doc_num }}</span>
                </p>
            </div>
        </div>

        {{-- Signature --}}
        <section class="mb-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                <h2 class="text-sm font-semibold text-slate-800 mb-2 text-center sm:text-left">
                    Dept Head
                </h2>

                <div class="flex flex-col items-center sm:flex-row sm:items-center sm:gap-6">
                    <div class="autograph-box rounded-lg bg-slate-50" id="autographBox1"></div>
                    <div class="mt-2 sm:mt-0 text-xs text-slate-700" id="autographuser1"></div>
                </div>

                @if (
                    Auth::check() &&
                    Auth::user()->department &&
                    Auth::user()->is_head == 1 &&
                    Auth::user()->department == $formcuti->department)
                    <div class="mt-4">
                        <button
                            id="btn1"
                            type="button"
                            onclick="addAutograph(1, {{ $formcuti->id }})"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        >
                            Acc Dept Head
                        </button>
                    </div>
                @endif
            </div>
        </section>

        {{-- Detail Card --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4 sm:p-6">
                <div class="text-center mb-4">
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900 tracking-wide">
                        FORM CUTI
                    </h2>
                    <div class="mt-2 text-xs sm:text-sm text-slate-600 space-y-0.5">
                        <p>
                            <span class="text-slate-500">No Karyawan:</span>
                            <span class="font-medium text-slate-800">{{ $formcuti->no_karyawan }}</span>
                        </p>
                        <p>
                            <span class="text-slate-500">Dibuat oleh:</span>
                            <span class="font-medium text-slate-800">{{ $formcuti->name }}</span>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full table-auto border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2 border border-slate-200 text-center">Name</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Jabatan</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Departemen</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Jenis Cuti</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Pengganti</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Tanggal Masuk</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Tanggal Permohonan</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Mulai</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Selesai</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Waktu Cuti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->name }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->jabatan }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->department }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ ucfirst($formcuti->jenis_cuti) }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->pengganti }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->tanggal_masuk }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->tanggal_permohonan }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->mulai_tanggal }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->sampai_tanggal }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formcuti->waktu_cuti }} {{ $formcuti->satuan_waktu_cuti ?? '' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function addAutograph(section, formId) {
            const autographBox = document.getElementById('autographBox' + section);

            const username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            const imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

            fetch('/save-aurographed-path/' + formId + '/' + section, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        imagePath: imageUrl,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            checkAutographStatus(formId);
        }

        function checkAutographStatus(formId) {
            const autographs = {
                autograph_1: '{{ $formcuti->autograph_1 ?? null }}',
            };

            const autographNames = {
                autograph_name_1: '{{ $formcuti->autograph_user_1 ?? null }}',
            };

            const i = 1;
            const autographBox = document.getElementById('autographBox' + i);
            const autographNameBox = document.getElementById('autographuser' + i);
            const btnId = document.getElementById('btn' + i);

            if (autographs['autograph_' + i]) {
                if (btnId) {
                    btnId.style.display = 'none';
                }

                const url = '/' + autographs['autograph_' + i];
                autographBox.style.backgroundImage = "url('" + url + "')";

                const autographName = autographNames['autograph_name_' + i];
                autographNameBox.textContent = autographName;
                autographNameBox.style.display = 'block';
            }
        }

        window.onload = function() {
            checkAutographStatus({{ $formcuti->id }});
        };
    </script>
@endpush
