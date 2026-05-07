@extends('new.layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest">
                <li class="inline-flex items-center">
                    <a href="{{ route('po.index') }}"
                            class="inline-flex items-center text-slate-400 hover:text-indigo-600 transition-colors">
                        <i class="bi bi-house-door mr-2"></i>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-slate-300 text-sm mx-1"></i>
                        <span class="text-slate-600">Edit Purchase Order</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Edit Purchase Order</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Update the details of purchase order #{{ $po->po_number }}.</p>
        </div>

        {{-- Edit form --}}
        @livewire('purchase-order.edit-purchase-order-form', ['poId' => $po->id])
    </div>
@endsection
