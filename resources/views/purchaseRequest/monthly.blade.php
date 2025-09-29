@extends('layouts.app')

@section('content')
    <form action="/purchaserequest/month-selected" method="get">
        <label for="month">Select a month:</label>
        <input type="month" id="month" name="month">
        <button type="submit">Submit</button>
    </form>

    <section aria-label="table-report" class="container mt-5">
        @foreach ($purchaseRequests as $pr)
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mx-3 mt-4 mb-5 text-center">
                        <span class="h1 fw-semibold">Purchase Requisition</span>
                        <div class="fs-6 mt-2">
                            @php
                                $userCreatedBy = $pr->createdBy;
                                $fromDepartment = App\Models\Department::where('name', $pr->from_department)->first();
                                if (!$fromDepartment) {
                                    // Handle the case where the department is not found
                                    abort(404, 'Department not found');
                                }
                                $fromDeptNo = $fromDepartment->dept_no;
                            @endphp
                            <span class="fs-6 text-secondary">Created By : </span> {{ $userCreatedBy->name }} <br>
                            <span class="fs-6 text-secondary">From Department : </span>
                            {{ $pr->from_department . " ($fromDeptNo)" }}
                            <br>
                            <span class="fs-6 text-secondary">Doc num : </span> {{ $pr->doc_num }}
                            <div class="mt-2">
                                @include('partials.pr-status-badge', ['pr' => $pr])
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderlesss">
                            <tbody>
                                <tr>
                                    <th>Date PR</th>
                                    <td>: {{ $pr->date_pr }}</td>
                                    <th>Date Required</th>
                                    <td>: {{ $pr->date_required }}</td>
                                </tr>
                                <tr>
                                    <th>To Department</th>
                                    <td>: {{ $pr->to_department }}</td>
                                    <th>PR No</th>
                                    <td>: {{ $pr->pr_no }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td>: {{ $pr->supplier }}</td>
                                    <th>PIC</th>
                                    <td>: {{ $pr->pic }}</td>

                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 15%">Remark</th>
                                    <td colspan="3" style="width: 35%; word-wrap: break-word; word-break: break-all;">:
                                        {{ $pr->remark }}
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover text-center table-striped">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">No</th>
                                    <th rowspan="2" class="align-middle">Item Name</th>
                                    <th rowspan="2" class="align-middle">Quantity</th>
                                    <th rowspan="2" class="align-middle">UoM</th>
                                    <th rowspan="2" class="align-middle">Purpose</th>
                                    <th colspan="2" class="align-middle">Unit Price</th>
                                    <th rowspan="2" class="align-middle">Subtotal</th>
                                </tr>
                                <tr>
                                    <th>Before</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            @php
                                $totalall = 0;
                                $isThereAnyCurrencyDifference = false;
                                $prevCurrency = null;
                            @endphp
                            @php
                                $totalall = 0; // Initialize the variable
                            @endphp
                            <tbody>
                                @forelse ($pr->itemDetail as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->item_name }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>{{ $detail->uom }}</td>
                                        <td>{{ $detail->purpose }}</td>
                                        <td>
                                            @if ($detail->master)
                                                @if ($detail->currency === 'USD')
                                                    @currencyUSD($detail->master->price)
                                                @elseif($detail->currency === 'CNY')
                                                    @currencyCNY($detail->master->price)
                                                @else
                                                    @currency($detail->master->price)
                                                @endif
                                            @else
                                                {{ 'N/A' }} <!-- Or handle null master object as appropriate -->
                                            @endif
                                        </td>
                                        <td>
                                            @if ($detail->currency === 'USD')
                                                @currencyUSD($detail->price)
                                            @elseif($detail->currency === 'CNY')
                                                @currencyCNY($detail->price)
                                            @else
                                                @currency($detail->price)
                                            @endif
                                        </td>
                                        @php
                                            $subtotal = $detail->quantity * $detail->price;
                                        @endphp
                                        <td>
                                            @if ($detail->currency === 'USD')
                                                @currencyUSD($subtotal)
                                            @elseif($detail->currency === 'CNY')
                                                @currencyCNY($subtotal)
                                            @else
                                                @currency($subtotal)
                                            @endif
                                        </td>

                                        {{-- Logic for total --}}
                                        @php
                                            if ($pr->status === 6 || $pr->status === 7) {
                                                if (!is_null($detail->is_approve_by_head)) {
                                                    if ($detail->is_approve_by_head) {
                                                        $totalall += $subtotal;
                                                    }
                                                } else {
                                                    $totalall += $subtotal;
                                                }
                                            } elseif ($pr->status === 2) {
                                                if (!is_null($detail->is_approve_by_verificator)) {
                                                    if ($detail->is_approve_by_verificator) {
                                                        $totalall += $subtotal;
                                                    }
                                                } else {
                                                    if ($detail->is_approve_by_head) {
                                                        $totalall += $subtotal;
                                                    }
                                                }
                                            } elseif ($pr->status === 3) {
                                                if (!is_null($detail->is_approve)) {
                                                    if ($detail->is_approve) {
                                                        $totalall += $subtotal;
                                                    }
                                                } else {
                                                    if (
                                                        $pr->type === 'office' ||
                                                        ($pr->to_department === 'Computer' && $pr->type === 'factory')
                                                    ) {
                                                        if ($detail->is_approve_by_verificator) {
                                                            $totalall += $subtotal;
                                                        }
                                                    } elseif ($detail->is_approve_by_gm) {
                                                        $totalall += $subtotal;
                                                    }
                                                }
                                            } elseif ($pr->status === 4) {
                                                if ($detail->is_approve) {
                                                    $totalall += $subtotal;
                                                }
                                            } elseif ($pr->status === 1) {
                                                if (!is_null($detail->is_approve_by_head)) {
                                                    if ($detail->is_approve_by_head) {
                                                        $totalall += $subtotal;
                                                    }
                                                } else {
                                                    $totalall += $subtotal;
                                                }
                                            } else {
                                                $totalall += 0;
                                            }
                                        @endphp
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No Data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-right"><strong>Total</strong></td>
                                    <td class="table-active fw-semibold">
                                        @if (!$isThereAnyCurrencyDifference)
                                            @if ($prevCurrency === 'USD')
                                                @currencyUSD($totalall ?? 0)
                                            @elseif($prevCurrency === 'CNY')
                                                @currencyCNY($totalall ?? 0)
                                            @else
                                                @currency($totalall ?? 0)
                                            @endif
                                        @else
                                            There is currency difference!
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
@endsection
