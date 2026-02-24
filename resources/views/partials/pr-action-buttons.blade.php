<div class="flex items-center gap-2">
    @if ($pr->is_cancel)
        {{-- Detail Button (Canceled State) --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm">
            <i class='bx bx-info-circle text-lg'></i>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">View Details</span>
        </a>

        {{-- Export PDF (Canceled State) --}}
        <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
           class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:border-emerald-600 hover:text-white transition-all shadow-sm">
            <i class='bx bxs-file-pdf text-lg'></i>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">Export PDF</span>
        </a>
    @else
        {{-- Quick View Button --}}
        <button type="button" 
                @click="$dispatch('open-quick-view-modal', { id: {{ $pr->id }} })"
                class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 border border-sky-100 text-sky-600 hover:bg-sky-500 hover:border-sky-600 hover:text-white transition-all shadow-sm">
            <i class='bx bx-search-alt text-lg'></i>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">Quick View</span>
        </button>

        {{-- Detail Button --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm">
            <i class='bx bx-info-circle text-lg'></i>
            <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">Full Details</span>
        </a>

        {{-- Delete Feature (Super Admin) --}}
        @if (auth()->user()->hasRole('super-admin'))
            <button type="button" 
                    @click="$dispatch('open-delete-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-500 hover:border-rose-600 hover:text-white transition-all shadow-sm">
                <i class='bx bx-trash-alt text-lg'></i>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">Delete PR</span>
            </button>
        @endif

        {{-- Cancel Feature --}}
        @if (auth()->user()->id === $pr->user_id_create || auth()->user()->hasRole('super-admin'))
            <button type="button" 
                    @click="$dispatch('open-cancel-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 border border-orange-100 text-orange-500 hover:bg-orange-500 hover:border-orange-600 hover:text-white transition-all shadow-sm">
                <i class='bx bx-x-circle text-lg'></i>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">Cancel PR</span>
            </button>
        @endif

        {{-- More Actions Dropdown (Alpine Headless - Fixed Position) --}}
        <div x-data="{ open: false, x: 0, y: 0 }">
            <button type="button" 
                    @click="open = !open; x = $event.clientX; y = $event.clientY;"
                    class="group relative flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all shadow-sm"
                    :class="{'bg-slate-200 border-slate-300': open}">
                <i class='bx bx-dots-vertical-rounded text-lg'></i>
                <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-50">More Actions</span>
            </button>
            
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
                <ul class="list-none m-0 p-1 font-sans text-sm">
                    <li>
                        <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors font-medium decoration-transparent">
                            <i class='bx bxs-file-pdf text-lg'></i> Export to PDF
                        </a>
                    </li>
                    @if ($pr->status === 4 && auth()->user()->hasRole('PURCHASER') || auth()->user()->hasRole('super-admin'))
                        <li class="my-1 border-t border-slate-100"></li>
                        <li>
                            <button type="button" 
                                    @click="$dispatch('open-edit-po-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}', po: '{{ $pr->po_number }}' }); open = false;"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-indigo-700 hover:bg-indigo-50 hover:text-indigo-800 transition-colors font-medium w-full text-left">
                                <i class='bx bx-edit text-lg'></i> Edit PO Number
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    @endif
</div>
