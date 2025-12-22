<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">

    {{-- Header + Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-slate-900">
                    Delivery Note #{{ $deliveryNote->id }}
                </h1>

                @if ($deliveryNote->status === 'draft')
                    <span
                        class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-amber-200">
                        Draft
                    </span>
                @else
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 ring-1 ring-emerald-200">
                        Submitted
                    </span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">
                Ringkasan informasi pengiriman, kendaraan, dan tujuan.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('delivery-notes.print', $deliveryNote->id) }}" target="_blank"
                class="inline-flex items-center rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-800 shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                Print
            </a>

            @if (auth()->check() || $deliveryNote->is_latest)
                <a href="{{ route('delivery-notes.edit', $deliveryNote->id) }}"
                    class="inline-flex items-center rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-xs font-medium text-indigo-800 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Edit
                </a>
            @endif

            <a href="{{ route('delivery-notes.index') }}"
                class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                Back to list
            </a>
        </div>
    </div>

    {{-- Basic Info --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-6 border-b border-slate-100 px-4 py-4 sm:grid-cols-2 lg:px-6">
            <div class="space-y-3 text-sm">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Ritasi
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->ritasi }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Delivery note date
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->formatted_delivery_note_date }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Departure time
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->formatted_departure_time }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Return time
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->formatted_return_time }}
                    </div>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Branch
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->branch }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Vehicle number
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->vehicle->plate_number ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Driver name
                    </div>
                    <div class="mt-0.5 text-slate-900">
                        {{ $deliveryNote->vehicle->driver_name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Destinations --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">
                Destinations
            </h2>
            <p class="text-xs text-slate-500">
                Termasuk nomor delivery order dan biaya per tujuan.
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full table-fixed divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-[5%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            #
                        </th>
                        <th class="w-[10%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Destination
                        </th>
                        <th class="w-[40%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Delivery orders
                        </th>
                        <th class="w-[15%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Remarks
                        </th>
                        <th
                            class="w-[10%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 text-right">
                            Driver cost
                        </th>
                        <th
                            class="w-[10%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 text-right">
                            Kenek cost
                        </th>
                        <th
                            class="w-[10%] px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 text-right">
                            Balikan cost
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($deliveryNote->destinations as $i => $d)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 align-top text-xs text-slate-500">
                                {{ $i + 1 }}
                            </td>

                            <td class="px-3 py-2 align-top text-sm text-slate-800">
                                {{ $d->destination }}
                            </td>

                            {{-- Delivery Orders (paling besar) --}}
                            <td class="px-3 py-2 align-top">
                                @if ($d->deliveryOrders->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($d->deliveryOrders as $order)
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                                                title="Order #{{ $order->delivery_order_number }}">
                                                {{ $order->delivery_order_number }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">No delivery order</span>
                                @endif
                            </td>

                            {{-- Remarks --}}
                            <td class="px-3 py-2 align-top text-sm text-slate-700 whitespace-normal break-words">
                                @if ($d->remarks)
                                    <span title="{{ $d->remarks }}">
                                        {{ \Illuminate\Support\Str::limit($d->remarks, 80) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">No remarks</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 align-top text-right text-sm tabular-nums text-slate-800">
                                {{ $d->driver_cost_currency }}
                                {{ number_format($d->driver_cost ?? 0, 2, '.', ',') }}
                            </td>
                            <td class="px-3 py-2 align-top text-right text-sm tabular-nums text-slate-800">
                                {{ $d->kenek_cost_currency }}
                                {{ number_format($d->kenek_cost ?? 0, 2, '.', ',') }}
                            </td>
                            <td class="px-3 py-2 align-top text-right text-sm tabular-nums text-slate-800">
                                {{ $d->balikan_cost_currency }}
                                {{ number_format($d->balikan_cost ?? 0, 2, '.', ',') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                                No destinations found.
                            </td>
                        </tr>
                    @endforelse

                    {{-- total row tetap sama seperti punyamu --}}
                </tbody>
            </table>
        </div>
    </section>

</div>
