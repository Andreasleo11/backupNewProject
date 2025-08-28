<div>
  {{-- Page Title --}}
  <div class="mb-4">
    <h3 class="fw-bold">📝 Final Review — QA/QC Report</h3>
    <p class="text-muted">Please review the information below before submitting the report.</p>
  </div>

  {{-- Step 1: General Info --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-semibold">
      📌 General Information
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="text-muted small">Customer</div>
          <div class="fw-semibold">{{ $report['customer'] }}</div>
        </div>
        <div class="col-md-6">
          <div class="text-muted small">Invoice No</div>
          <div class="fw-semibold">{{ $report['invoice_no'] }}</div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="text-muted small">Received Date</div>
          <div class="fw-semibold">{{ $report['rec_date'] }}</div>
        </div>
        <div class="col-md-6">
          <div class="text-muted small">Verified Date</div>
          <div class="fw-semibold">{{ $report['verify_date'] }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Step 2: Details & Defects --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-semibold">
      🧾 Part Details & Defects
    </div>
    <div class="card-body p-0">
      @forelse ($details as $detail)
        <div class="border-bottom p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ $detail->part_name }}</h5>
            <span class="badge bg-info text-dark">Total Defects:
              {{ collect($detail->defects)->sum('quantity') }}</span>
          </div>

          <div class="row text-muted small mb-3">
            <div class="col-md-4">
              ✅ Verified Qty: <span
                class="fw-semibold text-dark">{{ $detail->verify_quantity }}</span>
            </div>
            <div class="col-md-4">
              🟢 Can Use: <span class="fw-semibold text-success">{{ $detail->can_use }}</span>
            </div>
            <div class="col-md-4">
              🔴 Can't Use: <span class="fw-semibold text-danger">{{ $detail->cant_use }}</span>
            </div>
          </div>

          @if (!empty($detail->defects))
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle">
                <thead class="table-light text-center">
                  <tr>
                    <th style="width: 80px;">Qty</th>
                    <th>Category</th>
                    <th>Source</th>
                    <th>Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($detail->defects as $defect)
                    <tr>
                      <td class="text-center fw-bold">{{ $defect['quantity'] }}</td>
                      <td>{{ $categories[$defect['category_id']] ?? 'Unknown' }}</td>
                      <td class="text-center">
                        @if (!empty($defect['is_customer']))
                          <span class="badge bg-primary">Customer</span>
                        @elseif (!empty($defect['is_daijo']))
                          <span class="badge bg-warning text-dark">Daijo</span>
                        @elseif (!empty($defect['is_supplier']))
                          <span class="badge bg-info text-dark">Supplier</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>{{ $defect['remarks'] ?? '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-muted fst-italic">No defects reported for this part.</div>
          @endif
        </div>
      @empty
        <div class="p-4 text-danger text-center">
          🚫 No part details found.
        </div>
      @endforelse
    </div>
  </div>

  {{-- Submit Actions --}}
  <div class="d-flex justify-content-between align-items-center mt-4">
    <button class="btn btn-outline-secondary" wire:click="goBack">
      <i class="bi bi-arrow-left"></i> Back
    </button>

    <button class="btn btn-lg btn-success" wire:click="submitAll">
      ✅ Submit Report
    </button>
  </div>
</div>
