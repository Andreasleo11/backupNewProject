@extends('new.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Trial Details</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Detail request trial untuk
                    <span class="font-medium text-slate-800">{{ $trial->customer }}</span>
                    — {{ $trial->part_name }} ({{ $trial->part_no }})
                </p>
            </div>
        </div>

        {{-- Tonage form khusus PI ketika tonage belum diisi --}}
        @if (Auth::user()->department->name === 'PI' && !$trial->tonage)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-800">
                            Tonage belum diisi
                        </p>
                        <p class="text-xs text-amber-700">
                            Silakan input tonage agar data trial lengkap.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('update.tonage', $trial->id) }}"
                        class="mt-3 flex items-end gap-2 sm:mt-0">
                        @csrf
                        <div>
                            <label for="tonage" class="block text-xs font-medium text-amber-900">
                                Tonage
                            </label>
                            <input type="text" id="tonage" name="tonage" required
                                class="mt-1 block w-40 rounded-md border border-amber-300 bg-white px-3 py-1.5 text-sm
                                          text-slate-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-amber-600 px-3 py-1.5 text-xs font-semibold
                                       text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2
                                       focus:ring-amber-500 focus:ring-offset-1">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Detail Trial (satu tabel untuk semua departemen) --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-200">
                <h2 class="text-sm font-semibold text-slate-800">Informasi Trial</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <th
                                class="w-52 bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Customer
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->customer }}
                            </td>
                        </tr>
                        <tr>
                            <th
                                class="w-52 bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Part Name
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->part_name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Part No
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->part_no }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Model
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->model }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Cavity
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->cavity }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Status Trial
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->status_trial }}
                            </td>
                        </tr>

                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Material
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->material }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Status Material
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->status_material }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Color
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->color }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Material Consump
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->material_consump }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Dimension Tooling
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->dimension_tooling }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Member Trial
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->member_trial }}
                            </td>
                        </tr>

                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Request Trial
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->request_trial }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Trial Date
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->trial_date }}
                            </td>
                        </tr>

                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Time Set Up Tooling
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->time_set_up_tooling }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Time Setting Tooling
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->time_setting_tooling }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Time Finish Inject
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->time_finish_inject }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Time Set Down Tooling
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->time_set_down_tooling }}
                            </td>
                        </tr>

                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Trial Cost
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->trial_cost }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Tonage
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->tonage ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Qty
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->qty }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-2.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                Adjuster
                            </th>
                            <td class="px-4 py-2.5 text-slate-800">
                                {{ $trial->adjuster }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Autograph / Approval Section --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="px-4 py-3 border-b border-slate-200">
                <h2 class="text-sm font-semibold text-slate-800">Approval Signatures</h2>
            </div>

            <div class="px-4 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- 1. Requested By PE --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Requested By PE
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PE')
                            <button type="button" onclick="addAutograph(1, {{ $trial->id }})" id="autographBtn1"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox1"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser1"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>

                    {{-- 2. Verify By PE --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Verify By PE
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PE')
                            <button type="button" onclick="addAutograph(2, {{ $trial->id }})" id="autographBtn2"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox2"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser2"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>

                    {{-- 3. Confirmed By PI 1 --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Confirmed By PI 1
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PI')
                            <button type="button" onclick="addAutograph(3, {{ $trial->id }})" id="autographBtn3"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox3"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser3"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>

                    {{-- 4. Confirmed By PI 2 --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Confirmed By PI 2
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PI')
                            <button type="button" onclick="addAutograph(4, {{ $trial->id }})" id="autographBtn4"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox4"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser4"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>

                    {{-- 5. Confirmed By PI 3 --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Confirmed By PI 3
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PI')
                            <button type="button" onclick="addAutograph(5, {{ $trial->id }})" id="autographBtn5"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox5"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser5"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>

                    {{-- 6. Approved By --}}
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">
                            Approved By
                        </h3>

                        @if (Auth::check() && Auth::user()->department->name == 'PI')
                            <button type="button" onclick="addAutograph(6, {{ $trial->id }})" id="autographBtn6"
                                class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold
                                           text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2
                                           focus:ring-slate-500 focus:ring-offset-1">
                                Add Signature
                            </button>
                        @endif

                        <div id="autographBox6"
                            class="mt-1 h-24 w-52 rounded-lg border border-slate-300 bg-slate-100 bg-center bg-contain bg-no-repeat">
                        </div>
                        <div id="autographuser6"
                            class="mt-1 min-h-[1.25rem] text-xs font-medium text-slate-700 text-center">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extraJs')
    <script>
        function addAutograph(section, trialId) {
            const autographBox = document.getElementById('autographBox' + section);
            const username = @json(Auth::check() ? Auth::user()->name : '');
            const imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');

            if (autographBox) {
                autographBox.style.backgroundImage = "url('" + imageUrl + "')";
            }

            fetch('/save-signature/' + trialId + '/' + section, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        imagePath: imageUrl
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function checkAutographStatus(trialId) {
            const autographs = {
                autograph_1: @json($trial->autograph_1),
                autograph_2: @json($trial->autograph_2),
                autograph_3: @json($trial->autograph_3),
                autograph_4: @json($trial->autograph_4),
                autograph_5: @json($trial->autograph_5),
                autograph_6: @json($trial->autograph_6),
            };

            const autographNames = {
                autograph_user_1: @json($trial->autograph_user_1),
                autograph_user_2: @json($trial->autograph_user_2),
                autograph_user_3: @json($trial->autograph_user_3),
                autograph_user_4: @json($trial->autograph_user_4),
                autograph_user_5: @json($trial->autograph_user_5),
                autograph_user_6: @json($trial->autograph_user_6),
            };

            for (let i = 1; i <= 6; i++) {
                const box = document.getElementById('autographBox' + i);
                const nameBox = document.getElementById('autographuser' + i);
                const btn = document.getElementById('autographBtn' + i);

                if (autographs['autograph_' + i]) {
                    if (box) {
                        const url = '/' + autographs['autograph_' + i];
                        box.style.backgroundImage = "url('" + url + "')";
                    }

                    if (nameBox) {
                        const autographName = autographNames['autograph_user_' + i];
                        if (autographName) {
                            nameBox.textContent = autographName;
                        }
                    }

                    if (btn) {
                        btn.classList.add('hidden');
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkAutographStatus({{ $trial->id }});
        });
    </script>
@endpush
