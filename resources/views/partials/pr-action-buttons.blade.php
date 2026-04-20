<div class="flex items-center justify-center gap-1">
    {{-- PRIMARY ACTION: Quick View --}}
    @if (!$pr->is_cancel)
        <button type="button" title="Quick View" wire:click.prefetch="openQuickView({{ $pr->id }})"
            @click="$dispatch('open-quick-view-modal', { id: {{ $pr->id }} })"
            class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all shadow-sm">
            <i class='bx bx-search-alt text-lg group-hover:scale-110 transition-transform'></i>
        </button>
    @else
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" title="View Details"
            class="group flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 transition-all shadow-sm">
            <i class='bx bx-info-circle text-lg'></i>
        </a>
    @endif

    {{-- SECONDARY ACTIONS: Command Menu --}}
    <div x-data="{ open: false, x: 0, y: 0 }">
        <button type="button" @click="open = !open; x = $event.clientX; y = $event.clientY;"
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm"
            :class="{ 'bg-slate-100 border-slate-300 text-slate-900 ring-4 ring-slate-100': open }">
            <i class='bx bx-dots-vertical-rounded text-lg'></i>
        </button>

        <template x-teleport="body">
            <div x-show="open" @click.outside="open = false" @scroll.window="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="fixed w-48 rounded-xl bg-white shadow-xl shadow-indigo-200/40 border border-slate-100 py-1.5 z-[9999]"
                :style="`top: ${y}px; left: ${x}px; transform: translate(-100%, 10px);`" x-cloak>

                {{-- Global Actions --}}
                <div class="px-2 pb-1 mb-1 border-bottom border-slate-50">
                    <span
                        class="text-[9px] font-bold uppercase tracking-widest text-slate-400 px-2 leading-loose">General</span>
                </div>

                <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}"
                    class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    <i class='bx bx-info-circle text-lg opacity-80'></i> Full Detail View
                </a>

                <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}"
                    class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <i class='bx bxs-file-pdf text-lg opacity-80'></i> Export to PDF
                </a>

                {{-- Administrative --}}
                @can('updatePo', $pr)
                   <button 
                        type="button"
                        @click="$dispatch('open-edit-po-modal', { 
                            id: {{ $pr->id }}, 
                            doc: 'PR-{{ $pr->doc_num }}',
                            po: '{{ addslashes($pr->po_number) }}'
                        })"
                        class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 transition-colors w-full text-left">
                        <i class='bx bx-edit text-lg opacity-80'></i> 
                        Edit PO Number
                    </button>
                @endcan

                {{-- Danger Zone --}}
                @if (!$pr->is_cancel)
                    @if (Gate::any(['cancel', 'delete', 'forceDelete'], $pr))
                        <div class="h-px bg-slate-50 my-1"></div>
                        <div class="px-2 pb-1 mt-1">
                            <span
                                class="text-[9px] font-bold uppercase tracking-widest text-slate-400 px-2 leading-loose">Danger
                                Zone</span>
                        </div>

                        @can('cancel', $pr)
                            <button type="button"
                                @click="$dispatch('open-cancel-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' }); open = false;"
                                class="flex items-center gap-2.5 px-3 py-2 text-sm text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors w-full text-left font-medium">
                                <i class='bx bx-x-circle text-lg opacity-80'></i> Cancel Request
                            </button>
                        @endcan

                        @can('delete', $pr)
                            <button type="button"
                                @click="$dispatch('open-delete-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' }); open = false;"
                                class="flex items-center gap-2.5 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors w-full text-left font-medium">
                                <i class='bx bx-trash text-lg opacity-80'></i> Move to Trash
                            </button>
                        @endcan

                        @can('forceDelete', $pr)
                            <button type="button"
                                @click="$dispatch('open-delete-forever-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' }); open = false;"
                                class="flex items-center gap-2.5 px-3 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900 transition-colors w-full text-left font-black">
                                <i class='bx bxs-trash-alt text-lg opacity-80'></i> Purge Forever
                            </button>
                        @endcan
                    @endif
                @endif
            </div>
        </template>
    </div>
</div>
