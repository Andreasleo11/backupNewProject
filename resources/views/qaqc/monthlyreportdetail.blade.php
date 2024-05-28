@extends('layouts.app')


@section('content')


    <h1>detail</h1>


    @if ($reports->isEmpty())
        <p>No reports found for this month.</p>
    @else
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }

            tr:nth-child(even) {
                background-color: #f2f2f2;
            }

            .nested-table {
                margin-top: 10px;
            }

            .nested-table th,
            .nested-table td {
                border: 1px solid #ccc;
                padding: 4px;
                font-size: 0.9em;
            }

            .nested-table th {
                background-color: #e9e9e9;
            }
        </style>

        <table>
            <thead>
                <tr>
                    <th>Verify Date</th>
                    <th>Customer</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->verify_date }}</td>
                        <td>{{ $report->customer }}</td>
                        <td>
                            <table class="nested-table">
                                <thead>
                                    <tr>
                                        <th>Part Number</th>
                                        <th>Part Name</th>
                                        <th>Rec Quantity</th>
                                        <th>Can Use</th>
                                        <th>Can't Use</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                        <th>Defects</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report->details as $detail)
                                        @php
                                            $partDetails = explode('/', $detail->part_name, 2);
                                            $partNumber = $partDetails[0];
                                            $partName = isset($partDetails[1]) ? $partDetails[1] : '';
                                            $totalPrice = $detail->rec_quantity * $detail->price;
                                        @endphp
                                        <tr>
                                            <td>{{ $partNumber }}</td>
                                            <td>{{ $partName }}</td>
                                            <td>{{ $detail->rec_quantity }}</td>
                                            <td>{{ $detail->can_use }}</td>
                                            <td>{{ $detail->cant_use }}</td>
                                            <td>{{ 'IDR ' . number_format($detail->price, 0, ',', '.') }}</td>
                                            <td>{{ 'IDR ' . number_format($totalPrice, 0, ',', '.') }}</td>
                                            <td>
                                                <table class="nested-table">
                                                    <thead>
                                                        <tr>

                                                            <th>Defect quantity</th>
                                                            <th>Category</th>
                                                            <th>Info</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($detail->defects as $defect)
                                                            <tr>

                                                                <td>{{ $defect->quantity }}</td>
                                                                <td>{{ $defect->category->name ?? '' }}</td>
                                                                <td>{{ $defect->remarks }}</td>

                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
