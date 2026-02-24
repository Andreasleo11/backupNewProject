<div x-data="{ 
        show: false, 
        id: null, 
        doc: '',
        open(e) {
            this.id = e.detail.id;
            this.doc = e.detail.doc;
            this.show = true;
            $nextTick(() => { document.getElementById('cancel-description').focus() });
        }
    }" 
    @open-cancel-pr-modal.window="open($event)"
    @keydown.escape.window="show = false"
    x-show="show" 
    class="relative z-[100]" 
    aria-labelledby="cancelPrModal" 
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
                 
                <form :action="`/purchase-requests/${id}/cancel`" method="post" class="m-0">
                    @csrf
                    @method('put')
                    
                    {{-- Premium Header --}}
                    <div class="bg-gradient-to-r from-orange-50 to-white border-b border-orange-100 px-5 py-4 flex items-center justify-between">
                        <h5 class="font-bold text-slate-800 flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                                <i class="bx bx-x-circle text-lg"></i>
                            </div>
                            Cancel Purchase Request
                        </h5>
                        <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>
                    
                    {{-- Body with custom form --}}
                    <div class="p-5 bg-slate-50 relative">
                        <div class="mb-2">
                            <label for="cancel-description" class="form-label text-sm font-bold text-slate-700 mb-1 block">Reason for Cancellation <span class="text-rose-500">*</span></label>
                            <p class="text-xs text-slate-500 mb-3">Please provide a clear reason why this PR (<span x-text="doc" class="font-bold text-slate-700"></span>) is being canceled.</p>
                            
                            <textarea name="description" id="cancel-description" cols="30" rows="4" 
                                class="w-full form-control border-slate-200 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm p-3 bg-white transition-colors placeholder:text-slate-400"
                                placeholder="Tell us why you cancel this purchase request..." required></textarea>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                        <button type="button" @click="show = false" class="bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 py-2 font-medium transition-colors">Close</button>
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-200 text-white border-0 rounded-lg text-sm px-4 py-2 font-medium transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="bx bx-check-circle"></i> Confirm Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
