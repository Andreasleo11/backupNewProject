<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Header Content --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Fleet Management
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-500">
                Manage operational vehicles, monitor statuses, and driver assignments.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('vehicles.create') }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Register New Vehicle
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.out.duration.500ms
            class="mb-6 flex items-start gap-4 rounded-xl bg-emerald-50 px-4 py-3 border border-emerald-100 shadow-sm relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>
            <svg class="h-5 w-5 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Grid Layout for Table and Analytics --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Main Table Area --}}
        <div class="lg:col-span-3">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col h-full">

                {{-- Toolbar --}}
                <div
                    class="border-b border-slate-100 bg-slate-50/50 px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative w-full sm:max-w-xs group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Find plate number or driver..."
                            class="block w-full rounded-lg border-slate-300 bg-white pl-9 px-3 py-2 text-sm text-slate-900 shadow-sm transition
                                   focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-slate-400 focus:shadow-md">
                        <div wire:loading wire:target="search"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <div
                                class="h-4 w-4 rounded-full border-2 border-slate-200 border-t-indigo-500 animate-spin">
                            </div>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest hidden sm:block">
                        {{ $vehicles->count() }} Registered Fleets
                    </p>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="bg-white border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                            <tr>
                                <th scope="col" class="px-5 py-4">Vehicle Identification</th>
                                <th scope="col" class="px-5 py-4">Assigned Driver</th>
                                <th scope="col" class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($vehicles as $v)
                                <tr class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-9 w-9 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 ring-1 ring-inset ring-indigo-100">
                                                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 tracking-tight">
                                                    {{ $v->plate_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-6 w-6 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                                                <span
                                                    class="text-[10px] font-bold text-slate-600">{{ $v->driver_name ? substr($v->driver_name, 0, 1) : '-' }}</span>
                                            </div>
                                            <span
                                                class="font-medium text-slate-700">{{ $v->driver_name ?: 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <div
                                            class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('vehicles.edit', $v->id) }}"
                                                class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-indigo-600 transition"
                                                title="Edit Vehicle">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <button wire:click="delete({{ $v->id }})"
                                                wire:confirm="Are you sure you want to delete this vehicle?"
                                                class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition"
                                                title="Delete Vehicle">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center">
                                        <div
                                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 mb-3">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-slate-900">No vehicles found</h3>
                                        <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">Get started by
                                            registering a new vehicle to the fleet management system.</p>
                                        @if ($search)
                                            <button wire:click="$set('search', '')"
                                                class="mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-500">Clear
                                                search filter</button>
                                        @else
                                            <a href="{{ route('vehicles.create') }}"
                                                class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-500">Register
                                                First Vehicle</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Side Analytics/Info Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 rounded-full"></div>

                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 relative z-10">Total Active Fleet
                </h3>
                <p class="mt-2 text-4xl font-black text-slate-800 tracking-tighter relative z-10">
                    {{ $vehicles->count() }}</p>
                <p class="mt-1 text-xs text-slate-400 font-medium relative z-10">Monitored Units</p>

                <div class="mt-5 border-t border-slate-100 pt-4 relative z-10 text-left space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Operable
                        </span>
                        <span class="font-bold text-slate-800">{{ $vehicles->count() }}</span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl bg-gradient-to-b from-slate-800 to-slate-900 p-5 shadow-lg relative overflow-hidden text-white">
                <div class="absolute -right-4 top-4 text-white/5">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold tracking-wide flex items-center gap-2 relative z-10">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Pro Tip
                </h3>
                <p class="mt-2 text-xs text-slate-300 leading-relaxed font-medium relative z-10">
                    Ensure all vehicle plates are accurate to prevent issues when assigning fleet vehicles to new
                    Delivery Notes in the Operations domain.
                </p>
            </div>
        </div>

    </div>
</div>
