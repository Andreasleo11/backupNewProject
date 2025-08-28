@extends('layouts.pdf')

@section('content')
  <style>
    /* Reduce font size for all text */
    body {
      font-size: 12px;
      /* Adjust the font size as needed */
    }

    /* Reduce font size for specific elements */
    .autograph-box {
      width: 200px;
      height: 100px;
      background-size: contain;
      background-repeat: no-repeat;
      border: 1px solid #ccc;
    }

    /* Reduce font size for table headers */
    th,
    td {
      font-size: 14px;
      /* Adjust the font size as needed */
    }

    @page {
      transform: scale(0.75);
      /* Adjust the scale factor as needed */
      transform-origin: 0 0;
    }
  </style>
  <div class="card">
    <div class="d-flex flex-row-reverse mb-3">
      <div
        class="p-2 {{ ($purchaseRequest->user_id_create === $user->id && $purchaseRequest->status === 1) ||
        ($purchaseRequest->status === 1 && $user->is_head) ||
        ($purchaseRequest->status === 6 && $user->specification->name === 'PURCHASER') ||
        (($purchaseRequest->status === 2 &&
            $user->department->name == 'PERSONALIA' &&
            $user->is_head === 1) ||
            ($purchaseRequest->status === 7 && $user->is_gm))
            ? ''
            : 'd-none' }}">
        @include('partials.edit-purchase-request-modal', [
            'pr' => $purchaseRequest,
            'details' => $filteredItemDetail,
        ])
        <button data-bs-target="#edit-purchase-request-modal-{{ $purchaseRequest->id }}"
          data-bs-toggle="modal" class="btn btn-primary"><i class='bx bx-edit'></i> Edit</button>
      </div>
    </div>

    <div class="text-center">
      <span class="h2 fw-semibold">Purchase Requisition</span> <br>
      <div class="fs-6 mt-2">
        <span class="fs-6 text-secondary">Created By : </span> {{ $userCreatedBy->name }} <br>
        <span class="fs-6 text-secondary">From Department : </span>
        {{ $purchaseRequest->from_department }}
        <br>
        <span class="fs-6 text-secondary">Doc num : </span> {{ $purchaseRequest->doc_num }}
        <div class="mt-2">
          @include('partials.pr-status-badge', ['pr' => $purchaseRequest])
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-borderlesss">
          <tbody>
            <tr>
              <th>Date PR</th>
              <td>: @formatDate($purchaseRequest->date_pr)</td>
              <th>Date Required</th>
              <td>: @formatDate($purchaseRequest->date_required)
              </td>
            </tr>
            <tr>
              <th>To Department</th>
              <td>: {{ $purchaseRequest->to_department }}</td>
              <th>PR No</th>
              <td>: {{ $purchaseRequest->pr_no }}</td>
            </tr>
            <tr>
              <th>Supplier</th>
              <td>: {{ $purchaseRequest->supplier }}</td>
              <th>PIC</th>
              <td>: {{ $purchaseRequest->pic }}</td>

              </td>
            </tr>
            <tr>
              <th style="width: 15%">Remark</th>
              <td colspan="3" style="width: 35%; word-wrap: break-word; word-break: break-all;">:
                {{ $purchaseRequest->remark }}
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-responsive mt-4">
        <table class="table table-bordered table-hover text-center table-striped mb-0">
          <thead>
            <tr>
              <th rowspan="2" class="align-middle">No</th>
              <th rowspan="2" class="align-middle">Item Name</th>
              <th rowspan="2" class="align-middle">Quantity</th>
              <th rowspan="2" class="align-middle">UoM</th>
              <th rowspan="2" class="align-middle">Purpose</th>
              <th colspan="2" class="align-middle">Unit Price</th>
              <th rowspan="2" class="align-middle">Subtotal</th>
              @if ($purchaseRequest->is_import)
                @php
                  $mouldingApprovalCase =
                      ($purchaseRequest->is_import === 1 && $user->email === 'fang@daijo.co.id') ||
                      ($purchaseRequest->is_import === 0 && $user->email === 'ong@daijo.co.id');

                  $purchaseRequest->is_import;
                @endphp

                <th rowspan="2" class="align-middle {{ $mouldingApprovalCase ? '' : 'd-none' }}">
                  Is
                  Approve
                </th>
              @elseif (
                  $user->specification->name === 'DIRECTOR' ||
                      $user->specification->name == 'VERIFICATOR' ||
                      ($user->department->name === $purchaseRequest->from_department && $user->is_head == 1))
                <th rowspan="2" class="align-middle">Is Approve</th>
              @endif
              @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
                <th rowspan="2" class="align-middle">Received Qty</th>
              @endif
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
          <tbody>
            @forelse($filteredItemDetail as $detail)
              @if (!isset($prevCurrency))
                @php
                  $prevCurrency = $detail->currency;
                @endphp
              @elseif($prevCurrency != $detail->currency)
                @php
                  $isThereAnyCurrencyDifference = true;
                @endphp
              @endif
              <tr
                class=" @if ($detail->is_approve === 1) table-success
                                    @elseif($detail->is_approve === 0)
                                        table-danger text-decoration-line-through
                                    @elseif($detail->is_approve === null)
                                        @if ($user->specification->name === 'DIRECTOR')
                                        @elseif ($detail->is_approve_by_verificator === 1)
                                            table-success
                                        @elseif($detail->is_approve_by_verificator === 0)
                                            table-danger text-decoration-line-through
                                        @elseif($user->specification->name === 'VERIFICATOR')
                                        @else
                                            @if ($detail->is_approve_by_head === 1)
                                                table-success
                                            @elseif($detail->is_approve_by_head === 0)
                                                table-danger text-decoration-line-through @endif
                                        @endif
                                    @endif ">
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
                    @currency($detail->price) @endif
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
                    @currency($subtotal) @endif
                </td>

                {{-- Logic for total --}}
                @php
                  if ($purchaseRequest->status === 6 || $purchaseRequest->status === 7) {
                      if (!is_null($detail->is_approve_by_head)) {
                          if ($detail->is_approve_by_head) {
                              $totalall += $subtotal;
                          }
                      } else {
                          $totalall += $subtotal;
                      }
                  } elseif ($purchaseRequest->status === 2) {
                      if (!is_null($detail->is_approve_by_verificator)) {
                          if ($detail->is_approve_by_verificator) {
                              $totalall += $subtotal;
                          }
                      } else {
                          if ($detail->is_approve_by_head) {
                              $totalall += $subtotal;
                          }
                      }
                  } elseif ($purchaseRequest->status === 3) {
                      if (!is_null($detail->is_approve)) {
                          if ($detail->is_approve) {
                              $totalall += $subtotal;
                          }
                      } else {
                          if (
                              $purchaseRequest->type === 'office' ||
                              ($purchaseRequest->to_department === 'Computer' &&
                                  $purchaseRequest->type === 'factory')
                          ) {
                              if ($detail->is_approve_by_verificator) {
                                  $totalall += $subtotal;
                              }
                          } elseif ($detail->is_approve_by_gm) {
                              $totalall += $subtotal;
                          }
                      }
                  } elseif ($purchaseRequest->status === 4) {
                      if ($detail->is_approve) {
                          $totalall += $subtotal;
                      }
                  } elseif ($purchaseRequest->status === 1) {
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

                {{-- Button approve reject per item --}}
                @if ($purchaseRequest->is_import)
                  <td class="{{ $mouldingApprovalCase ? '' : 'd-none' }}">
                    @if ($detail->is_approve_by_head === null)
                      <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                        class="btn btn-danger">Reject</a>
                      <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                        class="btn btn-success">Approve</a>
                    @else
                      {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                    @endif
                  </td>
                @elseif ($user->department->name === $purchaseRequest->from_department && $user->is_head == 1)
                  <td>
                    @if ($detail->is_approve_by_head === null)
                      <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                        class="btn btn-danger">Reject</a>
                      <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                        class="btn btn-success">Approve</a>
                    @else
                      {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                    @endif
                  </td>
                @elseif ($user->specification->name == 'VERIFICATOR')
                  <td>
                    @if ($detail->is_approve_by_verificator === null)
                      <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                        class="btn btn-danger">Reject</a>
                      <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
                        class="btn btn-success">Approve</a>
                    @else
                      {{ $detail->is_approve_by_verificator == 1 ? 'Yes' : 'No' }}
                    @endif
                  </td>
                @elseif ($user->specification->name === 'DIRECTOR')
                  <td>
                    @if ($detail->is_approve === null)
                      <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                        class="btn btn-danger">Reject</a>
                      <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                        class="btn btn-success">Approve</a>
                    @else
                      {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                    @endif
                  </td>
                @endif
                @php
                  $receivedTdColor = '';
                  if ($detail->quantity > 1 && $detail->quantity && $detail->is_approve === 1) {
                      if ($detail->received_quantity === $detail->quantity) {
                          $receivedTdColor = 'table-success';
                      } else {
                          $receivedTdColor = 'table-warning';
                      }
                  }
                @endphp
                @if ($purchaseRequest->status === 4 && $user->id === $purchaseRequest->createdBy->id)
                  <td class="{{ $receivedTdColor }}">
                    {{ $detail->received_quantity }} of {{ $detail->quantity }}
                  </td>
                @endif
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
                    @currency($totalall ?? 0) @endif
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
@endsection
