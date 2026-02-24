<div class="modal fade" id="cancel-confirmation-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-xl rounded-2xl overflow-hidden text-start">
            <form id="cancel-pr-form" action="" method="post" class="m-0">
                @csrf
                @method('put')
                
                {{-- Premium Header --}}
                <div class="modal-header bg-gradient-to-r from-orange-50 to-white border-b border-orange-100 px-5 py-4">
                    <h5 class="modal-title font-bold text-slate-800 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                            <i class="bx bx-x-circle text-lg"></i>
                        </div>
                        Cancel Purchase Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                {{-- Body with custom form --}}
                <div class="modal-body p-5 bg-slate-50 relative" id="modalBody">
                    <div class="mb-2">
                        <label for="cancel-description" class="form-label text-sm font-bold text-slate-700 mb-1 block">Reason for Cancellation <span class="text-rose-500">*</span></label>
                        <p class="text-xs text-slate-500 mb-3">Please provide a clear reason why this PR (<span id="cancel-doc-num" class="font-bold text-slate-700"></span>) is being canceled.</p>
                        
                        <textarea name="description" id="cancel-description" cols="30" rows="4" 
                            class="w-full form-control border-slate-200 rounded-xl shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm p-3 bg-white transition-colors placeholder:text-slate-400"
                            placeholder="Tell us why you cancel this purchase request..." required></textarea>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="modal-footer bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-secondary bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 font-medium transition-colors" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning bg-orange-500 hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-200 text-white border-0 rounded-lg text-sm px-4 font-medium transition-all flex items-center gap-1.5">
                        <i class="bx bx-check-circle"></i> Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
