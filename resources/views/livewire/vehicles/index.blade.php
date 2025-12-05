<div class="max-w-6xl mx-auto px-3 md:px-4 py-4 space-y-4">
    {{-- Toolbar --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 p-3 lg:flex-row lg:items-center">

            {{-- Left: search + status filter --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:flex-1">
                {{-- Search --}}
                <div class="w-full max-w-xs">
                    <div class="relative">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="bi bi-search text-sm"></i>
                        </span>
                        <input type="text" placeholder="Search plate / brand / model / driver"
                            wire:model.live.debounce.300ms="q" aria-label="Search vehicles"
                            class="block w-full rounded-md border border-slate-300 bg-white py-2 pl-9 pr-8 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @if ($q !== '')
                            <button type="button" wire:click="$set('q','')"
                                class="absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400 hover:text-slate-600"
                                aria-label="Clear search">
                                <i class="bi bi-x text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Status filter --}}
                @php use App\Enums\VehicleStatus; @endphp
                @if ($fullFeature)
                    <div class="overflow-x-auto">
                        <div class="inline-flex flex-wrap gap-2">
                            {{-- All --}}
                            <button type="button" wire:click="$set('status','all')"
                                class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-medium transition
                       {{ $status === 'all'
                           ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                           : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                <i class="bi bi-ui-checks-grid mr-1 text-[0.8rem]"></i>
                                All
                            </button>

                            {{-- Enum-driven options --}}
                            @foreach (VehicleStatus::cases() as $st)
                                @php
                                    $isActive = $status === $st->value;
                                    $activeClass = $st->filterActiveClasses();
                                    $inactiveClass = $st->filterInactiveClasses();
                                @endphp

                                <button type="button" wire:click="$set('status','{{ $st->value }}')"
                                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-medium transition
                           {{ $isActive ? $activeClass : $inactiveClass }}">
                                    <i class="bi bi-{{ $st->icon() }} mr-1 text-[0.8rem]"></i>
                                    {{ $st->label() }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right: per page + New Vehicle --}}
            <div class="flex items-center justify-between gap-3 lg:justify-end lg:ml-auto">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span>Per page</span>
                    <select wire:model.live="perPage" aria-label="Rows per page"
                        class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <a href="{{ route('vehicles.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                    New Vehicle
                </a>
            </div>
        </div>
    </div>

    {{-- Desktop table (md+) --}}
    <div class="relative hidden md:block rounded-xl border border-slate-200 bg-white shadow-sm">
        @php
            $aria = fn($f) => $sort === $f ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
            $chev = fn($f) => $sort === $f
                ? ($dir === 'asc'
                    ? 'bi-chevron-up'
                    : 'bi-chevron-down')
                : 'bi-chevron-expand';
        @endphp

        <div class="max-h-[70vh] overflow-auto" wire:loading.class="opacity-50"
            wire:target="q,status,perPage,page,sort,dir">
            <table class="min-w-full text-sm text-left text-slate-700">
                @if ($fullFeature)
                    <thead
                        class="sticky top-0 z-10 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" role="button" wire:click="sortBy('plate_number')"
                                aria-sort="{{ $aria('plate_number') }}" class="cursor-pointer px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Vehicle</span>
                                    <i class="bi {{ $chev('plate_number') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('driver_name')"
                                aria-sort="{{ $aria('driver_name') }}"
                                class="cursor-pointer whitespace-nowrap px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Driver</span>
                                    <i class="bi {{ $chev('driver_name') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('odometer')"
                                aria-sort="{{ $aria('odometer') }}" class="cursor-pointer whitespace-nowrap px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Odometer</span>
                                    <i class="bi {{ $chev('odometer') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('status')"
                                aria-sort="{{ $aria('status') }}" class="cursor-pointer px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Status</span>
                                    <i class="bi {{ $chev('status') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('last_service_date')"
                                aria-sort="{{ $aria('last_service_date') }}" class="cursor-pointer px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Last Service</span>
                                    <i class="bi {{ $chev('last_service_date') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-2">
                                Checked / Parts
                            </th>
                            <th scope="col" class="px-4 py-2 text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $v)
                            @php $last = $v->latestService; @endphp
                            <tr wire:key="veh-{{ $v->id }}" class="hover:bg-slate-50/70">
                                {{-- Vehicle --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="font-semibold text-slate-900">
                                        {{ $v->display_name }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-slate-500 truncate max-w-xs">
                                        VIN:
                                        <span class="font-monospace">
                                            {{ $v->vin ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Driver --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center gap-1 text-slate-700">
                                        <i class="bi bi-person text-slate-400 text-[0.85rem]"></i>
                                        {{ $v->driver_name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Odometer --}}
                                <td class="px-4 py-3 align-top text-sm text-slate-800">
                                    {{ number_format($v->odometer) }}
                                    <span class="text-xs text-slate-400">km</span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 align-top text-sm">
                                    @php
                                        $variant = $v->status->variant();
                                        $statusClass = match ($variant) {
                                            'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                                            'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
                                            'danger' => 'bg-rose-50 text-rose-800 ring-rose-200',
                                            'info' => 'bg-sky-50 text-sky-800 ring-sky-200',
                                            default => 'bg-slate-50 text-slate-700 ring-slate-200',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                        <i class="bi bi-{{ $v->status->icon() }} mr-1 text-[0.8rem]"></i>
                                        {{ $v->status->label() }}
                                    </span>
                                </td>

                                {{-- Last service --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap text-sm">
                                    @if ($last)
                                        <div class="font-semibold text-slate-900">
                                            <time datetime="{{ $last->service_date->toDateString() }}">
                                                {{ $last->service_date->isoFormat('DD MMM YYYY') }}
                                            </time>
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            at {{ $last->workshop ?? 'Internal' }}
                                            • {{ number_format($last->odometer ?? 0) }} km
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">No service yet</span>
                                    @endif
                                </td>

                                {{-- Checked / Parts --}}
                                @php
                                    /** @var \App\Models\ServiceRecord|null $last */
                                    $last = $v->latestService;
                                    $shown = $last?->items?->count() ?? 0;
                                    $total = $last?->items_count ?? $shown;
                                @endphp
                                <td class="px-4 py-3 align-top text-xs">
                                    @if ($total > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($last?->items ?? collect() as $it)
                                                <span
                                                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-slate-700">
                                                    {{ $it->part_name }}
                                                    <span class="ml-1 text-slate-400">
                                                        ({{ $it->action }})
                                                    </span>
                                                </span>
                                            @endforeach

                                            @if ($total > $shown)
                                                <span class="text-[11px] text-slate-400">
                                                    +{{ $total - $shown }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">No items</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 align-top text-right text-sm">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('vehicles.show', $v) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm hover:bg-slate-50"
                                            title="View detail">
                                            <i class="bi bi-eye text-[0.8rem] mr-1"></i>
                                            Detail
                                        </a>
                                        <a href="{{ route('vehicles.edit', $v) }}"
                                            class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs text-indigo-700 shadow-sm hover:bg-indigo-100"
                                            title="Edit vehicle">
                                            <i class="bi bi-pencil text-[0.8rem] mr-1"></i>
                                            Edit
                                        </a>
                                        <a href="{{ route('services.create', $v) }}"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                                            title="Add service">
                                            <i class="bi bi-wrench-adjustable text-[0.8rem] mr-1"></i>
                                            Service
                                        </a>
                                        <button type="button" wire:click="deleteVehicle({{ $v->id }})"
                                            wire:confirm="Delete {{ $v->display_name }}? This cannot be undone."
                                            wire:loading.attr="disabled" wire:target="deleteVehicle"
                                            class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 shadow-sm hover:bg-rose-100 disabled:opacity-50">
                                            <span wire:loading wire:target="deleteVehicle"
                                                class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-rose-400 border-t-transparent"></span>
                                            <i class="bi bi-trash text-[0.8rem]" wire:loading.remove
                                                wire:target="deleteVehicle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="bi bi-truck text-2xl text-slate-300"></i>
                                        <div class="font-semibold text-slate-700">No vehicles found</div>
                                        <p class="text-xs text-slate-500">
                                            Try adjusting your search or add a new vehicle.
                                        </p>
                                        <a href="{{ route('vehicles.create') }}"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                            <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                                            New Vehicle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @else
                    {{-- Simplified head --}}
                    <thead
                        class="sticky top-0 z-10 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" role="button" wire:click="sortBy('plate_number')"
                                aria-sort="{{ $aria('plate_number') }}" class="cursor-pointer px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Vehicle</span>
                                    <i class="bi {{ $chev('plate_number') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" role="button" wire:click="sortBy('driver_name')"
                                aria-sort="{{ $aria('driver_name') }}"
                                class="cursor-pointer whitespace-nowrap px-4 py-2">
                                <div class="inline-flex items-center gap-1">
                                    <span>Driver</span>
                                    <i class="bi {{ $chev('driver_name') }} text-[0.7rem]"></i>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-2 text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $v)
                            <tr wire:key="veh-{{ $v->id }}" class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 align-top">
                                    <div class="font-semibold text-slate-900">
                                        {{ $v->display_name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center gap-1 text-slate-700">
                                        <i class="bi bi-person text-slate-400 text-[0.85rem]"></i>
                                        {{ $v->driver_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top text-right">
                                    <a href="{{ route('vehicles.edit', $v) }}"
                                        class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs text-indigo-700 shadow-sm hover:bg-indigo-100"
                                        title="Edit vehicle">
                                        <i class="bi bi-pencil text-[0.8rem] mr-1"></i>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="bi bi-truck text-2xl text-slate-300"></i>
                                        <div class="font-semibold text-slate-700">No vehicles found</div>
                                        <a href="{{ route('vehicles.create') }}"
                                            class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                            <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                                            New Vehicle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>

        {{-- Footer + pagination --}}
        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-500"
            wire:loading.class="opacity-50" wire:target="q,status,perPage,page,sort,dir">
            <div>
                Showing {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }} of {{ $vehicles->total() }}
            </div>
            <div class="text-right">
                {{ $vehicles->links() }}
            </div>
        </div>

        {{-- Loading overlay --}}
        <div wire:loading.flex wire:target="q,status,perPage,page,sort,dir"
            class="absolute inset-0 z-20 hidden items-center justify-center bg-white/75">
            <div class="h-6 w-6 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></div>
        </div>
    </div>

    {{-- Mobile cards (< md) --}}
    <div class="space-y-2 md:hidden">
        @forelse ($vehicles as $v)
            @php
                $last = $v->latestService;
                $shown = $last?->items?->count() ?? 0;
                $total = $last?->items_total ?? ($last?->items_count ?? $shown);
                $variant = $v->status->variant();
                $statusClass = match ($variant) {
                    'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                    'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
                    'danger' => 'bg-rose-50 text-rose-800 ring-rose-200',
                    'info' => 'bg-sky-50 text-sky-800 ring-sky-200',
                    default => 'bg-slate-50 text-slate-700 ring-slate-200',
                };
            @endphp

            @if ($fullFeature)
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $v->display_name }}
                                </div>
                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    VIN:
                                    <span class="font-monospace">
                                        {{ $v->vin ?? '—' }}
                                    </span>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset {{ $statusClass }}">
                                <i class="bi bi-{{ $v->status->icon() }} mr-1 text-[0.8rem]"></i>
                                {{ $v->status->label() }}
                            </span>
                        </div>

                        <div class="mt-2 space-y-1 text-xs text-slate-700">
                            <div class="flex items-center gap-1">
                                <i class="bi bi-person text-slate-400 text-[0.85rem]"></i>
                                <span>{{ $v->driver_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="bi bi-speedometer text-slate-400 text-[0.85rem]"></i>
                                <span>{{ number_format($v->odometer) }} km</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="bi bi-wrench-adjustable text-slate-400 text-[0.85rem]"></i>
                                @if ($last)
                                    <span>
                                        {{ $last->service_date->isoFormat('DD MMM YYYY') }}
                                        • {{ number_format($last->odometer ?? 0) }} km
                                    </span>
                                @else
                                    <span class="text-slate-400">No service yet</span>
                                @endif
                            </div>
                        </div>

                        @if ($total > 0)
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($last?->items ?? collect() as $it)
                                    <span
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-slate-700">
                                        {{ $it->part_name }}
                                        <span class="ml-1 text-slate-400">
                                            ({{ $it->action }})
                                        </span>
                                    </span>
                                @endforeach
                                @if ($total > $shown)
                                    <span class="text-[11px] text-slate-400">
                                        +{{ $total - $shown }} more
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-2 border-t border-slate-100 px-3 py-2 text-xs"
                        wire:loading.class="opacity-50" wire:target="q,status,perPage,page">
                        <a href="{{ route('vehicles.show', $v) }}"
                            class="inline-flex flex-1 items-center justify-center rounded-md border border-slate-200 bg-white px-2 py-1 text-slate-700 hover:bg-slate-50">
                            <i class="bi bi-eye mr-1 text-[0.8rem]"></i>
                            Detail
                        </a>
                        <a href="{{ route('vehicles.edit', $v) }}"
                            class="inline-flex flex-1 items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-indigo-700 hover:bg-indigo-100">
                            <i class="bi bi-pencil mr-1 text-[0.8rem]"></i>
                            Edit
                        </a>
                        @if (!$v->is_sold)
                            <a href="{{ route('services.create', $v) }}"
                                class="inline-flex flex-1 items-center justify-center rounded-md bg-indigo-600 px-2 py-1 font-semibold text-white hover:bg-indigo-700">
                                <i class="bi bi-wrench-adjustable mr-1 text-[0.8rem]"></i>
                                Service
                            </a>
                        @endif
                        <button type="button" wire:click="deleteVehicle({{ $v->id }})"
                            wire:confirm="Delete {{ $v->display_name }}? This cannot be undone."
                            wire:loading.attr="disabled" wire:target="deleteVehicle"
                            class="inline-flex flex-1 items-center justify-center rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-rose-700 hover:bg-rose-100 disabled:opacity-50">
                            <span wire:loading wire:target="deleteVehicle"
                                class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-rose-400 border-t-transparent"></span>
                            <i class="bi bi-trash text-[0.8rem]" wire:loading.remove wire:target="deleteVehicle"></i>
                            <span class="ml-1">Delete</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $v->display_name }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    <i class="bi bi-person mr-1 text-[0.85rem] text-slate-400"></i>
                                    {{ $v->driver_name ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 border-t border-slate-100 px-3 py-2 text-xs"
                        wire:loading.class="opacity-50" wire:target="q,status,perPage,page">
                        <a href="{{ route('vehicles.edit', $v) }}"
                            class="inline-flex flex-1 items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-indigo-700 hover:bg-indigo-100">
                            <i class="bi bi-pencil mr-1 text-[0.8rem]"></i>
                            Edit
                        </a>
                    </div>
                </div>
            @endif
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm">
                <div class="mb-2 flex justify-center">
                    <i class="bi bi-truck text-2xl text-slate-300"></i>
                </div>
                <div class="font-semibold text-slate-700">No vehicles found</div>
                <a href="{{ route('vehicles.create') }}"
                    class="mt-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                    New Vehicle
                </a>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $vehicles->links() }}
        </div>
    </div>

    {{-- Floating add button (mobile) --}}
    <a href="{{ route('vehicles.create') }}"
        class="fixed bottom-4 right-4 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-500/40 hover:bg-indigo-700 md:hidden">
        <i class="bi bi-plus-lg text-lg"></i>
    </a>
</div>
