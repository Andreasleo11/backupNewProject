<div class="modal fade" id="edit-purchase-request-po-number" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-xl rounded-2xl overflow-hidden text-start">
            <form id="edit-po-form" action="" method="post" class="m-0">
                @csrf
                @method('put')
                
                {{-- Premium Header --}}
                <div class="modal-header bg-gradient-to-r from-indigo-50 to-white border-b border-indigo-100 px-5 py-4">
                    <h5 class="modal-title font-bold text-slate-800 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <i class="bx bx-edit text-lg"></i>
                        </div>
                        Edit PO Number
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                {{-- Body with custom form --}}
                <div class="modal-body p-5 bg-slate-50 relative">
                    <div class="mb-2">
                        <label for="po_number_input" class="form-label text-sm font-bold text-slate-700 mb-1 block">PO Number for <span id="edit-po-doc-num" class="text-indigo-600"></span></label>
                        <p class="text-xs text-slate-500 mb-3">Update the Purchase Order number associated with this request.</p>
                        
                        <div class="relative">
                            <i class="bx bx-hash absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input id="po_number_input" type="text" name="po_number" value=""
                                class="w-full form-control border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 pl-10 pr-3 bg-white transition-colors placeholder:text-slate-400"
                                placeholder="e.g. PO-2026-001">
                        </div>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="modal-footer bg-white border-t border-slate-100 px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-secondary bg-slate-100 text-slate-600 hover:bg-slate-200 border-0 rounded-lg text-sm px-4 font-medium transition-colors" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 text-white border-0 rounded-lg text-sm px-4 font-medium transition-all flex items-center gap-1.5">
                        <i class="bx bx-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
