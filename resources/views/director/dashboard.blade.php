@extends('new.layouts.app')

@section('title', 'Director Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl px-3 py-6 sm:px-4 lg:px-0 space-y-6">
        
        {{-- HEADER CARD --}}
        <div class="glass-card flex flex-wrap items-center justify-between gap-4 p-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    Welcome, {{ Auth::user()->name }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Here's a high-level overview of the Purchase Requests module.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-requests.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:shadow-indigo-300 hover:-translate-y-0.5">
                    <i class="bi bi-box-arrow-up-right text-lg"></i>
                    <span>Open PR Index</span>
                </a>
            </div>
        </div>

        {{-- INSIGHTS MODULE --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            {{-- Pending My Approval --}}
            <a href="{{ route('purchase-requests.index', ['status' => 'waiting']) }}" class="block group">
                <div class="glass-card relative overflow-hidden p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-l-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 group-hover:text-slate-700 transition-colors">Action Required</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($prStats['pending_my_approval']) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-500 transition-transform group-hover:scale-110">
                            <i class="bi bi-exclamation-circle text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-amber-600 font-medium">PRs awaiting your signature</span>
                    </div>
                </div>
            </a>

            {{-- In Review Globally --}}
            <a href="{{ route('purchase-requests.index') }}" class="block group">
                <div class="glass-card relative overflow-hidden p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 group-hover:text-slate-700 transition-colors">In Review</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($prStats['in_review']) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 transition-transform group-hover:scale-110">
                            <i class="bi bi-hourglass-split text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-blue-600 font-medium">Total active PRs across dept</span>
                    </div>
                </div>
            </a>

            {{-- Total Value Pending --}}
            <a href="{{ route('purchase-requests.index') }}" class="block group">
                <div class="glass-card relative overflow-hidden p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-l-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 group-hover:text-slate-700 transition-colors">Est. Value Pending</p>
                            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">IDR {{ number_format($prStats['total_value_pending'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 transition-transform group-hover:scale-110">
                            <i class="bi bi-currency-dollar text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-indigo-600 font-medium">Approx queue value</span>
                    </div>
                </div>
            </a>

            {{-- Approved This Month --}}
            <a href="{{ route('purchase-requests.index') }}" class="block group">
                <div class="glass-card relative overflow-hidden p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-l-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 group-hover:text-slate-700 transition-colors">Approved This Month</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($prStats['approved_this_month']) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 transition-transform group-hover:scale-110">
                            <i class="bi bi-check-circle text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-emerald-600 font-medium">Successfully processed</span>
                    </div>
                </div>
            </a>

        </div>
        
        {{-- QUICK INSTRUCTIONS --}}
        <div class="glass-card p-5 mt-6 border border-blue-100 bg-blue-50/30">
            <div class="flex items-start gap-4">
                <div class="mt-1 text-blue-500">
                    <i class="bi bi-info-circle-fill text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900">Director Quick Actions</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Use the <strong>Open PR Index</strong> button to view all purchase requests in detail. 
                        As a director, you can use the <strong>Approve Selected</strong> and <strong>Reject Selected</strong> batch actions to process multiple requests simultaneously.
                    </p>
                </div>
            </div>
        </div>

    </div>
@endsection
