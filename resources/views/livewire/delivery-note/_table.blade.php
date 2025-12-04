<table class="min-w-full divide-y divide-slate-200 text-sm">
    <thead class="bg-slate-50">
        <tr>
            {{-- ID --}}
            <th scope="col"
                wire:click="sortBy('id')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>#</span>
                    @if ($sortField === 'id')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                {{-- Chevron up --}}
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                {{-- Chevron down --}}
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 001.04 0l4.23-4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Branch --}}
            <th scope="col"
                wire:click="sortBy('branch')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>Branch</span>
                    @if ($sortField === 'branch')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 001.04 0l4.23-4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Ritasi --}}
            <th scope="col"
                wire:click="sortBy('ritasi')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>Ritasi</span>
                    @if ($sortField === 'ritasi')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 001.04 0l4.23-4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Date --}}
            <th scope="col"
                wire:click="sortBy('delivery_note_date')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>Date</span>
                    @if ($sortField === 'delivery_note_date')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Vehicle --}}
            <th scope="col"
                wire:click="sortBy('plate_number')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>Vehicle</span>
                    @if ($sortField === 'plate_number')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Driver --}}
            <th scope="col"
                wire:click="sortBy('driver_name')"
                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 cursor-pointer select-none">
                <div class="inline-flex items-center gap-1">
                    <span>Driver</span>
                    @if ($sortField === 'driver_name')
                        <span class="inline-block">
                            @if ($sortDirection === 'asc')
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 12.79a.75.75 0 001.06.02L10 9.293l3.71 3.52a.75.75 0 101.04-1.08l-4.23-4a.75.75 0 00-1.04 0l-4.23 4a.75.75 0 00-.02 1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M14.77 7.21a.75.75 0 00-1.06-.02L10 10.707 6.29 7.19a.75.75 0 10-1.04 1.08l4.23 4a.75.75 0 00.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    @endif
                </div>
            </th>

            {{-- Status --}}
            <th scope="col"
                class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                Status
            </th>

            {{-- Actions --}}
            <th scope="col"
                class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-40">
                Actions
            </th>
        </tr>
    </thead>

    <tbody class="divide-y divide-slate-100 bg-white">
        @forelse ($deliveryNotes as $note)
            <tr class="hover:bg-slate-50">
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->id }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->branch }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->ritasi_label }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->formatted_delivery_note_date }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->vehicle->plate_number ?? '-' }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-slate-700">
                    {{ $note->vehicle->driver_name ?? '-' }}
                </td>
                <td class="px-3 py-2 text-center whitespace-nowrap">
                    @php
                        $isDraft = $note->status === 'draft';
                    @endphp
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                               {{ $isDraft
                                   ? 'bg-amber-50 text-amber-800 border border-amber-200'
                                   : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                        {{ ucfirst($note->status) }}
                    </span>
                </td>
                <td class="px-3 py-2 text-center whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                        @php
                            $isGuest  = !auth()->check();
                            $isLatest = $note->latest ?? false;
                        @endphp

                        {{-- View --}}
                        <a href="{{ route('delivery-notes.show', $note->id) }}"
                           class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700
                                  hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/60">
                            View
                        </a>

                        @if (!$isGuest && auth()->check())
                            {{-- Authenticated: edit + delete --}}
                            <a href="{{ route('delivery-notes.edit', $note->id) }}"
                               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700
                                      hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/60">
                                Edit
                            </a>

                            <button
                                type="button"
                                x-data
                                @click.prevent="if (confirm('Delete this delivery note?')) { $wire.delete({{ $note->id }}) }"
                                class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700
                                       hover:bg-rose-100 hover:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-500/60">
                                Delete
                            </button>
                        @elseif ($isGuest && $isLatest)
                            {{-- Guest + latest note: hanya boleh edit --}}
                            <a href="{{ route('delivery-notes.edit', $note->id) }}"
                               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700
                                      hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/60">
                                Edit
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500">
                    No delivery notes found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
