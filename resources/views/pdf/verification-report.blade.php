@extends('layouts.pdf')

@section('content')
<style>
    body { font-size: 11px; font-family: sans-serif; }
    h2  { font-size: 14px; font-weight: 600; margin: 0; }
    th  { font-size: 10px; background: #f1f5f9; }
    td  { font-size: 10px; vertical-align: top; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .bordered th, .bordered td { border: 1px solid #cbd5e1; padding: 4px 6px; }
    .meta-table th { width: 18%; font-weight: 600; }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
    .badge-approved  { background: #dcfce7; color: #166534; }
    .badge-rejected  { background: #fee2e2; color: #991b1b; }
    .badge-in-review { background: #fef9c3; color: #854d0e; }
    .badge-draft     { background: #f1f5f9; color: #475569; }
    .signature-table { margin-top: 24px; }
    .signature-table td { width: 33%; text-align: center; padding: 8px; border: 1px solid #e2e8f0; }
    .sig-box { height: 60px; background: #f8fafc; border-radius: 4px; margin-bottom: 4px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 10px; }
</style>

{{-- Header --}}
<div style="text-align:center; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0; margin-bottom: 12px;">
    <h2>Verification Report</h2>
    <div style="margin-top: 4px;">
        <strong>{{ $report->document_number }}</strong>
        @php
            $badgeClass = match($report->status) {
                'APPROVED'  => 'badge-approved',
                'REJECTED'  => 'badge-rejected',
                'IN_REVIEW' => 'badge-in-review',
                default     => 'badge-draft',
            };
        @endphp
        <span class="status-badge {{ $badgeClass }}" style="margin-left:8px;">
            {{ str_replace('_', ' ', $report->status) }}
        </span>
    </div>
</div>

{{-- Metadata --}}
<table class="meta-table">
    <tbody>
        <tr>
            <th>Rec Date</th>
            <td>{{ optional($report->rec_date)->format('d-m-Y') ?? '-' }}</td>
            <th>Customer</th>
            <td>{{ $report->customer ?? '-' }}</td>
        </tr>
        <tr>
            <th>Verify Date</th>
            <td>{{ optional($report->verify_date)->format('d-m-Y') ?? '-' }}</td>
            <th>Invoice No</th>
            <td>{{ $report->invoice_number ?? '-' }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ data_get($report->meta, 'department', '-') }}</td>
            <th>Created</th>
            <td>{{ $report->created_at->format('d-m-Y') }}</td>
        </tr>
    </tbody>
</table>

{{-- Items Table --}}
<table class="bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Part Name</th>
            <th style="text-align:right">Rec Qty</th>
            <th style="text-align:right">Verify Qty</th>
            <th style="text-align:right">Can Use</th>
            <th style="text-align:right">Can't Use</th>
            <th>Defects (Daijo / Customer / Supplier)</th>
            <th style="text-align:right">Price</th>
            <th style="text-align:right">Total</th>
            <th>DO Number</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($report->items as $item)
            @php
                $daijo    = $item->defects->where('source', 'DAIJO');
                $customer = $item->defects->where('source', 'CUSTOMER');
                $supplier = $item->defects->where('source', 'SUPPLIER');

                $formatDefects = fn($defects) => $defects->map(fn($d) =>
                    number_format((int)$d->quantity) . ' : ' . $d->name . ($d->notes ? ' (' . $d->notes . ')' : '')
                )->implode('; ');

                $lineTotal = (float) $item->verify_quantity * (float) $item->price;
            @endphp
            <tr>
                <td style="text-align:center">{{ $loop->iteration }}</td>
                <td>{{ $item->part_name }}</td>
                <td style="text-align:right">{{ number_format((int)$item->rec_quantity) }}</td>
                <td style="text-align:right">{{ number_format((int)$item->verify_quantity) }}</td>
                <td style="text-align:right">{{ number_format((int)$item->can_use) }}</td>
                <td style="text-align:right">{{ number_format((int)$item->cant_use) }}</td>
                <td style="font-size:9px">
                    @if($daijo->count())    <strong>D:</strong> {{ $formatDefects($daijo) }}<br> @endif
                    @if($customer->count()) <strong>C:</strong> {{ $formatDefects($customer) }}<br> @endif
                    @if($supplier->count()) <strong>S:</strong> {{ $formatDefects($supplier) }} @endif
                </td>
                <td style="text-align:right">{{ number_format($item->price, 2) }} {{ $item->currency }}</td>
                <td style="text-align:right">{{ number_format($lineTotal, 2) }}</td>
                <td>{{ $item->do_number ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" style="text-align:center">No items.</td></tr>
        @endforelse
        @if($report->items->count())
            @php $grandTotal = $report->items->sum(fn($i) => (float)$i->verify_quantity * (float)$i->price); @endphp
            <tr>
                <td colspan="8" style="text-align:right; font-weight:700;">Grand Total</td>
                <td style="text-align:right; font-weight:700;">{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        @endif
    </tbody>
</table>

{{-- Approval Signatures --}}
@if($report->approvalRequest && $report->approvalRequest->steps->count())
<table class="signature-table">
    <tbody>
        <tr>
            {{-- Creator is implicitly signed by submission; show steps from engine --}}
            <td>
                <div class="sig-box">Creator / Submitter</div>
                <strong>{{ optional($report->creator)->name ?? 'Creator' }}</strong><br>
                <small style="color:#64748b">{{ $report->created_at->format('d M Y') }}</small>
            </td>
            @foreach($report->approvalRequest->steps->sortBy('sequence') as $step)
            <td>
                <div class="sig-box">
                    @if($step->approved_at)
                        ✓ Approved
                    @elseif($step->rejected_at)
                        ✗ Rejected
                    @else
                        Pending
                    @endif
                </div>
                <strong>{{ $step->approver_snapshot_name ?? $step->approver?->name ?? 'Approver' }}</strong><br>
                <small style="color:#64748b">{{ $step->role_label ?? 'Step ' . $step->sequence }}</small><br>
                @if($step->approved_at || $step->rejected_at)
                    <small style="color:#64748b">{{ ($step->approved_at ?? $step->rejected_at)->format('d M Y') }}</small>
                @endif
                @if($step->remarks)
                    <br><small style="color:#64748b; font-style:italic">"{{ $step->remarks }}"</small>
                @endif
            </td>
            @endforeach
        </tr>
    </tbody>
</table>
@else
{{-- No approval request yet — blank signature boxes --}}
<table class="signature-table">
    <tbody>
        <tr>
            <td><div class="sig-box">Creator / QA Inspector</div><strong>{{ optional($report->creator)->name ?? '-' }}</strong></td>
            <td><div class="sig-box">QA Leader</div>&nbsp;</td>
            <td><div class="sig-box">QC Head / Dept. Head</div>&nbsp;</td>
        </tr>
    </tbody>
</table>
@endif
@endsection
