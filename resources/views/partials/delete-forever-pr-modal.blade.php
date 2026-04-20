<div x-data="{
    show: false,
    id: null,
    doc: '',
    open(e) {
        this.id = e.detail.id;
        this.doc = e.detail.doc;
        this.show = true;
    }
}" x-effect="document.body.style.overflow = show ? 'hidden' : ''"
    @open-delete-forever-pr-modal.window="open($event)" 
    @close-delete-forever-pr-modal.window="show = false"
    @keydown.escape.window="show = false" 
    x-show="show"
    class="relative z-[100]" 
    aria-labelledby="deleteForeverPrModal" 
    role="dialog" 
    aria-modal="true" 
    x-cloak>

    <template x-teleport="body">
        <div x-show="show" class="relative z-[100]">
            {{-- Backdrop --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="show" @click.away="show = false" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border-4 border-rose-100">

                        <div
                            class="bg-gradient-to-r from-rose-600 to-rose-700 px-5 py-6 flex items-center justify-between text-white">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm shadow-inner">
                                    <i class="bx bxs-skull text-2xl"></i>
                                </div>
                                <div>
                                    <h5 class="font-black uppercase tracking-widest text-sm">Critical Danger</h5>
                                    <p class="text-[10px] text-rose-100 font-bold uppercase tracking-tighter">Permanent Hard Deletion</p>
                                </div>
                            </div>
                            <button type="button" @click="show = false"
                                class="text-white/60 hover:text-white transition-colors">
                                <i class="bx bx-x text-3xl"></i>
                            </button>
                        </div>

                        <div class="p-6 bg-white relative">
                            <div class="flex flex-col items-center text-center mb-6">
                                <div class="h-16 w-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 mb-4 ring-8 ring-rose-50/50">
                                    <i class="bx bx-trash text-4xl animate-bounce"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-800">Destroy Record Permanently?</h3>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                    This action is <span class="text-rose-600 font-black underline">irreversible</span>. 
                                    Purchase Request <span x-text="doc" class="font-black text-rose-600"></span> and all its associated items will be purged from the database forever.
                                </p>
                            </div>

                            <div class="bg-rose-50 rounded-xl p-4 border border-rose-100 mb-6">
                                <p class="text-[10px] font-black text-rose-700 uppercase tracking-widest flex items-center gap-2 mb-1">
                                    <i class="bx bx-error"></i> Warning
                                </p>
                                <p class="text-[11px] text-rose-600 font-medium italic">
                                    "Once confirmed, there is no technical way to recover this data. Please ensure you have authorization for this purge."
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-slate-50 border-t border-slate-100 px-6 py-4 rounded-b-2xl flex items-center gap-3">
                            <button type="button" @click="show = false"
                                class="flex-1 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest py-3 transition-colors shadow-sm">
                                Abort Deletion
                            </button>
                            <button type="button" @click="$wire.deleteForeverPurchaseRequest(id)"
                                wire:loading.attr="disabled"
                                wire:target="deleteForeverPurchaseRequest"
                                class="flex-1 bg-rose-600 hover:bg-rose-700 hover:shadow-xl hover:shadow-rose-300/50 text-white border-0 rounded-xl text-[10px] font-black uppercase tracking-widest py-3 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                                <i class="bx bx-trash" wire:loading.class="bx-spin" wire:target="deleteForeverPurchaseRequest"></i>
                                <span wire:loading.remove wire:target="deleteForeverPurchaseRequest">Confirm Purge</span>
                                <span wire:loading wire:target="deleteForeverPurchaseRequest">Purging...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
