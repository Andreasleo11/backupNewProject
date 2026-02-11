<div x-data="{ 
        show: false, 
        id: null, 
        doc: '',
        open(e) {
            this.id = e.detail.id;
            this.doc = e.detail.doc;
            this.show = true;
        }
    }" 
    x-effect="document.body.style.overflow = show ? 'hidden' : ''"
    @open-delete-pr-modal.window="open($event)"
    @keydown.escape.window="show = false"
    x-show="show" 
    class="relative z-[100]" 
    aria-labelledby="deletePrModal" 
    role="dialog" 
    aria-modal="true"
    x-cloak>
    
    {{-- Backdrop --}}
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
         
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show"
                 @click.away="show = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                
                <div class="bg-gradient-to-r from-rose-50 to-white border-b border-rose-100 px-5 py-4 flex items-center justify-between">
                    <h5 class="font-bold text-slate-800 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                            <i class="bx bx-trash text-lg"></i>
                        </div>
                        Delete Confirmation
                    </h5>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
                
                <div class="p-5 bg-slate-50 relative">
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Are you sure you want to permanently delete Purchase Request <br>
                        <span x-text="doc" class="font-bold text-slate-800 text-base inline-block mt-2 px-3 py-1 bg-white border border-slate-200 rounded-lg shadow-sm"></span> ?
                    </p>
                    <p class="text-xs text-rose-500 font-medium mt-3 flex items-center gap-1">
                        <i class="bx bx-error-circle"></i> This action cannot be undone.
                    </p>
                </div>
                
                <div class="bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                    <button type="button" @click="show = false" class="bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 py-2 font-medium transition-colors">Cancel</button>
                    <form :action="`/purchase-requests/${id}`" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 hover:shadow-lg hover:shadow-rose-200 text-white border-0 rounded-lg text-sm px-4 py-2 font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="bx bx-trash"></i> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
