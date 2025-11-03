<div x-data
     x-init="
      const modalEl = $refs.modal;
      let modal;

      const show = () => { modal = modal ?? new bootstrap.Modal(modalEl); modal.show(); };
      const hide = () => { if (!modal) return; modal.hide(); };

      window.addEventListener('show-upload-modal', show);
      window.addEventListener('hide-upload-modal', hide);
     ">

    <div class="modal fade" x-ref="modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Requirement #{{$requirementId}} for dept {{$department?->name}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($requirementId)
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" class="form-control" wire:model="file">
                            @error('file')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" class="form-control" wire:model="valid_from">
                        </div>

                        <div wire:loading wire:target="file" class="text-muted small">Uploading…</div>
                    @else
                        <div class="text-muted">Choose a requirement from the table first.</div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$dispatch('hide-upload-modal')">Close</button>
                    <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
