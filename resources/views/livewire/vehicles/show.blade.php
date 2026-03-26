<div class="max-w-5xl mx-auto px-3 md:px-4 py-4 space-y-4">
    {{-- Back link --}}
    <a href="{{ route('vehicles.index') }}"
        class="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800">
        <i class="bi bi-arrow-left mr-1 text-[0.8rem]"></i>
        Back
    </a>

    @php
        $last = $vehicle->latestService;
        $lastKm = (int) ($last->odometer ?? 0);
        $nextKm = $lastKm ? $lastKm + 10000 : null;
    @endphp

    {{-- Header card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 md:p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                        <i class="bi bi-truck text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-semibold text-slate-900 md:text-lg">
                            {{ $vehicle->display_name }}
                        </h1>
                        <div class="mt-1 text-xs text-slate-500 md:text-sm">
                            VIN:
                            <span class="font-mono">
                                {{ $vehicle->vin ?? '—' }}
                            </span>
                            <span class="mx-1">•</span>
                            Status:
                            @php
                                $variant = $vehicle->status->variant();
                                $statusClass = match ($variant) {
                                    'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                                    'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
                                    'danger' => 'bg-rose-50 text-rose-800 ring-rose-200',
                                    'info' => 'bg-sky-50 text-sky-800 ring-sky-200',
                                    default => 'bg-slate-50 text-slate-700 ring-slate-200',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset {{ $statusClass }}">
                                <i class="bi bi-{{ $vehicle->status->icon() }} mr-1 text-[0.8rem]"></i>
                                {{ $vehicle->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('vehicles.edit', $vehicle) }}"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="bi bi-pencil mr-1 text-[0.9rem]"></i>
                        Edit Vehicle
                    </a>
                    @if (!$vehicle->is_sold)
                        <a href="{{ route('services.create', $vehicle) }}"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                            <i class="bi bi-wrench-adjustable mr-1 text-[0.9rem]"></i>
                            Add Service
                        </a>
                    @endif
                </div>
            </div>

            @if ($vehicle->is_sold)
                <div
                    class="mt-3 flex items-start gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                    <i class="bi bi-cash-coin mt-0.5 text-slate-500"></i>
                    <div>
                        Sold on
                        <strong>{{ $vehicle->sold_at?->isoFormat('DD MMM YYYY') ?? '—' }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid gap-3 md:grid-cols-3">
        {{-- Odometer --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Odometer
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <i class="bi bi-speedometer2 text-slate-400"></i>
                    <div class="text-lg font-semibold text-slate-900">
                        {{ number_format($vehicle->odometer) }}
                        <span class="text-sm font-normal text-slate-500">km</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Last service --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Last Service
                </div>
                <div class="mt-2 text-sm">
                    @if ($last)
                        <div class="font-semibold text-slate-900">
                            {{ $last->service_date->isoFormat('DD MMM YYYY') }}
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            at {{ $last->workshop ?? 'Internal' }}
                            • {{ number_format($last->odometer ?? 0) }} km
                        </div>
                    @else
                        <div class="text-slate-400">—</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Costs --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Costs
                </div>
                <div class="mt-2 flex items-start justify-between text-sm">
                    <div>
                        <div class="text-xs text-slate-500">YTD</div>
                        <div class="font-semibold text-slate-900">
                            Rp {{ number_format($ytdCost, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Lifetime</div>
                        <div class="font-semibold text-slate-900">
                            Rp {{ number_format($lifetimeCost, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Service history --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Header / filters --}}
        <div class="flex flex-col gap-2 border-b border-slate-100 px-3 py-2.5 md:flex-row md:items-center">
            <span class="text-sm font-semibold text-slate-800">Service History</span>

            <div class="mt-1 flex flex-1 flex-wrap items-center justify-end gap-2 md:mt-0">
                {{-- Year --}}
                <div class="flex items-center gap-1 text-xs text-slate-500">
                    <span>Year</span>
                    <select wire:model.live="year"
                        class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="all">All</option>
                        @for ($y = now()->year; $y >= now()->year - 10; $y--)
                            <option>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Workshop filter --}}
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="workshop" placeholder="Filter workshop…"
                        class="w-40 rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 md:w-52">
                </div>

                {{-- Per page --}}
                <div class="flex items-center gap-1 text-xs text-slate-500">
                    <span>Per page</span>
                    <select wire:model.live="perPage"
                        class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option>10</option>
                        <option selected>20</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-h-[70vh] overflow-auto text-sm">
            <table class="min-w-full border-collapse">
                <thead
                    class="sticky top-0 z-10 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="whitespace-nowrap px-3 py-2 text-left">Odometer</th>
                        <th class="px-3 py-2 text-left">Workshop</th>
                        <th class="px-3 py-2 text-left">Items</th>
                        <th class="whitespace-nowrap px-3 py-2 text-left">Total Cost</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($records as $r)
                        @php
                            $items = $r->items ?? collect();
                            $preview = $items->take(3);
                            $rest = $items->skip(3);
                        @endphp
                        <tr wire:key="svc-row-{{ $r->id }}" class="hover:bg-slate-50/70">
                            {{-- Date --}}
                            <td class="whitespace-nowrap px-3 py-2 align-top text-slate-800">
                                {{ optional($r->service_date)->isoFormat('DD MMM YYYY') }}
                            </td>

                            {{-- Odometer --}}
                            <td class="px-3 py-2 align-top text-slate-800">
                                {{ number_format($r->odometer ?? 0) }}
                                <span class="text-xs text-slate-400">km</span>
                            </td>

                            {{-- Workshop --}}
                            <td class="px-3 py-2 align-top text-slate-800">
                                {{ $r->workshop ?? 'Internal' }}
                            </td>

                            {{-- Items --}}
                            <td class="px-3 py-2 align-top text-xs text-slate-800">
                                @if ($items->isNotEmpty())
                                    <div x-data="{ open: false }" class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-1">
                                            @foreach ($preview as $it)
                                                <span
                                                    class="inline-flex max-w-full items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-slate-700">
                                                    <span class="truncate">
                                                        {{ $it->part_name }}
                                                    </span>
                                                    <span class="ml-1 text-slate-400">
                                                        ({{ $it->action }})
                                                    </span>
                                                    @if ($it->qty)
                                                        <span class="ml-1 text-slate-400">
                                                            —
                                                            {{ rtrim(rtrim(number_format($it->qty, 2, '.', ''), '0'), '.') }}
                                                            {{ $it->uom }}
                                                        </span>
                                                    @endif
                                                </span>
                                            @endforeach

                                            @if ($rest->isNotEmpty())
                                                <button type="button" @click="open = !open"
                                                    class="ml-1 text-[11px] font-medium text-indigo-600 hover:underline">
                                                    <span x-show="!open">+{{ $rest->count() }} more</span>
                                                    <span x-show="open">Show less</span>
                                                </button>
                                            @endif
                                        </div>

                                        @if ($rest->isNotEmpty())
                                            <div x-show="open" x-cloak class="flex flex-wrap gap-1">
                                                @foreach ($rest as $it)
                                                    <span
                                                        class="inline-flex max-w-full items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-slate-700">
                                                        <span class="truncate">
                                                            {{ $it->part_name }}
                                                        </span>
                                                        <span class="ml-1 text-slate-400">
                                                            ({{ $it->action }})
                                                        </span>
                                                        @if ($it->qty)
                                                            <span class="ml-1 text-slate-400">
                                                                —
                                                                {{ rtrim(rtrim(number_format($it->qty, 2, '.', ''), '0'), '.') }}
                                                                {{ $it->uom }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">No items</span>
                                @endif
                            </td>

                            {{-- Total cost --}}
                            <td class="whitespace-nowrap px-3 py-2 align-top text-slate-800">
                                Rp {{ number_format($r->total_cost, 0, ',', '.') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-3 py-2 align-top text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('services.edit', $r) }}"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm hover:bg-slate-50"
                                        title="Edit service">
                                        <i class="bi bi-pencil text-[0.8rem]"></i>
                                    </a>

                                    @if ($canManage)
                                        <button type="button" wire:click="deleteService({{ $r->id }})"
                                            wire:confirm="Delete service on {{ optional($r->service_date)->isoFormat('DD MMM YYYY') }} ({{ number_format($r->odometer ?? 0) }} km)? This cannot be undone."
                                            wire:loading.attr="disabled" wire:target="deleteService"
                                            class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 shadow-sm hover:bg-rose-100 disabled:opacity-50">
                                            <span wire:loading wire:target="deleteService"
                                                class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-rose-400 border-t-transparent"></span>
                                            <i class="bi bi-trash text-[0.8rem]" wire:loading.remove
                                                wire:target="deleteService"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="bi bi-clipboard2-x text-2xl text-slate-300"></i>
                                    <div class="font-semibold text-slate-700">No records yet.</div>
                                    <div class="text-xs text-slate-500">
                                        Add the first service record to get started.
                                    </div>
                                    @if (!$vehicle->is_sold)
                                        <a href="{{ route('services.create', $vehicle) }}"
                                            class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                            <i class="bi bi-plus-lg mr-1 text-[0.8rem]"></i>
                                            Add Service
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (kalau pakai) --}}
        @if (method_exists($records, 'links'))
            <div class="border-t border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
