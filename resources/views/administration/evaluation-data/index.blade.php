{{--
    DEPRECATED — This view was used by the P&E Data (Monthly) Excel-upload pipeline.
    The pipeline has been removed. Routes admin.evaluation-data.* no longer exist.
    This file is safe to delete.

    @deprecated Replaced by live attendance_records queries in EvaluationController.
--}}
@extends('new.layouts.app')

@section('title', '[DEPRECATED] P&E Data Monthly')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-16 text-center">
        <div class="glass-card p-10 border border-amber-200 bg-amber-50/60 space-y-4">
            <div class="mx-auto h-16 w-16 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-500">
                <i class="bx bx-trash text-4xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Fitur Telah Dihapus</h1>
            <p class="text-sm text-slate-600">
                Fitur "P&amp;E Data Monthly" telah digantikan oleh data absensi langsung dari
                <strong>Attendance Records</strong>.
            </p>
            <a href="{{ route('evaluation.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition-all">
                <i class="bx bx-arrow-back"></i> Ke Evaluasi Karyawan
            </a>
        </div>
    </div>
@endsection
