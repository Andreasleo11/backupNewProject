@extends('new.layouts.app')

@section('title', 'Evaluasi Karyawan — ' . \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'))

@section('content')

<div class="mx-auto max-w-7xl px-3 py-6 sm:px-4 lg:px-0 space-y-6">
    {{-- ═══════════════════════════════════════════════════════════════
         HEADER — Period Selector & Title (Glass Card)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="glass-card overflow-hidden pt-8 pb-6 px-6 relative">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/5 to-purple-600/5 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            
            {{-- Title & Icon --}}
            <div class="flex items-center gap-4 border-l-4 border-indigo-600 pl-4">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i class='bx bx-user-check text-2xl'></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800">
                        Employee Evaluations
                    </h1>
                    <p class="text-sm font-medium text-slate-500 mt-0.5" id="period-label">
                        Penilaian periode: <strong>{{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</strong>
                    </p>
                </div>
            </div>

            {{-- Period navigation (Sleek Inline Controls) --}}
            <div class="relative z-10 flex flex-wrap items-center gap-2 bg-white/60 backdrop-blur-md p-1.5 rounded-2xl border border-slate-200/60 shadow-sm">
                
                {{-- Month selector --}}
                <div class="relative">
                    <i class="bx bx-calendar absolute left-3 top-1/2 -translate-y-1/2 text-indigo-400"></i>
                    <select id="period-month" class="form-select form-select-sm border-0 bg-transparent py-1.5 pl-8 pr-6 text-sm font-semibold text-slate-700 focus:ring-0 cursor-pointer w-auto shadow-none">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate($year, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="h-4 w-px bg-slate-200"></div>

                {{-- Year selector --}}
                <div class="relative">
                    <select id="period-year" class="form-select form-select-sm border-0 bg-transparent py-1.5 pl-3 pr-6 text-sm font-semibold text-slate-700 focus:ring-0 cursor-pointer w-auto shadow-none">
                        @for ($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <button id="apply-period" class="ml-1 inline-flex items-center justify-center h-8 w-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors shadow-sm">
                    <i class="bx bx-search-alt text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STATUS SUMMARY CHIPS (Metrics Dashboard)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4" id="status-summary-row">
        @php
            $chips = [
                'pending'       => ['label' => 'Belum Dinilai',   'color' => 'slate',   'icon' => 'bx-time-five'],
                'graded'        => ['label' => 'Sudah Dinilai',   'color' => 'amber',   'icon' => 'bx-check-circle'],
                'dept_approved' => ['label' => 'Approved Kepala', 'color' => 'sky',     'icon' => 'bx-user-check'],
                'fully_approved'=> ['label' => 'Final Approved',  'color' => 'emerald', 'icon' => 'bx-check-double'],
                'rejected'      => ['label' => 'Ditolak',         'color' => 'rose',    'icon' => 'bx-x-circle'],
                'total'         => ['label' => 'Total Pegawai',   'color' => 'indigo',  'icon' => 'bx-group'],
            ];
        @endphp

        @foreach ($chips as $key => $chip)
        <div class="glass-card bg-white/80 p-4 border border-{{ $chip['color'] }}-100 group hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ $chip['label'] }}</p>
                    <h3 class="text-2xl font-black text-{{ $chip['color'] }}-600 tracking-tight" data-chip="{{ $key }}">
                        {{ $summary[$key] ?? 0 }}
                    </h3>
                </div>
                <div class="h-10 w-10 rounded-xl bg-{{ $chip['color'] }}-50 flex items-center justify-center text-{{ $chip['color'] }}-500 transition-colors group-hover:bg-{{ $chip['color'] }}-100">
                    <i class='bx {{ $chip["icon"] }} text-xl'></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         TABS & ACTION BAR
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="glass-card overflow-hidden shadow-sm border border-slate-200/60 relative mb-6">
        <div class="absolute inset-0 bg-gradient-to-b from-white to-slate-50/30 -z-10"></div>
        
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Tabs --}}
            <ul class="nav nav-tabs flex items-center gap-2 p-1 bg-slate-100/80 rounded-xl" id="evalTabs" role="tablist" style="border-bottom: none;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300" 
                            id="tab-regular" data-bs-toggle="tab" data-bs-target="#pane-regular" role="tab" data-type="regular"
                            style="border:none;">
                        Regular
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition-all duration-300" 
                            id="tab-yayasan" data-bs-toggle="tab" data-bs-target="#pane-yayasan" role="tab" data-type="yayasan"
                            style="border:none;">
                        Yayasan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition-all duration-300" 
                            id="tab-magang" data-bs-toggle="tab" data-bs-target="#pane-magang" role="tab" data-type="magang"
                            style="border:none;">
                        Magang
                    </button>
                </li>
            </ul>

            {{-- Action Bar --}}
            <div class="flex flex-wrap items-center gap-2" id="batch-action-bar">
                
                {{-- Dept Head: Approve All Graded --}}
                <form method="POST" action="{{ route('evaluation.approve-dept') }}" class="m-0" id="approve-dept-form">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <input type="hidden" name="type"  id="approve-dept-type" value="">
                    
                    {{-- This button would ideally be hidden via policy/role if not dept head --}}
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition-all hover:-translate-y-0.5 group">
                        <i class="bx bx-check-double text-lg group-hover:scale-110 transition-transform"></i>
                        Approve Semua
                    </button>
                </form>

                {{-- HRD: Final Approve --}}
                <form method="POST" action="{{ route('evaluation.approve-hrd') }}" class="m-0" id="approve-hrd-form">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <input type="hidden" name="type"  id="approve-hrd-type" value="">
                    
                    {{-- This button would ideally be hidden via policy/role if not HRD --}}
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all hover:-translate-y-0.5 group">
                        <i class="bx bx-check-shield text-lg group-hover:scale-110 transition-transform"></i>
                        Final Approve
                    </button>
                </form>

                <div class="h-6 w-px bg-slate-200 mx-1"></div>

                {{-- Focus Mode Toggle Button --}}
                <button type="button" onclick="
                    const type = document.querySelector('.nav-tabs .nav-link.active').dataset.type;
                    window.dispatchEvent(new CustomEvent('open-focus-mode', { 
                        detail: { type: type, month: {{ $month }}, year: {{ $year }} }
                    }));
                " class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_4px_12px_rgba(79,70,229,0.3)] border border-transparent transition-all hover:bg-indigo-700 hover:shadow-[0_6px_16px_rgba(79,70,229,0.4)] hover:-translate-y-0.5 group" >
                    <i class="bx bx-scan text-lg group-hover:scale-110 transition-transform"></i>
                    <span>Mode Fokus</span>
                </button>

                {{-- Advanced Toggle Button --}}
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-advanced-sidebar'))" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm border border-slate-200 transition-all hover:bg-slate-200 hover:-translate-y-0.5" >
                    <i class="bx bx-slider-alt text-lg text-slate-500"></i>
                    <span>Tingkat Lanjut</span>
                </button>

                {{-- Export — only when fully approved --}}
                @if($canExport)
                <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm border border-slate-200 transition-all hover:bg-slate-50 hover:-translate-y-0.5" id="export-btn" onclick="
                    const type = document.querySelector('.nav-tabs .nav-link.active').dataset.type;
                    this.href = '{{ route('evaluation.export') }}?month={{ $month }}&year={{ $year }}&type=' + type;
                ">
                    <i class="bx bx-export text-lg text-emerald-600"></i>
                    <span>Export Excel</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Tab panes --}}
        <div class="tab-content p-4" id="evalTabContent">

            {{-- Regular --}}
            <div class="tab-pane fade show active" id="pane-regular" role="tabpanel">
                <div class="premium-datatable-wrapper overflow-x-auto custom-scrollbar pb-4 block w-full">
                    <table id="evaluation-regular-table" class="table table-hover table-striped w-full nowrap"></table>
                </div>
            </div>

            {{-- Yayasan --}}
            <div class="tab-pane fade" id="pane-yayasan" role="tabpanel">
                <div class="premium-datatable-wrapper overflow-x-auto custom-scrollbar pb-4 block w-full">
                    <table id="evaluation-yayasan-table" class="table table-hover table-striped w-full nowrap"></table>
                </div>
            </div>

            {{-- Magang --}}
            <div class="tab-pane fade" id="pane-magang" role="tabpanel">
                <div class="premium-datatable-wrapper overflow-x-auto custom-scrollbar pb-4 block w-full">
                    <table id="evaluation-magang-table" class="table table-hover table-striped w-full nowrap"></table>
                </div>
            </div>
        </div>
    </div>

{{-- Grade / Edit Modal --}}
@push('modals')
    @include('partials.edit-discipline-modal')

    {{-- ═══════════════════════════════════════════════════════════════
        ADVANCED SIDEBAR (AlpineJS + Tailwind)
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-data="{ advancedOpen: false }" 
         @open-advanced-sidebar.window="advancedOpen = true"
         x-init="$watch('advancedOpen', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
         })"
         x-show="advancedOpen" 
         style="display: none;"
         class="relative z-[1050]" 
         aria-labelledby="advancedFeaturesLabel" 
         role="dialog" 
         aria-modal="true"
         x-cloak>
         
        {{-- Background Overlay --}}
        <div x-show="advancedOpen" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        {{-- Slide-over Container --}}
        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="advancedOpen" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-auto custom-scrollbar bg-white shadow-xl shadow-slate-900/20 border-l border-slate-200" @click.outside="advancedOpen = false">
                            
                            {{-- Header --}}
                            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                        <i class="bx bx-slider-alt text-2xl"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <h2 class="text-lg font-bold text-slate-900" id="advancedFeaturesLabel">Tingkat Lanjut</h2>
                                        <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-0.5">Filter & Export</p>
                                    </div>
                                </div>
                                <div class="ml-3 flex h-7 items-center">
                                    <button type="button" @click="advancedOpen = false" class="relative rounded-lg bg-white p-2 text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm border border-slate-200 transition-colors">
                                        <span class="absolute -inset-2.5"></span>
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Content --}}
                            <div class="relative flex-1 px-6 py-6 space-y-8 bg-white">
                
                {{-- Feature 1: Cetak Format (Legacy Restored) --}}
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 shadow-sm">
                                        <h6 class="text-[13px] font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                                            <div class="p-1.5 bg-indigo-100 text-indigo-600 rounded-lg">
                                                <i class="bx bx-printer"></i>
                                            </div>
                                            Cetak Format Penilaian
                                        </h6>
                                        <div class="space-y-2.5">
                                            <a href="{{ route('format.evaluation.year.allin') }}" class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transition-all text-sm text-slate-700 font-semibold group flex-shrink-0">
                                                <span class="truncate pr-4">Format Regular (All In)</span>
                                                <i class="bx bx-chevron-right text-lg text-slate-400 group-hover:text-indigo-600 group-hover:-translate-x-1 transition-all"></i>
                                            </a>
                                            <a href="{{ route('format.evaluation.year.yayasan') }}" class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transition-all text-sm text-slate-700 font-semibold group flex-shrink-0">
                                                <span class="truncate pr-4">Format Yayasan</span>
                                                <i class="bx bx-chevron-right text-lg text-slate-400 group-hover:text-indigo-600 group-hover:-translate-x-1 transition-all"></i>
                                            </a>
                                            <a href="{{ route('format.evaluation.year.magang') }}" class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transition-all text-sm text-slate-700 font-semibold group flex-shrink-0">
                                                <span class="truncate pr-4">Format Magang</span>
                                                <i class="bx bx-chevron-right text-lg text-slate-400 group-hover:text-indigo-600 group-hover:-translate-x-1 transition-all"></i>
                                            </a>
                                            <a href="{{ route('format.evaluation.year.allinperpanjangan') }}" class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transition-all text-sm text-slate-700 font-semibold group flex-shrink-0">
                                                <span class="truncate pr-4">Format Perpanjangan Kontrak</span>
                                                <i class="bx bx-chevron-right text-lg text-slate-400 group-hover:text-indigo-600 group-hover:-translate-x-1 transition-all"></i>
                                            </a>
                                        </div>
                                    </div>
                        
                                    {{-- Feature: Export Yayasan Data --}}
                                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                                        <div class="absolute -right-4 -top-4 text-indigo-100/50 transform rotate-12 pointer-events-none">
                                            <i class="bx bx-buildings text-9xl"></i>
                                        </div>
                                        <h6 class="relative text-[13px] font-extrabold text-indigo-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                            <div class="p-1.5 bg-indigo-600 text-white rounded-lg shadow-sm font-black">
                                                <i class="bx bxs-file-export"></i>
                                            </div>
                                            Export Yayasan
                                        </h6>
                                        <div class="relative space-y-2.5">
                                            <a href="{{ route('exportyayasan.dateinput') }}" class="flex items-center justify-between px-4 py-3 rounded-xl border border-indigo-200 bg-white shadow-sm hover:border-indigo-500 hover:ring-2 hover:ring-indigo-500/20 hover:shadow-md transition-all text-sm text-slate-800 font-bold group">
                                                <span>Export Data ke JPayroll</span>
                                                <i class="bx bx-right-arrow-alt text-xl text-indigo-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all"></i>
                                            </a>
                                        </div>
                                    </div>
                        
                                    {{-- Feature 2: Analytics (Placeholder) --}}
                                    <div>
                                        <h6 class="text-[13px] font-extrabold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                                            <i class="bx bx-bar-chart-alt-2 text-indigo-500 text-lg"></i> Distribusi Nilai
                                        </h6>
                                        <div class="px-5 py-6 rounded-2xl border border-slate-200/60 bg-slate-50 shadow-sm text-sm text-slate-500 text-center flex flex-col items-center gap-3">
                                            <div class="h-12 w-12 rounded-full bg-slate-200/50 flex items-center justify-center">
                                                <i class="bx bx-pie-chart-alt text-2xl text-slate-400"></i>
                                            </div>
                                            <span class="font-medium">Grafik performa dan distribusi departemen masih dalam tahap pengembangan.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Full-Screen Focus Mode Component --}}
    @livewire('evaluation.focus-mode')

@endpush

@endsection

@push('scripts')
<script type="module">
(function () {
    'use strict';

    const currentMonth = {{ $month }};
    const currentYear  = {{ $year }};

    // ── DataTable column definitions (injected from controller via JSON) ──────
    const columnsRegular  = @json(\App\DataTables\DisciplineDataTable::columnsForJs('regular'));
    const columnsYayasan  = @json(\App\DataTables\DisciplineDataTable::columnsForJs('yayasan'));
    const columnsMagang   = @json(\App\DataTables\DisciplineDataTable::columnsForJs('magang'));

    // ── Build each DataTable ──────────────────────────────────────────────────
    function makeTable(tableId, ajaxRoute, columns) {
        return $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url:  ajaxRoute,
                data: function(d) {
                    d.month = currentMonth;
                    d.year  = currentYear;
                }
            },
            columns: columns,
            order: [[1, 'asc']],
            responsive: true,
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
        });
    }

    const dtRegular = makeTable(
        'evaluation-regular-table',
        '{{ route("evaluation.data.regular") }}',
        columnsRegular
    );

    // Yayasan and Magang tables are initialised lazily (on tab show)
    let dtYayasan = null;
    let dtMagang  = null;

    document.getElementById('tab-yayasan')?.addEventListener('shown.bs.tab', function () {
        if (!dtYayasan) {
            dtYayasan = makeTable(
                'evaluation-yayasan-table',
                '{{ route("evaluation.data.yayasan") }}',
                columnsYayasan
            );
        }
    });

    document.getElementById('tab-magang')?.addEventListener('shown.bs.tab', function () {
        if (!dtMagang) {
            dtMagang = makeTable(
                'evaluation-magang-table',
                '{{ route("evaluation.data.magang") }}',
                columnsMagang
            );
        }
    });

    // ── Period selector ───────────────────────────────────────────────────────
    document.getElementById('apply-period')?.addEventListener('click', function () {
        const month = document.getElementById('period-month').value;
        const year  = document.getElementById('period-year').value;
        window.location.href = '/evaluation/' + month + '/' + year;
    });

    // ── Batch Action Form Interceptors (SweetAlert2) ──────────
    ['approve-dept-form', 'approve-hrd-form'].forEach(id => {
        const form = document.getElementById(id);
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const actionText = this.querySelector('button').innerText.trim();
            const typeValue  = this.querySelector('input[name="type"]').value;
            const typeLabel  = typeValue.charAt(0).toUpperCase() + typeValue.slice(1);

            Swal.fire({
                title: 'Konfirmasi Approval',
                html: `Apakah Anda yakin ingin melakukan <strong>${actionText}</strong> untuk tab <strong>${typeLabel}</strong> bulan ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Indigo-600
                cancelButtonColor: '#ef4444',  // Red-500
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    
                    // Show a quick loading toast
                    window.dispatchEvent(new CustomEvent('toast', { detail: { title: 'Memproses...', message: 'Mohon tunggu', type: 'info' } }));

                    axios.post(this.action, formData)
                        .then(({ data }) => {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Berhasil',
                                    message: data.message || 'Approval berhasil dilakukan.',
                                    type: 'success'
                                }
                            }));
                            // Refresh tables
                            window.reloadEvaluationTables();
                        })
                        .catch((err) => {
                            console.error(err);
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Gagal',
                                    message: err.response?.data?.message || 'Terjadi kesalahan saat memproses approval.',
                                    type: 'error'
                                }
                            }));
                        });
                }
            });
        });
    });

    // ── Update batch-action hidden type inputs and tab styling from active tab ────────
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            // Update inputs
            const type = this.dataset.type;
            document.getElementById('approve-dept-type').value = type;
            document.getElementById('approve-hrd-type').value  = type;

            // Restyle ALL tabs to inactive
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(b => {
                b.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                b.classList.add('text-slate-500');
            });
            
            // Style ACTIVE tab
            this.classList.remove('text-slate-500');
            this.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
        });
    });
    // Set initial value for Regular tab
    document.getElementById('approve-dept-type').value = 'regular';
    document.getElementById('approve-hrd-type').value  = 'regular';
    
    // Initial active styling for default tab
    const activeTab = document.querySelector('.nav-link.active');
    if(activeTab) {
        activeTab.classList.remove('text-slate-500');
        activeTab.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
    }

    // ── Status chip AJAX refresh ──────────────────────────────────────────────
    function refreshSummary() {
        axios.get('{{ route("evaluation.summary") }}', {
            params: { month: currentMonth, year: currentYear }
        }).then(({ data }) => {
            ['pending','graded','dept_approved','fully_approved','rejected','total'].forEach(k => {
                const el = document.querySelector('[data-chip="' + k + '"]');
                if (el) el.textContent = data[k] ?? 0;
            });
        });
    }

    // Reload all tables + refresh summary after grade/approve/reject
    window.reloadEvaluationTables = function () {
        dtRegular.ajax.reload(null, false);
        dtYayasan?.ajax.reload(null, false);
        dtMagang?.ajax.reload(null, false);
        refreshSummary();
    };

})();
</script>
@endpush
