<div class="flex items-center gap-2">
    @if ($pr->is_cancel)
        {{-- Detail Button (Canceled State) --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm"
           data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
            <i class='bx bx-info-circle text-lg'></i>
        </a>

        {{-- Export PDF (Canceled State) --}}
        <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:border-emerald-600 hover:text-white transition-all shadow-sm"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Export PDF">
            <i class='bx bxs-file-pdf text-lg'></i>
        </a>
    @else
        {{-- Quick View Button --}}
        <button type="button" 
                @click="$dispatch('open-quick-view-modal', { id: {{ $pr->id }} })"
                class="group flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 border border-sky-100 text-sky-600 hover:bg-sky-500 hover:border-sky-600 hover:text-white transition-all shadow-sm" 
                data-bs-toggle="tooltip" data-bs-placement="top" title="Quick View">
            <i class='bx bx-search-alt text-lg'></i>
        </button>

        {{-- Detail Button --}}
        <a href="{{ route('purchase-requests.show', ['id' => $pr->id]) }}" 
           class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all shadow-sm"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Full Details">
            <i class='bx bx-info-circle text-lg'></i>
        </a>

        {{-- Delete Feature (Super Admin) --}}
        @if (auth()->user()->hasRole('super-admin'))
            <button type="button" 
                    @click="$dispatch('open-delete-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-500 hover:border-rose-600 hover:text-white transition-all shadow-sm" 
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete PR">
                <i class='bx bx-trash-alt text-lg'></i>
            </button>
        @endif

        {{-- Cancel Feature --}}
        @if (($user->id === $pr->user_id_create && $pr->status === 1) || ($user->department?->name === 'COMPUTER' && $user->is_head && $pr->status === 4) || auth()->user()->hasRole('super-admin'))
            <button type="button" 
                    @click="$dispatch('open-cancel-pr-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}' })"
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 border border-orange-100 text-orange-500 hover:bg-orange-500 hover:border-orange-600 hover:text-white transition-all shadow-sm"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel PR">
                <i class='bx bx-x-circle text-lg'></i>
            </button>
        @endif

        {{-- More Actions Dropdown --}}
        <div class="dropdown">
            <button type="button" 
                    class="group flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all shadow-sm"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="More Actions">
                <i class='bx bx-dots-vertical-rounded text-lg'></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl overflow-hidden mt-2 p-1 font-sans text-sm min-w-[160px]">
                <li>
                    <a href="{{ route('purchase-requests.export-pdf', $pr->id) }}" 
                       class="dropdown-item flex items-center gap-2 px-3 py-2 rounded-lg text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors font-medium">
                        <i class='bx bxs-file-pdf text-lg'></i> Export to PDF
                    </a>
                </li>
                @if ($pr->status === 4 && $user->specification->name === 'PURCHASER')
                <li><hr class="dropdown-divider my-1 border-slate-100"></li>
                <li>
                    <button type="button" 
                            @click="$dispatch('open-edit-po-modal', { id: {{ $pr->id }}, doc: '{{ $pr->doc_num }}', po: '{{ $pr->po_number }}' })"
                            class="dropdown-item flex items-center gap-2 px-3 py-2 rounded-lg text-indigo-700 hover:bg-indigo-50 hover:text-indigo-800 transition-colors font-medium w-full text-left">
                        <i class='bx bx-edit text-lg'></i> Edit PO Number
                    </button>
                </li>
                @endif
            </ul>
        </div>
    @endif
</div>

{{-- Initialize tooltips --}}
<script>
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
