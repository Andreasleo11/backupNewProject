{{--
    Purchase Request Approve Modal
    Alpine.js modal for approving a PR with optional remarks
    
    Props:
    - $pr: PurchaseRequest model
--}}

<div x-data="{ open: false }" x-show="open" x-cloak
     @open-approve-modal.window="open = true"
     x-init="$watch('open', value => {
        if (value) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
     })"
     class="fixed inset-0 z-[100] overflow-y-auto" 
     aria-labelledby="approve-modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"
         @click="open = false"></div>
    
    {{-- Modal Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
             @click.stop>
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white bg-opacity-20">
                            <i class='bx bx-check-circle text-2xl text-white'></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white" id="approve-modal-title">
                            Approve Purchase Request
                        </h3>
                    </div>
                    <button type="button" 
                            @click="open = false"
                            class="rounded-lg p-1 text-white hover:bg-white hover:bg-opacity-20">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>
            </div>
            
            {{-- Body --}}
            <form method="POST" action="{{ route('purchase-requests.approve', $pr->id) }}" class="p-6">
                @csrf
                
                <div class="mb-6 space-y-4">
                     {{-- Info Alert --}}
                     <div class="flex items-start gap-3 rounded-lg bg-emerald-50 p-4 border border-emerald-100">
                        <i class='bx bx-info-circle text-xl text-emerald-600 mt-0.5'></i>
                        <div class="text-sm text-emerald-800">
                            <p class="font-bold">You are about to approve this request.</p>
                            <p class="mt-1 opacity-90">
                                This will move the request to the next stage in the workflow. Your digital signature will be attached.
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="approve-remarks" class="block text-sm font-bold text-slate-700 mb-2">
                            Remarks / Notes <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <textarea 
                            id="approve-remarks"
                            name="remarks" 
                            rows="3"
                            placeholder="Add any internal notes or comments regarding this approval..."
                            class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-all"></textarea>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button"
                            @click="open = false"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-0.5 hover:shadow-emerald-300">
                        <i class='bx bx-check-double'></i>
                        <span>Confirm Approval</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
