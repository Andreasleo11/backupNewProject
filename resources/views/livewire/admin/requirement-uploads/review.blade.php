{{-- resources/views/livewire/admin/requirement-uploads/review.blade.php --}}
<div class="container py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h5 mb-0">Requirement Uploads — Review</h1>
    <div class="d-flex gap-2">
      <input type="text" class="form-control" placeholder="Search..." wire:model.live="q" style="width:220px">
      <select class="form-select" wire:model="status" style="width:160px">
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="all">All</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Requirement</th>
          <th>File</th>
          <th>Status</th>
          <th>Validity</th>
          <th>Uploaded</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $u)
          <tr>
            <td>{{ $u->requirement->name }} <small class="text-muted">({{ $u->requirement->code }})</small></td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-semibold">{{ $u->original_name }}</span>
                <small class="text-muted">{{ $u->mime_type }} · {{ number_format($u->size/1024,1) }} KB</small>
              </div>
            </td>
            <td>
              @php $c = ['pending'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
              <span class="badge text-bg-{{ $c[$u->status] ?? 'secondary' }}">{{ ucfirst($u->status) }}</span>
            </td>
            <td>
              {{ $u->valid_from?->format('Y-m-d') ?? '—' }} → {{ $u->valid_until?->format('Y-m-d') ?? '—' }}
            </td>
            <td><small class="text-muted">{{ $u->created_at->format('Y-m-d H:i') }}</small></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary"
                 href="{{ URL::signedRoute('uploads.download', ['upload' => $u->id]) }}">
                Download
              </a>
              @can('approve-requirements')
                @if($u->status !== 'approved')
                  <button class="btn btn-sm btn-success"
                          wire:click="$set('uploadId', {{ $u->id }})"
                          data-bs-toggle="modal" data-bs-target="#decisionModal">
                    Approve/Reject
                  </button>
                @endif
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nothing here.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $rows->links() }}

  {{-- Decision Modal --}}
  <div wire:ignore.self class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Approval Decision</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Notes (optional)</label>
          <textarea class="form-control" rows="3" wire:model.defer="review_notes"></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          @if($uploadId)
            <button class="btn btn-danger" wire:click="reject({{ $uploadId }})" data-bs-dismiss="modal">Reject</button>
            <button class="btn btn-success" wire:click="approve({{ $uploadId }})" data-bs-dismiss="modal">Approve</button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
