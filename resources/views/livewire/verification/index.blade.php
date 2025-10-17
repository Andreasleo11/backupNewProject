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
                @foreach (['all', 'DRAFT', 'IN_REVIEW', 'APPROVED', 'REJECTED'] as $st)
                    <input class="btn-check" type="radio" id="st-{{ $st }}" value="{{ $st }}"
                        wire:model.live="status">
                    <label class="btn btn-outline-secondary" for="st-{{ $st }}">{{ $st }}</label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Doc No</th>
                    <th>Customer</th>
                    <th>Invoice</th>
                    <th>Rec Date</th>
                    <th>Verify Date</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $r)
                    <tr>
                        <td>{{ $r->document_number }}</td>
                        <td>{{ $r->customer ?? '—' }}</td>
                        <td>{{ $r->invoice_number ?? '—' }}</td>
                        <td>{{ optional($r->rec_date)?->format('d M Y') ?? '—' }}</td>
                        <td>{{ optional($r->verify_date)?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end">
                            {{ number_format($r->total_value ?? 0, 2) }}
                        </td>
                        <td>
                            <span
                                class="badge text-bg-{{ [
                                    'DRAFT' => 'secondary',
                                    'IN_REVIEW' => 'warning',
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                ][$r->status] ?? 'secondary' }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('verification.show', $r->id) }}">
                                Open
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $reports->links() }}
</div>
