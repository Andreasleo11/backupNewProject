<div x-data="{ 
    show: false,
    id: null,
    doc: '',
    currentPo: '',
    open(e) {
        this.id = e.detail.id;
        this.doc = e.detail.doc;
        this.currentPo = e.detail.po;
        this.show = true;
        
        $nextTick(() => {
            const input = document.getElementById('po_number_input');
            if (input) {
                input.focus();
                input.select();
            }
        });
    }
}" 
    x-effect="document.body.style.overflow = show ? 'hidden' : ''"
    @open-edit-po-modal.window="open($event)" 
    @close-edit-po-modal.window="show = false" 
    @keydown.escape.window="show = false" 
    class="relative z-[100]"
    aria-labelledby="editPoModal" 
    role="dialog" 
    aria-modal="true" 
    x-cloak>

    <template x-teleport="body">
        <div x-show="show" class="relative z-[100]">
            {{-- Backdrop --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="show" @click.away="show = false" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">

                        <form class="m-0" wire:submit.prevent>
                            {{-- Premium Header --}}
                            <div
                                class="bg-gradient-to-r from-indigo-50 to-white border-b border-indigo-100 px-5 py-4 flex items-center justify-between">
                                <h5 class="font-bold text-slate-800 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                        <i class="bx bx-edit text-lg"></i>
                                    </div>
                                    Edit PO Number
                                </h5>
                                <button type="button" @click="show = false"
                                    class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>

                            {{-- Body with custom form --}}
                            <div class="p-5 bg-slate-50 relative">
                                <div class="mb-2">
                                    <label for="po_number_input"
                                        class="form-label text-sm font-bold text-slate-700 mb-1 block">PO Number for <span
                                            class="text-indigo-600" x-text="doc"></span></label>
                                    <p class="text-xs text-slate-500 mb-3">Update the Purchase Order number associated with this
                                        request.</p>

                                    <div class="relative">
                                        <i class="bx bx-hash absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                                        <input id="po_number_input" type="text" x-model="currentPo"
                                            class="w-full border border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 pl-10 pr-3 bg-white transition-colors placeholder:text-slate-400"
                                            placeholder="e.g. PO-2026-001">
                                        @error('editingPoNumber')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div
                                class="bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                                <button type="button" @click="show = false"
                                    class="bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 py-2 font-medium transition-colors">Close</button>
                                <button @click="$wire.updatePoNumber(id, currentPo)" 
                                    type="button" 
                                    wire:loading.attr="disabled"
                                    wire:target="updatePoNumber"
                                    class="bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 text-white border-0 rounded-lg text-sm px-4 py-2 font-medium transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="bx bx-save" wire:loading.class="animate-spin" wire:target="updatePoNumber"></i>
                                    <span wire:loading.remove wire:target="updatePoNumber">Save Changes</span>
                                    <span wire:loading wire:target="updatePoNumber">Saving...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
