<div class="modal fade" id="delete-pr-modal" tabindex="-1" role="dialog" aria-labelledby="deletePrModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-gradient-to-r from-rose-50 to-white border-b border-rose-100 px-5 py-4">
                <h5 class="modal-title font-bold text-slate-800 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <i class="bx bx-trash text-lg"></i>
                    </div>
                    Delete Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-5 bg-slate-50 relative">
                <p class="text-slate-600 text-sm leading-relaxed">
                    Are you sure you want to permanently delete Purchase Request <br>
                    <span id="delete-doc-num" class="font-bold text-slate-800 text-base inline-block mt-2 px-3 py-1 bg-white border border-slate-200 rounded-lg shadow-sm"></span> ?
                </p>
                <p class="text-xs text-rose-500 font-medium mt-3 flex items-center gap-1">
                    <i class="bx bx-error-circle"></i> This action cannot be undone.
                </p>
            </div>
            
            <div class="modal-footer bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                <button type="button" class="btn btn-secondary bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 font-medium transition-colors" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-pr-form" action="" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger bg-rose-500 hover:bg-rose-600 hover:shadow-lg hover:shadow-rose-200 border-0 rounded-lg text-sm px-4 font-medium transition-all flex items-center gap-1.5">
                        <i class="bx bx-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
