@extends('new.layouts.app')

@section('page-title', __('Dashboard'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Dashboard') }}</h2>
            </div>

            <div class="p-6">
                @if (session('status'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                <p class="text-slate-700">{{ __('Welcome Back Staff') }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('header.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                Verification Report
            </a>
            <a href="{{ route('report.view') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                View Report
            </a>
            <a href="{{ route('pe.landing') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                PE Project
            </a>
        </div>
    </div>
@endsection
