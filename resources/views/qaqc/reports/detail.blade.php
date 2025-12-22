@extends('new.layouts.app')

@push('head')
    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            border-radius: 0.5rem;
            border: 1px dashed #cbd5e1;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-color: #f8fafc;
            margin-inline: auto;
        }

        .autograph-name {
            font-size: .875rem;
            font-weight: 500;
            color: #334155;
        }

        .autograph-role {
            font-size: .875rem;
            font-weight: 600;
            color: #0f172a;
        }

        .table-vqc th,
        .table-vqc td {
            vertical-align: middle;
            font-size: .8rem;
        }

        .table-vqc thead th {
            text-align: center;
            white-space: nowrap;
        }

        .table-vqc tbody td {
            text-align: center;
        }

        @media (max-width: 992px) {
            .table-vqc {
                font-size: .75rem;
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    @php
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $currentUser = $user;

        // Cek apakah masih ada DO number yang kosong
        $isNull = $report->details->contains(function ($detail) {
            return is_null($detail->do_num);
        });
    @endphp

    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 py-4 space-y-4" x-data="vqcDetailPage()">

        {{-- HEADER: breadcrumb + title + actions --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav aria-label="breadcrumb" class="mb-1 text-xs text-slate-500">
                    <ol class="flex flex-wrap items-center gap-1">
                        <li>
                            <a href="{{ route('qaqc') }}" class="text-indigo-600 hover:underline">Home</a>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li>
                            <a href="{{ route('qaqc.report.index') }}" class="text-indigo-600 hover:underline">
                                Reports
                            </a>
                        </li>
                        <li class="text-slate-500">/ Detail</li>
                    </ol>
                </nav>

                <h1 class="text-lg font-semibold text-slate-900">
                    Verification Report Detail
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Dokumen:
                    <span class="font-semibold text-slate-800">
                        {{ $report->doc_num ?? '-' }}
                    </span>
                    —
                    Dibuat oleh:
                    <span class="font-semibold text-slate-800">
                        {{ $report->created_by ?? '-' }}
                    </span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Send mail --}}
                @if ($user->department->name === 'QC' && $user->specification->name === 'INSPECTOR')
                    @if ($report->has_been_emailed)
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-indigo-600 bg-white
                                       px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            @click="openSendMailConfirm = true">
                            <i class="bx bx-envelope text-sm"></i>
                            <span class="hidden sm:inline">Send mail</span>
                        </button>
                    @else
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-indigo-600 bg-white
                                       px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            @click="openSendMail = true">
                            <i class="bx bx-envelope text-sm"></i>
                            <span class="hidden sm:inline">Send mail</span>
                        </button>
                    @endif
                @endif

                {{-- Upload files --}}
                @if ($user->specification->name !== '-')
                    <button type="button"
                        class="inline-flex items-center gap-1 rounded-md border border-indigo-600 bg-white
                                   px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="openUploadFiles = true">
                        <i class="bx bx-upload text-sm"></i>
                        <span class="hidden sm:inline">Upload</span>
                    </button>
                @endif

                {{-- Adjust form --}}
                <a href="{{ route('adjust.index', ['reports' => $report]) }}"
                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white
                          px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50
                          focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1
                          {{ $isNull ? 'hidden' : '' }}">
                    <span>Adjust Form</span>
                </a>

                {{-- View Adjust form --}}
                @if ($adjustForm)
                    <form action="{{ route('adjustview') }}" method="get" class="{{ $isNull ? 'hidden' : '' }}">
                        <input type="hidden" name="report_id" value="{{ $report->id }}">
                        <button type="submit"
                            class="inline-flex items-center gap-1 rounded-md border border-emerald-600 bg-emerald-600
                                       px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            id="finishBtn">
                            <span>View Adjust Form</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ========= MODAL: SEND MAIL ========= --}}
        <div x-show="openSendMail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @keydown.escape.window="openSendMail = false">
            <div class="absolute inset-0" @click="openSendMail = false"></div>

            <div class="relative z-10 w-full max-w-3xl mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
                <form action="{{ route('qaqc.report.sendEmail', $report->id) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    {{-- Header --}}
                    <div class="flex items-start justify-between px-5 pt-5 pb-2">
                        <h5 class="text-sm font-semibold text-slate-900">
                            Send mail
                        </h5>
                        <button type="button"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                       hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                       focus:ring-indigo-500 focus:ring-offset-1"
                            @click="openSendMail = false">
                            <span class="sr-only">Close</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 pb-4 pt-1 space-y-4 text-sm text-slate-700">
                        {{-- From --}}
                        <div class="space-y-1">
                            <label for="fromInput" class="block text-xs font-medium text-slate-600">
                                From
                            </label>
                            <input id="fromInput" type="text"
                                class="block w-full rounded-md border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                          text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value="{{ Auth::user()->email }}" readonly disabled>
                        </div>

                        {{-- To --}}
                        <div class="space-y-1">
                            <label for="toInput" class="block text-xs font-medium text-slate-600">
                                To
                            </label>
                            <textarea name="to" id="toInput" rows="4"
                                class="semicolon-input block w-full rounded-md border-slate-300 px-3 py-2 text-xs
                                             text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                spellcheck="false">andriani@daijo.co.id; sriyati@daijo.co.id; anik@daijo.co.id; albert@daijo.co.id; nurul@daijo.co.id; riki@daijo.co.id; rony@daijo.co.id; sukiyono@daijo.co.id; budiman@daijo.co.id; heri@daijo.co.id; leny@daijo.co.id; popon@daijo.co.id; sukur@daijo.co.id; supri@daijo.co.id; wiji@daijo.co.id; agus_s@daijo.co.id; catur@daijo.co.id; yeyen@daijo.co.id; </textarea>
                            <p class="mt-1 text-[11px] text-slate-400">
                                Pisahkan email dengan titik koma <code>;</code>. Spasi akan ditambahkan otomatis.
                            </p>
                        </div>

                        {{-- CC --}}
                        <div class="space-y-1">
                            <label for="ccInput" class="block text-xs font-medium text-slate-600">
                                CC
                            </label>
                            <textarea name="cc" id="ccInput" rows="4"
                                class="semicolon-input block w-full rounded-md border-slate-300 px-3 py-2 text-xs
                                             text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                spellcheck="false">deni_qc@daijo.co.id; beata.qc@daijo.co.id; erizal@daijo.co.id; nurul_hidayati@daijo.co.id; herlina@daijo.co.id; srie@daijo.co.id; bayu@daijo.co.id; ekoqc@daijo.co.id; QA01_daijo@daijo.co.id; qa02_daijo@daijo.co.id; umi@daijo.co.id; yuli@daijo.co.id; emma@daijo.co.id; abdulrahim@daijo.co.id; raditya_qc@daijo.co.id; naya@daijo.co.id; adi@daijo.co.id; dian@daijo.co.id; dedi.agung@daijo.co.id; </textarea>
                        </div>

                        {{-- Subject --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-600">
                                Subject
                            </label>
                            <input type="text" name="subject" placeholder="Subject for the email"
                                class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm text-slate-700
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Body --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-600">
                                Body
                            </label>
                            <textarea name="body" rows="4" placeholder="Body for the email"
                                class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm text-slate-700
                                             shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        {{-- Attachments --}}
                        <div class="space-y-2">
                            <div class="text-xs font-medium text-slate-600">
                                Attachments
                            </div>

                            @php
                                $fileName = 'verification-report-' . $report->id . '.pdf';
                                $filePath = Storage::url('pdfs/' . $fileName);
                                $fileExists = file_exists(public_path('storage/pdfs/' . $fileName));
                            @endphp

                            @if ($fileExists)
                                <a href="{{ asset($filePath) }}" download="{{ $fileName }}" class="block">
                                    <div
                                        class="flex items-center justify-between rounded-lg border border-slate-200
                                                bg-slate-50 px-3 py-2 text-xs text-slate-600 hover:bg-slate-100">
                                        <span>{{ $fileName }}</span>
                                        <span class="text-[11px] text-slate-400">Click to download</span>
                                    </div>
                                </a>
                            @else
                                <div class="mt-1 text-xs font-semibold text-slate-500">
                                    You need to export the document first
                                </div>
                                <a href="{{ route('qaqc.report.savePdf', $report->id) }}"
                                    class="inline-flex items-center rounded-md border border-indigo-600 bg-white
                                          px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                    Export PDF
                                </a>
                            @endif

                            @foreach ($report->files as $file)
                                @php
                                    $filename = basename($file->name);
                                    $filepath = Storage::url('files/' . $filename);
                                @endphp
                                <a href="{{ $filepath }}" download="{{ $filename }}" class="block">
                                    <div
                                        class="flex items-center justify-between rounded-lg border border-slate-200
                                                bg-slate-50 px-3 py-2 text-xs text-slate-600 hover:bg-slate-100">
                                        <span>{{ $filename }}</span>
                                        <span class="text-[11px] text-slate-400">Click to download</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
                        <button type="button"
                            class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                       px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                       focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                            @click="openSendMail = false">
                            Close
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                       font-semibold text-white shadow-sm hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========= MODAL: SEND MAIL CONFIRMATION ========= --}}
        <div x-show="openSendMailConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @keydown.escape.window="openSendMailConfirm = false">
            <div class="absolute inset-0" @click="openSendMailConfirm = false"></div>

            <div class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
                <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
                    <h5 class="text-sm font-semibold text-slate-900">
                        Send Email Confirmation
                    </h5>
                    <button type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                   hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-1"
                        @click="openSendMailConfirm = false">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <div class="px-5 pt-1 pb-4 text-sm text-slate-600 space-y-2">
                    <p>You have already sent this report before.</p>
                    <p>Are you sure you want to send it again?</p>
                </div>

                <div class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                   px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                   focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                        @click="openSendMailConfirm = false">
                        Close
                    </button>

                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                   font-semibold text-white shadow-sm hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="openSendMailConfirm = false; openSendMail = true">
                        Confirm
                    </button>
                </div>
            </div>
        </div>

        {{-- ========= UPLOAD FILES MODAL (partial) ========= --}}
        {{-- Pastikan partial ini juga sudah kamu ubah ke Tailwind/Alpine sendiri --}}
        @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])

        {{-- ========= AUTOGRAPH / APPROVAL SECTION ========= --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="px-4 py-4 sm:px-6">
                <div class="grid gap-6 md:grid-cols-3 text-center">
                    {{-- Inspector --}}
                    <div>
                        <div class="autograph-role mb-1">QC Inspector</div>
                        <div class="autograph-box" id="autographBox1"></div>
                        <div class="mt-2 autograph-name" id="autographuser1"></div>
                        {{-- Tombol inspector di-disable di kode lama, biarkan tetap hidden --}}
                        {{-- ... --}}
                    </div>

                    {{-- Leader --}}
                    <div>
                        <div class="autograph-role mb-1">QC Leader</div>
                        <div class="autograph-box" id="autographBox2"></div>
                        <div class="mt-2 autograph-name" id="autographuser2"></div>

                        @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'LEADER')
                            <button id="btn2"
                                class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                           font-semibold text-white shadow-sm hover:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                onclick="addAutograph(2, {{ $report->id }})">
                                Acc QC Leader
                            </button>
                        @endif
                    </div>

                    {{-- Head --}}
                    <div>
                        <div class="autograph-role mb-1">QC/QA Head</div>
                        <div class="autograph-box" id="autographBox3"></div>
                        <div class="mt-2 autograph-name" id="autographuser3"></div>

                        @if (Auth::check() &&
                                ($currentUser->department->name == 'QC' || $currentUser->department->name == 'QA') &&
                                $currentUser->specification->name == 'HEAD' &&
                                ($report->autograph_1 || $report->autograph_2) != null)
                            <button id="btn3"
                                class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                           font-semibold text-white shadow-sm hover:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                onclick="addAutograph(3, {{ $report->id }}, {{ $user->id }})">
                                Acc QC/QA Head
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= MAIN REPORT CARD ========= --}}
        <section aria-label="table-report">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Header --}}
                <div class="px-4 pt-4 text-center sm:px-6">
                    <span class="block text-lg font-semibold text-slate-900">
                        Verification Reports
                    </span>

                    <div class="mt-1 mb-2">
                        <span class="block text-sm text-slate-800">
                            {{ $report->doc_num ?? '-' }}
                        </span>
                        <span class="text-xs text-slate-500">
                            Created by:
                            <span class="font-semibold text-slate-800">
                                {{ $report->created_by ?? '-' }}
                            </span>
                        </span>
                    </div>

                    @include('partials.vqc-status-badge')

                    <hr class="mt-3 mb-0 border-slate-200">
                </div>

                <div class="px-3 pb-4 pt-3 sm:px-5 sm:pb-6">
                    {{-- Header info --}}
                    <div class="overflow-x-auto mb-3">
                        <table class="min-w-full text-xs">
                            <tbody>
                                <tr>
                                    <th class="whitespace-nowrap pr-3 py-1 text-left font-semibold text-slate-700">
                                        Rec Date
                                    </th>
                                    <td class="py-1 text-slate-600">
                                        : @formatDate($report->rec_date)
                                    </td>
                                    <th class="whitespace-nowrap px-3 py-1 text-left font-semibold text-slate-700">
                                        Customer
                                    </th>
                                    <td class="py-1 text-slate-600">
                                        : {{ $report->customer }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap pr-3 py-1 text-left font-semibold text-slate-700">
                                        Verify Date
                                    </th>
                                    <td class="py-1 text-slate-600">
                                        : @formatDate($report->verify_date)
                                    </td>
                                    <th class="whitespace-nowrap px-3 py-1 text-left font-semibold text-slate-700">
                                        Invoice No
                                    </th>
                                    <td class="py-1 text-slate-600">
                                        : {{ $report->invoice_no }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Detail table --}}
                    <div class="overflow-x-auto mt-3">
                        <table class="min-w-full border border-slate-200 text-[11px] table-vqc">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">No</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Part Name</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Rec Qty</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Verify Qty</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Can Use</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Can't Use</th>
                                    <th colspan="3" class="border border-slate-200 px-2 py-2">Daijo Defect</th>
                                    <th colspan="3" class="border border-slate-200 px-2 py-2">Customer Defect</th>
                                    <th colspan="3" class="border border-slate-200 px-2 py-2">Supplier Defect</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Price / Qty</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">Total</th>
                                    <th rowspan="2" class="border border-slate-200 px-2 py-2">DO Number</th>
                                    @if ($report->is_approve !== 0)
                                        <th rowspan="2" class="border border-slate-200 px-2 py-2">Action</th>
                                    @endif
                                </tr>
                                <tr>
                                    <th class="border border-slate-200 px-2 py-1">Qty</th>
                                    <th class="border border-slate-200 px-2 py-1">Category</th>
                                    <th class="border border-slate-200 px-2 py-1">Remark</th>
                                    <th class="border border-slate-200 px-2 py-1">Qty</th>
                                    <th class="border border-slate-200 px-2 py-1">Category</th>
                                    <th class="border border-slate-200 px-2 py-1">Remark</th>
                                    <th class="border border-slate-200 px-2 py-1">Qty</th>
                                    <th class="border border-slate-200 px-2 py-1">Category</th>
                                    <th class="border border-slate-200 px-2 py-1">Remark</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($report->details as $detail)
                                    <tr>
                                        <td class="border border-slate-200 px-2 py-1">{{ $loop->iteration }}</td>
                                        <td class="border border-slate-200 px-2 py-1 text-left">
                                            {{ $detail->part_name }}
                                        </td>
                                        <td class="border border-slate-200 px-2 py-1">{{ $detail->rec_quantity }}</td>
                                        <td class="border border-slate-200 px-2 py-1">{{ $detail->verify_quantity }}</td>
                                        <td class="border border-slate-200 px-2 py-1">{{ $detail->can_use }}</td>
                                        <td class="border border-slate-200 px-2 py-1">{{ $detail->cant_use }}</td>

                                        {{-- Daijo defect --}}
                                        <td colspan="3" class="border border-slate-200 p-0">
                                            @foreach ($detail->defects as $defect)
                                                @if ($defect->is_daijo)
                                                    <table class="w-full text-[10px]">
                                                        <tbody>
                                                            <tr class="text-center">
                                                                <td class="py-1" style="width:33%;">
                                                                    {{ $defect->quantity }}
                                                                </td>
                                                                <td class="py-1" style="width:34%;">
                                                                    {{ $defect->quantity . ' : ' . ($defect->category?->name ?? '-') }}
                                                                </td>
                                                                <td class="py-1">
                                                                    {{ $defect->remarks }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                @endif
                                            @endforeach
                                        </td>

                                        {{-- Customer defect --}}
                                        <td colspan="3" class="border border-slate-200 p-0">
                                            @foreach ($detail->defects as $defect)
                                                @if ($defect->is_customer)
                                                    <table class="w-full text-[10px]">
                                                        <tbody>
                                                            <tr class="text-center">
                                                                <td class="py-1" style="width:33%;">
                                                                    {{ $defect->quantity }}
                                                                </td>
                                                                <td class="py-1" style="width:34%;">
                                                                    {{ $defect->quantity . ' : ' . ($defect->category?->name ?? '-') }}
                                                                </td>
                                                                <td class="py-1">
                                                                    {{ $defect->remarks }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                @endif
                                            @endforeach
                                        </td>

                                        {{-- Supplier defect --}}
                                        <td colspan="3" class="border border-slate-200 p-0">
                                            @foreach ($detail->defects as $defect)
                                                @if ($defect->is_supplier)
                                                    <table class="w-full text-[10px]">
                                                        <tbody>
                                                            <tr class="text-center">
                                                                <td class="py-1" style="width:33%;">
                                                                    {{ $defect->quantity }}
                                                                </td>
                                                                <td class="py-1" style="width:34%;">
                                                                    {{ $defect->quantity . ' : ' . ($defect->category?->name ?? '-') }}
                                                                </td>
                                                                <td class="py-1">
                                                                    {{ $defect->remarks }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                @endif
                                            @endforeach
                                        </td>

                                        <td class="border border-slate-200 px-2 py-1 text-right min-w-[110px]">
                                            @currency($detail->price)
                                        </td>
                                        <td class="border border-slate-200 px-2 py-1 text-right min-w-[110px]">
                                            @currency($detail->price * $detail->rec_quantity)
                                        </td>
                                        <td class="border border-slate-200 px-2 py-1">
                                            {{ $detail->do_num }}
                                        </td>

                                        @if ($report->is_approve !== 1)
                                            <td class="border border-slate-200 px-2 py-1">
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1
                                                               text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-700
                                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                                    @click="openEditDoId = {{ $detail->id }}">
                                                    Edit DO Number
                                                </button>
                                            </td>
                                        @else
                                            <td class="border border-slate-200 px-2 py-1 text-slate-400 text-[11px]">
                                                Approved
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18"
                                            class="border border-slate-200 px-3 py-3 text-center text-xs text-slate-500">
                                            No data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MODALS EDIT DO NUMBER (Alpine, satu state openEditDoId) --}}
                    @foreach ($report->details as $detail)
                        <div x-show="openEditDoId === {{ $detail->id }}" x-cloak
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                            @keydown.escape.window="openEditDoId = null">
                            <div class="absolute inset-0" @click="openEditDoId = null"></div>

                            <div
                                class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
                                <form method="POST" action="{{ route('update.do.number', $detail->id) }}">
                                    @csrf
                                    @method('PUT')

                                    {{-- Header --}}
                                    <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
                                        <h5 class="text-sm font-semibold text-slate-900">
                                            Edit DO Number for
                                            <span class="font-bold text-slate-900">
                                                {{ $detail->part_name }}
                                            </span>
                                        </h5>
                                        <button type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                                       hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                                       focus:ring-indigo-500 focus:ring-offset-1"
                                            @click="openEditDoId = null">
                                            <span class="sr-only">Close</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4"
                                                fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                    d="M6 6l12 12M18 6L6 18" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Body --}}
                                    <div class="px-5 pt-1 pb-4">
                                        <label for="do_num_{{ $detail->id }}"
                                            class="block text-xs font-medium text-slate-700">
                                            DO Number
                                        </label>
                                        <input type="text" id="do_num_{{ $detail->id }}" name="do_num"
                                            value="{{ old('do_num', $detail->do_num) }}" required
                                            class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm
                                                      text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('do_num')
                                            <p class="mt-1 text-[11px] text-rose-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    {{-- Footer --}}
                                    <div
                                        class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                                       px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                                       focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                                            @click="openEditDoId = null">
                                            Close
                                        </button>
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                                                       text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========= UPLOADED FILES SECTION ========= --}}
        <section aria-label="uploaded" class="mt-2">
            @include('partials.uploaded-section', [
                'showDeleteButton' => true,
                'files' => $report->files,
            ])
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function vqcDetailPage() {
            return {
                openSendMail: false,
                openSendMailConfirm: false,
                openUploadFiles: false,
                openEditDoId: null,
            };
        }

        // Semicolon helper untuk field To & CC
        document.addEventListener('DOMContentLoaded', function() {
            const inputFields = document.querySelectorAll('.semicolon-input');
            let backspaceCounts = {};
            let timeouts = {};

            inputFields.forEach((inputField, index) => {
                backspaceCounts[index] = 0;

                inputField.addEventListener('keydown', function(event) {
                    if (event.key === 'Backspace') {
                        backspaceCounts[index]++;
                        clearTimeout(timeouts[index]);
                        timeouts[index] = setTimeout(() => backspaceCounts[index] = 0, 300);
                    }
                });

                inputField.addEventListener('input', function(event) {
                    let inputValue = event.target.value;

                    // Tambah spasi setelah titik koma
                    if (inputValue.endsWith(';')) {
                        inputValue += ' ';
                    }
                    event.target.value = inputValue;

                    // If backspace dua kali setelah "; " => hapus entry terakhir
                    if (backspaceCounts[index] === 2 && inputValue.endsWith('; ')) {
                        const semicolonIndex = inputValue.lastIndexOf(';');
                        if (semicolonIndex !== -1) {
                            event.target.value = inputValue.slice(0, semicolonIndex);
                        }
                        backspaceCounts[index] = 0;
                    }
                });
            });
        });

        // Autograph
        function addAutograph(section, reportId) {
            const autographBox = document.getElementById('autographBox' + section);

            const username = @json(Auth::check() ? Auth::user()->name : '');
            const imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');

            if (autographBox) {
                autographBox.style.backgroundImage = "url('" + imageUrl + "')";
            }

            fetch('/save-image-path/' + reportId + '/' + section, {
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

            checkAutographStatus(reportId);
        }

        function checkAutographStatus(reportId) {
            const autographs = {
                autograph_1: @json($report->autograph_1 ?? null),
                autograph_2: @json($report->autograph_2 ?? null),
                autograph_3: @json($report->autograph_3 ?? null),
            };

            const autographNames = {
                autograph_name_1: @json($autographNames['autograph_name_1'] ?? null),
                autograph_name_2: @json($autographNames['autograph_name_2'] ?? null),
                autograph_name_3: @json($autographNames['autograph_name_3'] ?? null),
            };

            for (let i = 1; i <= 3; i++) {
                const autographBox = document.getElementById('autographBox' + i);
                const autographNameBox = document.getElementById('autographuser' + i);
                const btn = document.getElementById('btn' + i);

                if (autographs['autograph_' + i] && autographBox) {
                    if (btn) {
                        btn.classList.add('hidden');
                    }

                    const url = '/autographs/' + autographs['autograph_' + i];
                    autographBox.style.backgroundImage = "url('" + url + "')";

                    const autographName = autographNames['autograph_name_' + i];
                    if (autographName && autographNameBox) {
                        autographNameBox.textContent = autographName;
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkAutographStatus({{ $report->id }});
        });
    </script>
@endpush
