@props([
    'status' => '',
    'variant' => 'filled',
    'size' => 'sm'
])

@php
    $statusConfig = [
        'draft' => ['label' => 'Draft', 'color' => 'slate', 'bgClass' => 'bg-slate-100', 'textClass' => 'text-slate-800'],
        'waiting' => ['label' => 'Waiting Approval', 'color' => 'yellow', 'bgClass' => 'bg-yellow-100', 'textClass' => 'text-yellow-800'],
        'approved' => ['label' => 'Approved', 'color' => 'green', 'bgClass' => 'bg-green-100', 'textClass' => 'text-green-800'],
        'rejected' => ['label' => 'Rejected', 'color' => 'red', 'bgClass' => 'bg-red-100', 'textClass' => 'text-red-800'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'gray', 'bgClass' => 'bg-gray-100', 'textClass' => 'text-gray-800'],
        'shipped' => ['label' => 'Shipped', 'color' => 'blue', 'bgClass' => 'bg-blue-100', 'textClass' => 'text-blue-800'],
        'delivered' => ['label' => 'Delivered', 'color' => 'emerald', 'bgClass' => 'bg-emerald-100', 'textClass' => 'text-emerald-800'],
    ];

    // Handle both enum values and string statuses
    $statusKey = strtolower(is_object($status) ? $status->value : $status);

    $config = $statusConfig[$statusKey] ?? [
        'label' => ucfirst(strtolower($status)),
        'color' => 'slate',
        'bgClass' => 'bg-slate-100',
        'textClass' => 'text-slate-800'
    ];

    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base',
    ];

    $baseClasses = 'inline-flex items-center font-medium rounded-full transition-colors';
    $variantClasses = $variant === 'outlined'
        ? "border border-{$config['color']}-300 text-{$config['color']}-700 bg-white"
        : $config['bgClass'] . ' ' . $config['textClass'];

    $finalClasses = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['sm']) . ' ' . $variantClasses;
@endphp

@if(in_array(strtolower($status), ['waiting', 'processing']))
    <span {{ $attributes->merge(['class' => $finalClasses]) }}>
        <svg class="w-3 h-3 mr-1.5 -ml-0.5 animate-spin" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
        </svg>
        {{ $config['label'] }}
    </span>
@else
    <span {{ $attributes->merge(['class' => $finalClasses]) }}>
        {{ $config['label'] }}
    </span>
@endif