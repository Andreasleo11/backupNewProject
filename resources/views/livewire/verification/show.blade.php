<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h5 mb-0">Verification Report</h1>
            <div class="small text-muted">
                Doc#: <span class="fw-semibold">{{ $report->document_number }}</span>
                <span class="mx-2">•</span>
                Created {{ $report->created_at->format('d M Y H:i') }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span
                class="badge text-bg-{{ [
                    'DRAFT' => 'secondary',
                    'IN_REVIEW' => 'warning',
                    'APPROVED' => 'success',
                    'REJECTED' => 'danger',
                ][$report->status] ?? 'secondary' }}">
                {{ $report->status }}
            </span>

            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            @can('update', $report)
                @if ($report->status === 'DRAFT')
                    <a href="{{ route('verification.edit', $report->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                @endif
            @endcan
        </div>
    </div>

    {{-- Top cards --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="small text-muted">Receive Date</div>
                            <div>{{ optional($report->rec_date)?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Verify Date</div>
                            <div>{{ optional($report->verify_date)?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Customer</div>
                            <div>{{ $report->customer ?: '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Invoice #</div>
                            <div>{{ $report->invoice_number ?: '—' }}</div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="small text-muted mb-1">Meta</div>
                    <div class="d-flex flex-wrap gap-3">
                        <div><span class="text-muted">Department:</span>
                            {{ data_get($report->meta, 'department', '—') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions (submit/approve/reject) --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Actions</div>

                    {{-- Submit (creator) --}}
                    @can('update', $report)
                        @if ($report->status === 'DRAFT')
                            <div class="mb-3">
                                <label class="form-label">Remarks (optional)</label>
                                <textarea rows="2" class="form-control" wire:model.live.defer="remarks" placeholder="Any notes to approvers"></textarea>
                            </div>
                            <button class="btn btn-primary w-100" wire:click="submit">
                                <i class="bi bi-send"></i> Submit for Approval
                            </button>
                        @endif
                    @endcan

                    {{-- Approve / Reject (approvers) --}}
                    @if ($report->status === 'IN_REVIEW') 
                        <div class="mb-2">
                            <label class="form-label">Remarks (optional)</label>
                            <textarea rows="2" class="form-control" wire:model.live.defer="remarks" placeholder="Reason / note"></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success flex-fill" wire:click="approve">
                                <i class="bi bi-check2-circle"></i> Approve
                            </button>
                            <button class="btn btn-outline-danger flex-fill" wire:click="reject">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </div>
                        <div class="form-text mt-2">
                            Only the assigned approver can act; others will be blocked by the engine.
                        </div>
                    @endif

                    @if (in_array($report->status, ['APPROVED', 'REJECTED']))
                        <div class="alert alert-light border mt-2 mb-0">
                            This report is <strong>{{ strtolower($report->status) }}</strong>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Items table --}}
    @php
        $monetary = (float) $report->items->sum(fn($i) => (float) $i->verify_quantity * (float) $i->price);
    @endphp

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Items</div>
                <div class="small text-muted">
                    Total: <span class="fw-semibold">{{ number_format($monetary, 2) }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Part Name</th>
                            <th class="text-end">Rec Qty</th>
                            <th class="text-end">Verify Qty</th>
                            <th class="text-end">Can Use</th>
                            <th class="text-end">Can't Use</th>
                            <th class="text-end">Price</th>
                            <th>Cur</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report->items as $i)
                            @php $line = (float)$i->verify_quantity * (float)$i->price; @endphp
                            <tr>
                                <td>{{ $i->part_name }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($i->rec_quantity, 4, '.', ''), '0'), '.') }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($i->verify_quantity, 4, '.', ''), '0'), '.') }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($i->can_use, 4, '.', ''), '0'), '.') }}
                                </td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($i->cant_use, 4, '.', ''), '0'), '.') }}
                                </td>
                                <td class="text-end">{{ number_format($i->price, 2) }}</td>
                                <td>{{ $i->currency }}</td>
                                <td class="text-end">{{ number_format($line, 2) }}</td>
                            </tr>
                            {{-- Defects row --}}
                            @if ($i->defects->count())
                                <tr class="bg-light">
                                    <td colspan="8" class="py-2">
                                        <div class="small text-muted mb-1">Defects</div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="width:12%">Code</th>
                                                        <th style="width:28%">Name</th>
                                                        <th style="width:14%">Severity</th>
                                                        <th style="width:14%">Source</th>
                                                        <th class="text-end" style="width:14%">Qty</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($i->defects as $d)
                                                        <tr>
                                                            <td>{{ $d->code ?? '—' }}</td>
                                                            <td>{{ $d->name }}</td>
                                                            <td>{{ $d->severity }}</td>
                                                            <td>{{ $d->source }}</td>
                                                            <td class="text-end">
                                                                {{ rtrim(rtrim(number_format($d->quantity, 4, '.', ''), '0'), '.') }}
                                                            </td>
                                                            <td class="text-muted">{{ $d->notes ?: '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($report->items->count())
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Grand Total</th>
                                <th class="text-end">{{ number_format($monetary, 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Generic Approval Timeline widget --}}
    @livewire('approval.timeline', [
        'approvableType' => \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class,
        'approvableId' => $report->id,
    ])
</div>
