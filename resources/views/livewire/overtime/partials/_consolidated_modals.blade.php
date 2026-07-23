{{-- ===== CONSOLIDATED VIEW MODALS ===== --}}
@if($showRejectModal)
    <template x-teleport="body">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
            x-data
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 mx-4 relative overflow-hidden"
                @click.outside="$wire.set('showRejectModal', false)"
                x-transition:enter="transition ease-out duration-300 delay-75"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                
                {{-- Decorative bg --}}
                <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-rose-50 blur-3xl"></div>

                <div class="relative">
                    <div class="h-12 w-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-5">
                        <x-bx-x class="w-6 h-6" />
                    </div>

                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Reject Overtime Form</h3>
                    <p class="text-sm text-slate-500 mt-1.5 font-medium leading-relaxed">
                        Please provide a reason for rejection. This will be recorded and visible to the requestor.
                    </p>

                    <div class="mt-6">
                        <textarea wire:model="rejectReason"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-rose-300 focus:bg-white focus:ring-4 focus:ring-rose-50 transition-all resize-none"
                            rows="4"
                            placeholder="Reason for rejection..."></textarea>

                        @error('rejectReason')
                            <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-2 flex items-center gap-1">
                                <x-bx-error-circle class="" /> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <button type="button" wire:click="$set('showRejectModal', false)"
                            class="px-5 py-2.5 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:bg-slate-100 transition-all">
                            Cancel
                        </button>
                        <button type="button" wire:click="submitReject"
                            wire:loading.attr="disabled"
                            wire:target="submitReject"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-rose-200 hover:bg-rose-700 hover:-translate-y-0.5 transition-all flex items-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitReject">
                                Reject Form
                            </span>
                            <span wire:loading wire:target="submitReject">
                                <x-bx-loader-alt class="animate-spin w-4 h-4" />
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endif
