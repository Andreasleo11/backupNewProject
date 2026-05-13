{{-- ===== STATUS TABS =====
     Replaces both the 5-card metrics grid and the urgent action banners.
     Clicking a tab sets infoStatus via existing Livewire methods.
     Badge turns red when count > 0 and action is urgent.
--}}
@php
    $tabs = [];

    // All — always visible to everyone
    $tabs[] = [
        'key'    => '__all__',
        'label'  => 'All',
        'icon'   => 'bx-list-ul',
        'count'  => null,
        'urgent' => false,
        'show'   => true,
    ];

    // My Sign — shown to users who can approve (Q1: always shown per user answer)
    $tabs[] = [
        'key'    => 'my_approval',
        'label'  => 'My Approval',
        'icon'   => 'bx-edit',
        'count'  => $stats['my_approval_count'] > 0 ? $stats['my_approval_count'] : null,
        'urgent' => $stats['my_approval_count'] > 0,
        'show'   => $canApprove || $stats['my_approval_count'] > 0,
    ];

    // Detail-reviewer-only tabs
    if ($isDetailReviewer) {
        $tabs[] = [
            'key'    => 'pending',
            'label'  => 'Pending',
            'icon'   => 'bx-time-five',
            'count'  => $stats['pending'] > 0 ? $stats['pending'] : null,
            'urgent' => $stats['pending'] > 0,
            'show'   => true,
        ];
        $tabs[] = [
            'key'    => 'fully_approved',
            'label'  => 'Approved',
            'icon'   => 'bx-check-double',
            'count'  => $stats['fully_approved'] > 0 ? $stats['fully_approved'] : null,
            'urgent' => false,
            'show'   => true,
        ];
        $tabs[] = [
            'key'    => 'fully_rejected',
            'label'  => 'Rejected',
            'icon'   => 'bx-x-circle',
            'count'  => $stats['fully_rejected'] > 0 ? $stats['fully_rejected'] : null,
            'urgent' => false,
            'show'   => true,
        ];
        $tabs[] = [
            'key'    => 'partially_approved',
            'label'  => 'Partially Approved',
            'icon'   => 'bx-x-circle',
            'count'  => $stats['partially_approved'] > 0 ? $stats['partially_approved'] : null,
            'urgent' => false,
            'show'   => true,
        ];
    }
@endphp

<div class="flex items-center gap-1 overflow-x-auto scrollbar-hide"
    wire:loading.class="opacity-50 pointer-events-none"
    wire:target="startDate,endDate,dept,search,range,perPage,clearFilter,resetFilters">

    {{-- Tab background pill --}}
    <div class="flex items-center gap-0.5 rounded-xl bg-slate-100/80 p-0.5 w-max">
        @foreach ($tabs as $tab)
            @if (!$tab['show']) @continue @endif

            @php
                $isActive = ($tab['key'] === '__all__')
                    ? $infoStatus === null || $infoStatus === ''
                    : $infoStatus === $tab['key'];
            @endphp

            <button type="button"
                @if ($tab['key'] === '__all__')
                    wire:click="clearFilter('infoStatus')"
                @else
                    wire:click="setInfoFilter('{{ $tab['key'] }}')"
                @endif
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs transition-all whitespace-nowrap
                    {{ $isActive
                        ? 'bg-white text-indigo-600 font-black shadow-sm'
                        : 'text-slate-500 font-bold hover:text-slate-700' }}">

                <i class="bx {{ $tab['icon'] }} text-sm {{ $isActive ? 'text-indigo-500' : '' }}"></i>
                {{ $tab['label'] }}

                @if ($tab['count'] !== null)
                    <span class="inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[9px] font-black leading-none min-w-[1.1rem]
                        {{ $tab['urgent']
                            ? 'bg-rose-500 text-white'
                            : ($isActive ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-300/70 text-slate-600') }}">
                        {{ number_format($tab['count']) }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>
</div>
