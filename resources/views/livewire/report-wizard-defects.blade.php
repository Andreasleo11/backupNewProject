<div class="row">
  {{-- Header --}}
  <div class="mb-4">
    <h4 class="fw-bold">📝 Add/Modify Part Defects</h4>
    <p class="text-muted">You need to add part defects for each of part details that you have been
      added before.</p>
  </div>

  {{-- LEFT PANEL: Part Selection --}}
  <div class="col-md-4 mb-3">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light fw-semibold">
        🧩 Select Part Detail
      </div>
      <div class="card-body p-2">
        <ul class="list-group">
          @foreach ($existingDetails as $detail)
            <li
              class="list-group-item d-flex justify-content-between align-items-center 
                            {{ $activeDetailId === $detail->id ? 'active text-white bg-primary border-primary' : '' }}"
              wire:click="setActiveDetail({{ $detail->id }})" style="cursor: pointer;">
              <span>{{ $detail->part_name }}</span>
              @if (is_array($detail->defects))
                <span class="badge bg-secondary">{{ count($detail->defects) }} defect(s)</span>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  {{-- RIGHT PANEL: Defect Form --}}
  <div class="col-md-8">
    @if ($activeDetailId !== null)
      <div class="alert alert-info fw-semibold">
        Adding defects for part: <span
          class="text-primary">{{ $existingDetails[$activeDetailId]->part_name ?? 'Unknown Part' }}</span>
      </div>
    @endif

    @if ($activeDetailId === null)
      <div class="alert alert-warning mb-0">
        Please select a part from the left to input its defects.
      </div>
    @else
      {{-- Defect Form --}}
      <div class="card bg-light border-1 mb-4">
        <div class="card-header fw-semibold bg-white d-flex justify-content-between">
          <div>
            <i class="bi bi-tools me-2 text-secondary"></i> Add Defect Form
          </div>
          @include('partials.add-defect-category-modal', ['id' => 1])
          <button type="button" data-bs-toggle="modal"
            data-bs-target="#add-defect-category-modal-1" class="btn btn-outline-primary btn-sm">+
            Add Category</button>
        </div>
        <div class="card-body">
          {{-- Defect Source --}}
          <div class="mb-3">
            <label class="fw-semibold mb-1">Select Defect Source:</label>
            <div class="d-flex flex-wrap gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="sourceCustomer" value="customer"
                  wire:model="defectSource">
                <label class="form-check-label" for="sourceCustomer">Customer</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="sourceDaijo" value="daijo"
                  wire:model="defectSource">
                <label class="form-check-label" for="sourceDaijo">Daijo</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="sourceSupplier" value="supplier"
                  wire:model="defectSource">
                <label class="form-check-label" for="sourceSupplier">Supplier</label>
              </div>
            </div>
            @error('defectSource')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- Input Fields --}}
          <div class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label">Qty</label>
              <input type="number" min="1" wire:model="defect.quantity"
                class="form-control @error('defect.quantity') is-invalid @enderror"
                placeholder="e.g. 3">
              @error('defect.quantity')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Category</label>
              <select wire:model="defect.category_id"
                class="form-select @error('defect.category_id') is-invalid @enderror">
                <option value="" disabled>-- Select Category --</option>
                @foreach ($categories as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
              </select>
              @error('defect.category_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Remarks</label>
              <input type="text" wire:model="defect.remarks"
                class="form-control @error('defect.remarks') is-invalid @enderror"
                placeholder="Optional remarks">
              @error('defect.remarks')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-2 text-end">
              <button class="btn btn-success w-100" wire:click.prevent="saveDefect">
                💾 Save
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Saved Defects Table --}}
      @if (!is_null($activeDetailId) && isset($existingDetails[$activeDetailId]))
        <h6 class="fw-semibold mb-3">
          📝 Saved Defects for:
          <span
            class="text-primary">{{ $existingDetails[$activeDetailId]->part_name ?? 'Unknown Part' }}</span>
        </h6>

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover align-middle">
            <thead class="table-light text-center">
              <tr>
                <th style="width: 80px;">Qty</th>
                <th>Category</th>
                <th>Remarks</th>
                <th>Source</th>
                <th style="width: 110px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($existingDetails[$activeDetailId]->defects as $i => $d)
                <tr>
                  <td class="text-center fw-bold">{{ $d['quantity'] }}</td>
                  <td>{{ $categories[$d['category_id']] ?? 'Unknown' }}</td>
                  <td>{{ $d['remarks'] ?? '-' }}</td>
                  <td class="text-center text-capitalize">
                    @if (!empty($d['is_customer']))
                      <span class="badge bg-primary">Customer</span>
                    @elseif (!empty($d['is_daijo']))
                      <span class="badge bg-warning text-dark">Daijo</span>
                    @elseif (!empty($d['is_supplier']))
                      <span class="badge bg-info text-dark">Supplier</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <button wire:click="removeDefectFromSession({{ $i }})"
                      class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2 fs-5 text-secondary"></i>
                    No saved defects yet. Add one using the form above.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif
    @endif
  </div>

  {{-- Navigation Buttons --}}
  <div class="d-flex justify-content-between mt-4">
    <button class="btn btn-secondary" wire:click="goBack">
      <i class="bi bi-arrow-left"></i> Back
    </button>
    <button class="btn btn-primary" wire:click="nextStep">Next <i
        class="bi bi-arrow-right"></i></button>
  </div>

  {{-- Toast --}}
  <div wire:ignore x-data="{ show: false }" x-cloak
    x-on:defect-added.window="
        show = true;
        setTimeout(() => show = false, 3000);
    "
    x-show="show" x-transition
    class="position-fixed bottom-0 end-0 m-4 bg-success text-white text-sm p-2 rounded shadow-sm"
    style="z-index: 1055; max-width: 250px;">
    ✅ Defect added successfully!
  </div>
</div>
