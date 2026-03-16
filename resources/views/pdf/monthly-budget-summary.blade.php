@extends('layouts.pdf')

@section('content')
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10px;
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
        margin-bottom: 20px;
        border-bottom: 2px solid #2c3e50;
        padding-bottom: 10px;
    }
    .header h2 {
        margin: 0;
        font-size: 18px;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .info-section {
        width: 100%;
        margin-bottom: 15px;
    }
    .info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 2px 0;
        vertical-align: top;
    }
    .info-label {
        font-weight: bold;
        width: 12%;
        color: #555;
    }
    .info-value {
        width: 38%;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .items-table th {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: bold;
        text-align: center;
        padding: 6px 4px;
        border: 1px solid #dee2e6;
        text-transform: uppercase;
        font-size: 9px;
    }
    .items-table td {
        padding: 6px 4px;
        border: 1px solid #dee2e6;
        vertical-align: top;
    }
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    .font-bold { font-weight: bold; }
    
    .grand-total {
        background-color: #f8f9fa;
        font-weight: bold;
        font-size: 11px;
    }

    .signatures-container {
        width: 100%;
        margin-top: 30px;
        page-break-inside: avoid;
    }
    .signature-box {
        width: 25%;
        float: left;
        text-align: center;
        padding: 0 5px;
    }
    .signature-title {
        font-weight: bold;
        font-size: 9px;
        color: #555;
        margin-bottom: 45px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 3px;
        text-transform: uppercase;
    }
    .signature-name {
        margin-top: 5px;
        font-size: 9px;
        height: 12px;
        font-weight: bold;
    }
    .signature-img {
        max-height: 35px;
        max-width: 100px;
        margin-bottom: 5px;
        margin-top: -40px;
    }
    .clearfix { clear: both; }

    .status-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        color: #fff;
    }
    .status-draft { background-color: #6c757d; }
    .status-review { background-color: #f59f00; }
    .status-approved { background-color: #2b8a3e; }
    .status-rejected { background-color: #c92a2a; }
</style>

<div class="container">
    <div class="header">
        <h2>Monthly Budget Summary Report</h2>
        <div style="margin-top: 5px; font-size: 11px; color: #666;">
            <strong>Doc Number:</strong> {{ $report->doc_num }} | 
            <strong>Month:</strong> {{ \Carbon\Carbon::parse($report->report_date)->format('F Y') }}
        </div>
        <div style="margin-top: 5px;">
            <span class="status-badge {{ $report->workflow_status === 'APPROVED' ? 'status-approved' : ($report->workflow_status === 'REJECTED' ? 'status-rejected' : 'status-review') }}">
                {{ $report->workflow_status }}
            </span>
        </div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Created By:</td>
                <td class="info-value">{{ $report->user->name ?? 'System' }}</td>
                <td class="info-label">Date Created:</td>
                <td class="info-value">{{ $report->created_at->format('d F Y') }}</td>
            </tr>
            @if($report->is_moulding)
            <tr>
                <td class="info-label">Type:</td>
                <td class="info-value">Moulding Budget</td>
                <td colspan="2"></td>
            </tr>
            @endif
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th>Item Name</th>
                <th width="5%">Dept</th>
                <th width="5%">Qty</th>
                <th width="5%">UoM</th>
                @if($report->is_moulding)
                    <th width="10%">Spec</th>
                    <th width="8%">Stock</th>
                    <th width="8%">Usage/Mo</th>
                @endif
                <th width="12%">Supplier</th>
                <th width="12%">Cost/Unit</th>
                <th width="12%">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotal = 0;
                $rowIndex = 1;
            @endphp
            @foreach($groupedDetails as $group)
                @php $rowspan = count($group['items']); @endphp
                @foreach($group['items'] as $index => $item)
                    @php 
                        $subtotal = $item['quantity'] * $item['cost_per_unit'];
                        $grandTotal += $subtotal;
                    @endphp
                    <tr>
                        @if($index === 0)
                            <td class="text-center" rowspan="{{ $rowspan }}">{{ $rowIndex++ }}</td>
                            <td rowspan="{{ $rowspan }}" class="font-bold">{{ $group['name'] }}</td>
                        @endif
                        <td class="text-center">{{ $item['dept_no'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-center">{{ $item['uom'] }}</td>
                        @if($report->is_moulding)
                            <td>{{ $item['spec'] ?? '-' }}</td>
                            <td class="text-center">{{ $item['last_recorded_stock'] ?? '-' }}</td>
                            <td class="text-center">{{ $item['usage_per_month'] ?? '-' }}</td>
                        @endif
                        <td>{{ $item['supplier'] ?? '-' }}</td>
                        <td class="text-right">@currency($item['cost_per_unit'])</td>
                        <td class="text-right font-bold">@currency($subtotal)</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="{{ $report->is_moulding ? 10 : 7 }}" class="text-right">Grand Total:</td>
                <td class="text-right">@currency($grandTotal)</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures-container">
        @php
            $sigs = $report->workflow_signatures;
        @endphp

        {{-- Prepared By --}}
        <div class="signature-box">
            <div class="signature-title">Prepared By</div>
            @if(isset($sigs['PREPARED BY']))
                <img src="{{ storage_path('app/public/' . $sigs['PREPARED BY']['image']) }}" class="signature-img">
                <div class="signature-name">{{ $sigs['PREPARED BY']['name'] }}</div>
            @else
                <div class="signature-name">{{ $report->user->name ?? '' }}</div>
            @endif
        </div>

        {{-- Checked By (GM) --}}
        <div class="signature-box">
            <div class="signature-title">Checked By (GM)</div>
            @if(isset($sigs['CHECKED BY']))
                <img src="{{ storage_path('app/public/' . $sigs['CHECKED BY']['image']) }}" class="signature-img">
                <div class="signature-name">{{ $sigs['CHECKED BY']['name'] }}</div>
            @else
                <div class="signature-name">&nbsp;</div>
            @endif
        </div>

        {{-- Known By (Director) --}}
        <div class="signature-box">
            <div class="signature-title">Known By (Director)</div>
            @if(isset($sigs['KNOWN BY']))
                <img src="{{ storage_path('app/public/' . $sigs['KNOWN BY']['image']) }}" class="signature-img">
                <div class="signature-name">{{ $sigs['KNOWN BY']['name'] }}</div>
            @else
                <div class="signature-name">&nbsp;</div>
            @endif
        </div>

        {{-- Approved By (Optional 4th signature or just spacing) --}}
        <div class="signature-box">
            <div class="signature-title">Approved By</div>
             @if(isset($sigs['APPROVED BY']))
                <img src="{{ storage_path('app/public/' . $sigs['APPROVED BY']['image']) }}" class="signature-img">
                <div class="signature-name">{{ $sigs['APPROVED BY']['name'] }}</div>
            @else
                <div class="signature-name">&nbsp;</div>
            @endif
        </div>

        <div class="clearfix"></div>
    </div>
</div>
@endsection
