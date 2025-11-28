
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Verification Reports</h1>
    <a class="btn btn-primary" href="{{ route('verification.create') }}">New</a>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-md-3">
      <input type="text" class="form-control" placeholder="Search…" wire:model.live.debounce.300ms="search">
    </div>
    <div class="col">
      <div class="btn-group" role="group" aria-label="Status">
        @foreach (['all','DRAFT','IN_REVIEW','APPROVED','REJECTED'] as $st)
          <input class="btn-check" type="radio" id="st-{{ $st }}" value="{{ $st }}" wire:model.live="status">
          <label class="btn btn-outline-secondary" for="st-{{ $st }}">{{ $st }}</label>
        @endforeach
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>#</th><th>Doc No</th><th>Title</th><th>Status</th><th>Created</th><th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($reports as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->document_number }}</td>
          <td>{{ $r->title }}</td>
          <td><span class="badge text-bg-light">{{ $r->status }}</span></td>
          <td>{{ $r->created_at->format('d M Y H:i') }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('verification.show', $r->id) }}">Open</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  {{ $reports->links() }}
</div>
