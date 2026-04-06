<div class="flex items-center gap-2">
    @if ($pr->is_cancel)
        {{-- Detail Button (Canceled State) --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           title="View Details"
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm">
            <i class='bx bx-info-circle text-lg'></i>
        </a>

        {{-- Export PDF (Canceled State) --}}
        <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
           title="Export PDF"
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:border-emerald-600 hover:text-white transition-all shadow-sm">
            <i class='bx bxs-file-pdf text-lg'></i>
        </a>
    @else
        {{-- Quick View Button --}}
        <button type="button" 
                title="Quick View"
                @click="$dispatch('open-quick-view-modal', { id: {{ $pr->id }} })"
                class="group flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 border border-sky-100 text-sky-600 hover:bg-sky-500 hover:border-sky-600 hover:text-white transition-all shadow-sm">
            <i class='bx bx-search-alt text-lg'></i>
        </button>

        {{-- Detail Button --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           title="Full Details"
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm">
            <i class='bx bx-info-circle text-lg'></i>
        </a>

        {{-- Delete Feature --}}
        @can('delete', $pr)
            <button type="button" 
                    title="Delete PR"
                    @click="$dispatch('open-delete-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-500 hover:border-rose-600 hover:text-white transition-all shadow-sm">
                <i class='bx bx-trash-alt text-lg'></i>
            </button>
        @endcan

        {{-- Cancel Feature --}}
        @can('cancel', $pr)
            <button type="button" 
                    title="Cancel PR"
                    @click="$dispatch('open-cancel-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 border border-orange-100 text-orange-500 hover:bg-orange-500 hover:border-orange-600 hover:text-white transition-all shadow-sm">
                <i class='bx bx-x-circle text-lg'></i>
            </button>
        @endcan

        {{-- More Actions Dropdown (Alpine Headless - Fixed Position) --}}
        <div x-data="{ open: false, x: 0, y: 0 }">
            <button type="button" 
                    title="More Actions"
                    @click="open = !open; x = $event.clientX; y = $event.clientY;"
                    @scroll.window="open = false"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all shadow-sm"
                    :class="{'bg-slate-200 border-slate-300': open}">
                <i class='bx bx-dots-vertical-rounded text-lg'></i>
            </button>
            
            <template x-teleport="body">
                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="fixed w-48 rounded-xl bg-white shadow-lg shadow-indigo-100 border border-slate-100 py-1 z-[9999]"
                     :style="`top: ${y}px; left: ${x}px; transform: translate(-100%, 10px);`"
                     x-cloak>
                    <ul class="list-none m-0 p-1 font-sans text-sm shadow-sm overflow-hidden">
                        <li>
                            <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors font-medium decoration-transparent">
                                <i class='bx bxs-file-pdf text-lg'></i> Export to PDF
                            </a>
                        </li>
                        @can('update', $pr)
                            <li class="my-1 border-t border-slate-100"></li>
                            <li>
                                <button type="button" 
                                        @click="$dispatch('open-edit-po-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}', po: '{{ $pr->po_number }}' }); open = false;"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-indigo-700 hover:bg-indigo-50 hover:text-indigo-800 transition-colors font-medium w-full text-left">
                                    <i class='bx bx-edit text-lg'></i> Edit PO Number
                                </button>
                            </li>
                        @endcan
                    </ul>
                </div>
            </template>
        </div>
    @endif
</div>
