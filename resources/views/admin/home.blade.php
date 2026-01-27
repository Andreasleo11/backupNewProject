@extends('new.layouts.app')

@section('page-title', __('Dashboard'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('update.dept') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium inline-block">
            Update Dept Yayasan
        </a>

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

                <p class="text-slate-700">{{ __('Hello monseiurs') }}</p>
            </div>
        </div>
    </div>
@endsection
