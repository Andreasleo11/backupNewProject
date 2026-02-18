<div x-data="{ open: false, reason: '' }"
     @open-return-modal.window="open = true"
     class="relative z-[100]"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     style="display: none;"
     x-show="open">
    
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 x-show="open"
                 @click.outside="open = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form action="{{ route('purchase-requests.return', $pr->id) }}" method="POST">
                    @csrf
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-arrow-counterclockwise text-orange-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Return for Revision?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">
                                        Are you sure you want to return this request to the creator? The approval process will be <strong>restarted from the beginning</strong>.
                                    </p>
                                    
                                    <div class="mt-4">
                                        <label for="return_reason" class="block text-xs font-bold uppercase text-slate-500 mb-1">Reason for Return <span class="text-rose-500">*</span></label>
                                        <textarea name="reason" id="return_reason" rows="3" required x-model="reason"
                                                  class="w-full rounded-xl border-slate-200 text-sm focus:border-orange-500 focus:ring-orange-500"
                                                  placeholder="Please explain what needs to be revised..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" 
                                :disabled="!reason.trim()"
                                class="inline-flex w-full justify-center rounded-xl bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto">
                            Confirm Return
                        </button>
                        <button type="button" @click="open = false" 
                                class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
