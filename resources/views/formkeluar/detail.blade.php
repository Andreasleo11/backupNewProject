@extends('new.layouts.app')

@push('extraCss')
    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
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
                            <a href="{{ route('formkeluar') }}" class="hover:text-slate-700">Form Keluar</a>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li class="font-medium text-slate-700">
                            Detail
                        </li>
                    </ol>
                </nav>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                    Detail Form Keluar
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Doc No: <span class="font-medium text-slate-700">{{ $formkeluar->doc_num }}</span>
                </p>
            </div>
        </div>

        {{-- Signatures Section --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Dept Head --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                <h2 class="text-sm font-semibold text-slate-800 mb-2 text-center md:text-left">
                    Dept Head
                </h2>

                <div class="flex flex-col items-center">
                    <div class="autograph-box border border-slate-300 rounded-lg bg-slate-50" id="autographBox1"></div>
                    <div class="mt-2 text-xs text-slate-700" id="autographuser1"></div>

                    @if (Auth::check() &&
                            Auth::user()->department &&
                            Auth::user()->is_head == 1 &&
                            Auth::user()->department == $formkeluar->department)
                        <button id="btn1" type="button" onclick="addAutograph(1, {{ $formkeluar->id }})"
                            class="mt-3 inline-flex items-center rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                            Acc Dept Head
                        </button>
                    @endif
                </div>
            </div>

            {{-- Yang Bersangkutan --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                @php
                    $path2 = null;
                    if ($formkeluar->signature) {
                        $path = $formkeluar->signature->getSignatureImagePath();
                        $path2 = str_replace('public/', 'storage/', $path);
                    }
                @endphp

                <h2 class="text-sm font-semibold text-slate-800 mb-2 text-center md:text-left">
                    Yang Bersangkutan
                </h2>

                @if (!$formkeluar->hasBeenSigned())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4">
                        <p class="mb-2 text-xs text-slate-600 text-center">
                            Silakan tanda tangan di bawah ini untuk mengesahkan Form Keluar.
                        </p>
                        <form action="{{ $formkeluar->getSignatureRoute() }}" method="POST"
                            class="flex flex-col items-center">
                            @csrf
                            <div class="w-full max-w-xs">
                                <x-creagia-signature-pad />
                            </div>
                        </form>
                    </div>
                @else
                    <div class="flex flex-col items-center">
                        <div class="autograph-box border border-slate-300 rounded-lg bg-white flex items-center justify-center"
                            id="specialbox">
                            @if ($path2)
                                <img src="{{ asset($path2) }}" alt="Signature Image"
                                    class="max-h-24 max-w-full object-contain">
                            @endif
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-800">
                            {{ $formkeluar->name }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Main Detail Card --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4 sm:p-6">
                <div class="text-center mb-4">
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900 tracking-wide">
                        FORM KELUAR
                    </h2>
                    <div class="mt-2 text-xs sm:text-sm text-slate-600 space-y-0.5">
                        <p>
                            <span class="text-slate-500">Doc No:</span>
                            <span class="font-medium text-slate-800">{{ $formkeluar->doc_num }}</span>
                        </p>
                        <p>
                            <span class="text-slate-500">No Karyawan:</span>
                            <span class="font-medium text-slate-800">{{ $formkeluar->no_karyawan }}</span>
                        </p>
                        <p>
                            <span class="text-slate-500">Dibuat oleh:</span>
                            <span class="font-medium text-slate-800">{{ $formkeluar->name }}</span>
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
                                <th class="px-3 py-2 border border-slate-200 text-center">Pengganti</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Keperluan</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Tanggal Permohonan</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Waktu Keluar</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Jam Keluar</th>
                                <th class="px-3 py-2 border border-slate-200 text-center">Jam Kembali</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->name }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->jabatan }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->department }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->pengganti }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-left">
                                    {{ $formkeluar->keperluan }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->tanggal_permohonan }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->waktu_keluar }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->jam_keluar }}
                                </td>
                                <td class="px-3 py-2 border border-slate-200 text-center">
                                    {{ $formkeluar->jam_kembali }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script src="{{ asset('vendor/sign-pad/sign-pad.min.js') }}"></script>
@endsection
@push('scripts')
    <script>
        function addAutograph(section, formId) {
            const autographBox = document.getElementById('autographBox' + section);
            const username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            const imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

            fetch('/save-autosignature-path/' + formId + '/' + section, {
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
                autograph_1: '{{ $formkeluar->autograph_1 ?? null }}',
            };

            const autographNames = {
                autograph_name_1: '{{ $formkeluar->autograph_user_1 ?? null }}',
            };

            const i = 1;
            const autographBox = document.getElementById('autographBox' + i);
            const autographNameBox = document.getElementById('autographuser' + i);
            const btnId = document.getElementById('btn' + i);

            if (autographs['autograph_' + i]) {
                if (btnId) {
                    btnId.style.display = 'none';
                }

                const url = '/autographs/' + autographs['autograph_' + i];
                autographBox.style.backgroundImage = "url('" + url + "')";

                const autographName = autographNames['autograph_name_' + i];
                autographNameBox.textContent = autographName;
                autographNameBox.style.display = 'block';
            }
        }

        window.onload = function() {
            checkAutographStatus({{ $formkeluar->id }});
        };
    </script>
@endpush
