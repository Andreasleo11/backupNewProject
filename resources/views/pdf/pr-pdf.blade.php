@extends('layouts.pdf')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            width: 15%;
            color: #555;
        }

        .info-value {
            width: 35%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 10px;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .items-table tr.rejected td {
            color: #e74c3c;
            text-decoration: line-through;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .totals-box {
            float: right;
            width: 300px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 30px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-weight: bold;
            font-size: 12px;
        }

        .clearfix {
            clear: both;
        }

        .signatures-container {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 20%;
            float: left;
            text-align: center;
            padding: 0 10px;
        }

        .signature-title {
            font-weight: bold;
            font-size: 10px;
            color: #555;
            margin-bottom: 50px;
            /* Space for the signature image / signature line */
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .signature-name {
            margin-top: 5px;
            font-size: 10px;
            height: 12px;
        }

        .signature-img {
            max-height: 40px;
            max-width: 120px;
            margin-bottom: 5px;
            margin-top: -45px;
            /* Pull it up into the empty 50px space */
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }

        .status-draft {
            background-color: #6c757d;
        }

        .status-review {
            background-color: #f59f00;
        }

        .status-approved {
            background-color: #2b8a3e;
        }

        .status-rejected {
            background-color: #c92a2a;
        }
    </style>

    <div class="container">
        <div class="header">
            <h2>Purchase Requisition</h2>
            <div style="margin-top: 5px; font-size: 12px; color: #666;">
                <strong>Doc Num:</strong> {{ $purchaseRequest->doc_num }}
            </div>
            <div style="margin-top: 8px;">
                @php
                    $statusClass = 'status-draft';
                    $statusText = 'Draft';
                    switch ($purchaseRequest->workflow_status) {
                        case 'IN_REVIEW':
                            $statusClass = 'status-review';
                            $statusText = 'In Review';
                            break;
                        case 'APPROVED':
                            $statusClass = 'status-approved';
                            $statusText = 'Approved';
                            break;
                        case 'REJECTED':
                            $statusClass = 'status-rejected';
                            $statusText = 'Rejected';
                            break;
                        case 'RETURNED':
                            $statusClass = 'status-review';
                            $statusText = 'Returned';
                            break;
                        case 'CANCELED':
                            $statusClass = 'status-rejected';
                            $statusText = 'Canceled';
                            break;
                    }
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
            </div>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="info-label">Created By:</td>
                    <td class="info-value">{{ $userCreatedBy->name }}</td>
                    <td class="info-label">Date PR:</td>
                    <td class="info-value">@formatDate($purchaseRequest->date_pr)</td>
                </tr>
                <tr>
                    <td class="info-label">From Dept:</td>
                    <td class="info-value">{{ $purchaseRequest->from_department }}</td>
                    <td class="info-label">Date Required:</td>
                    <td class="info-value">@formatDate($purchaseRequest->date_required)</td>
                </tr>
                <tr>
                    <td class="info-label">To Dept:</td>
                    <td class="info-value">{{ $purchaseRequest->to_department }}</td>
                    <td class="info-label">Supplier:</td>
                    <td class="info-value">{{ $purchaseRequest->supplier ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">PR Number:</td>
                    <td class="info-value">{{ $purchaseRequest->pr_no ?: '-' }}</td>
                    <td class="info-label">PIC:</td>
                    <td class="info-value">{{ $purchaseRequest->pic ?: '-' }}</td>
                </tr>
                @if ($purchaseRequest->remark)
                    <tr>
                        <td class="info-label" style="padding-top: 10px;">Remarks:</td>
                        <td colspan="3" style="padding-top: 10px; font-style: italic;">"{{ $purchaseRequest->remark }}"
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="25%">Item Description</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="10%" class="text-center">UoM</th>
                    <th width="15%" class="text-right">Unit Price</th>
                    <th width="15%" class="text-right">Subtotal</th>
                    <th width="20%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filteredItemDetail as $detail)
                    @php
                        $isRejected =
                            $detail->is_approve_by_head === 0 ||
                            $detail->is_approve_by_verificator === 0 ||
                            $detail->is_approve_by_gm === 0 ||
                            $detail->is_approve === 0;

                        $isApproved = $purchaseRequest->workflow_status === 'APPROVED' && $detail->is_approve === 1;

                        $rowClass = $isRejected ? 'rejected' : '';
                        $subtotal = $detail->quantity * $detail->price;
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $detail->item_name }}</strong><br>
                            <span style="font-size: 9px; color: #777;">{{ $detail->purpose }}</span>
                        </td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-center">{{ $detail->uom }}</td>
                        <td class="text-right">
                            @if ($detail->currency === 'USD')
                                @currencyUSD($detail->price)
                            @elseif($detail->currency === 'CNY')
                                @currencyCNY($detail->price)
                            @else
                                @currency($detail->price)
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($detail->currency === 'USD')
                                @currencyUSD($subtotal)
                            @elseif($detail->currency === 'CNY')
                                @currencyCNY($subtotal)
                            @else
                                @currency($subtotal)
                            @endif
                        </td>
                        <td>
                            @if ($isRejected)
                                Rejected
                            @elseif($isApproved)
                                Approved
                            @else
                                Pending
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if (isset($totals['total']) && $totals['total'] > 0)
            <div class="totals-box">
                <div class="totals-row">
                    <span>Approved Total ({{ $totals['currency'] ?? 'IDR' }}):</span>
                    <span>
                        @if ($totals['currency'] === 'USD')
                            @currencyUSD($totals['total'])
                        @elseif($totals['currency'] === 'CNY')
                            @currencyCNY($totals['total'])
                        @else
                            @currency($totals['total'])
                        @endif
                    </span>
                </div>
                @if (isset($totals['hasCurrencyDiff']) && $totals['hasCurrencyDiff'])
                    <div style="font-size: 9px; color: #e67e22; text-align: right; margin-top: 5px;">
                        * Contains mixed currencies
                    </div>
                @endif
            </div>
        @endif

        <div class="clearfix"></div>

        <div class="signatures-container">
            {{-- Preparer Signature --}}
            <div class="signature-box">
                <div class="signature-title">Prepared By</div>
                @if (isset($signatures[1]))
                    <img src="{{ storage_path('app/public/' . $signatures[1]) }}" class="signature-img">
                @endif
                <div class="signature-name">{{ $userCreatedBy->name }}</div>
            </div>

            {{-- Dept Head Signature --}}
            <div class="signature-box">
                <div class="signature-title">Dept Head</div>
                @if (isset($signatures[2]))
                    <img src="{{ storage_path('app/public/' . $signatures[2]) }}" class="signature-img">
                @endif
                <div class="signature-name">&nbsp;</div>
            </div>

            {{-- Verificator --}}
            <div class="signature-box">
                <div class="signature-title">Verificator</div>
                @if (isset($signatures[3]))
                    <img src="{{ storage_path('app/public/' . $signatures[3]) }}" class="signature-img">
                @endif
                <div class="signature-name">&nbsp;</div>
            </div>

            {{-- GM / Factory --}}
            <div class="signature-box">
                <div class="signature-title">GM</div>
                @if (isset($signatures[4]))
                    <img src="{{ storage_path('app/public/' . $signatures[4]) }}" class="signature-img">
                @endif
                <div class="signature-name">&nbsp;</div>
            </div>

            {{-- Director --}}
            <div class="signature-box">
                <div class="signature-title">Director</div>
                @if (isset($signatures[5]))
                    <img src="{{ storage_path('app/public/' . $signatures[5]) }}" class="signature-img">
                @endif
                <div class="signature-name">&nbsp;</div>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>
@endsection
