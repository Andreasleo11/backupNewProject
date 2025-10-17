{{-- resources/views/livewire/signature/manage-signatures.blade.php --}}
<div class="container py-4" wire:ignore.self>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="d-flex align-items-center gap-2">
      <h1 class="h5 mb-0">My Signatures</h1>
      <span class="badge bg-dark text-light">{{ count($items) }} total</span>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('signatures.capture') }}" class="btn btn-primary">
        <i class="bi bi-pen"></i> Capture New
      </a>
    </div>
  </div>

  @if (empty($items))
    <div class="alert alert-info d-flex align-items-center" role="alert">
      <i class="bi bi-info-circle me-2"></i>
      <div>No signatures yet. Click <strong>Capture New</strong> to add one.</div>
    </div>
  @else
    <div class="row g-3">
      @foreach ($items as $sig)
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="small text-muted">Label</div>
                @if ($sig['is_default'])
                  <span class="badge text-bg-success">Default</span>
                @endif
              </div>

              <div class="fw-semibold mb-2">{{ $sig['label'] ?? '—' }}</div>
              <div class="border rounded bg-white d-flex justify-content-center align-items-center mb-3" style="height:150px; overflow:hidden;">
                <img src="{{ $sig['url'] }}" alt="Signature" style="max-height:130px; max-width:100%; object-fit:contain;"/>
              </div>

              <div class="mt-auto d-flex flex-wrap gap-2">
                @unless ($sig['is_default'])
                  <button wire:click="setDefault({{ $sig['id'] }})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-star"></i> Set Default
                  </button>
                @endunless
                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#revokeModal-{{ $sig['id'] }}">
                  <i class="bi bi-x-circle"></i> Revoke
                </button>
                <a href="{{ $sig['url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-box-arrow-up-right"></i> Open
                </a>
              </div>
            </div>
          </div>
        </div>

        {{-- Revoke Modal --}}
        <div class="modal fade" id="revokeModal-{{ $sig['id'] }}" tabindex="-1" aria-labelledby="revokeModalLabel-{{ $sig['id'] }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="revokeModalLabel-{{ $sig['id'] }}">Revoke signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p class="mb-1">You are about to revoke this signature:</p>
                <div class="small text-muted">Label: <strong>{{ $sig['label'] ?? '—' }}</strong></div>
                <div class="small text-muted">ID: <code>{{ $sig['id'] }}</code></div>
                <div class="alert alert-warning mt-3 mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Revoked signatures cannot be used anymore.</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" wire:click="revoke({{ $sig['id'] }})" data-bs-dismiss="modal">Revoke</button>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  @once
    @push('styles')
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
      <script>
        // Optional: Listen for Livewire toast events and show a simple Bootstrap toast
        window.addEventListener('toast', (e) => {
          const msg = e.detail?.message || 'Done';
          if (!window.bootstrap) { alert(msg); return; }

          let el = document.getElementById('lw-toast');
          if (!el) {
            el = document.createElement('div');
            el.id = 'lw-toast';
            el.className = 'toast align-items-center text-bg-dark border-0 position-fixed bottom-0 end-0 m-3';
            el.setAttribute('role', 'status');
            el.setAttribute('aria-live', 'polite');
            el.setAttribute('aria-atomic', 'true');
            el.innerHTML = `<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
            document.body.appendChild(el);
          }
          el.querySelector('.toast-body').textContent = msg;
          const toast = new bootstrap.Toast(el, { delay: 2500 });
          toast.show();
        });
      </script>
    @endpush
  @endonce
</div>
