<div>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-lg-6">
          <label class="col-form-label">Operator</label>
        </div>
        <div class="col">
          <input type="text" class="form-control-plaintext text-secondary"
            @if ($operator) wire:model="operator" @else value="Not Assigned" @endif
            disabled>
        </div>
        <div class="col-12 col-lg-6">
          <label class="col-form-label">Shift</label>
        </div>
        <div class="col">
          <input type="text" class="form-control-plaintext text-secondary"
            @if ($shift) wire:model="shift" @else value="Not Assigned" @endif
            disabled>
        </div>
        <div class="col-12 col-lg-6">
          <label class="col-form-label">Part Name</label>
        </div>
        <div class="col">
          <input type="text" class="form-control-plaintext text-secondary"
            @if ($part_name) wire:model="part_name" @else value="Not Assigned" @endif
            disabled>
        </div>
        <div class="col-12 col-lg-6">
          <label class="col-form-label">Part Number</label>
        </div>
        <div class="col">
          <input type="text" class="form-control-plaintext text-secondary"
            @if ($part_number) wire:model="part_number" @else value="Not Assigned" @endif
            disabled>
        </div>
      </div>
    </div>
  </div>

  @php
    $showDocumentInfo = false;
    $locked = empty(session('stepHeaderSaved'));
    $shift = session('stepDetailSaved.shift') ?? '';
    $overlay = $locked ? 'Assign step header first!' : "Shift $shift";
  @endphp
  <x-lockable-card :locked="$locked" :overlay="$overlay" title="Problems" class="mt-4">
    @foreach ($problems as $index => $row)
      <div class="card mb-3 @if (!$showDocumentInfo) d-none @endif">
        <div class="card-body">
          <h6 class="fw-bold text-primary mb-3">Document Information</h6>
          <label class="form-label">Inspection Report Document
            Number <span class="text-danger">*</span></label>
          <input type="text"
            wire:model.blur="problems.{{ $index }}.inspection_report_document_number"
            class="form-control bg-secondary-subtle @error("problems.$index.inspection_report_document_number")
                                    is-invalid
                                @enderror"
            readonly>
          @error("problems.$index.inspection_report_document_number")
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>
      <div class="card p-3 mb-3 border">
        <div class="row g-3">
          <div class="col">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select wire:model.blur="problems.{{ $index }}.type"
              class="form-select @error("problems.$index.type") is-invalid @enderror">
              <option value="">-- Select Type --</option>
              @foreach ($types as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
            @error("problems.$index.type")
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="col">
            <label class="form-label">Time <span class="text-danger">*</span></label>
            <div x-data="{ v: @entangle('problems.' . $index . '.time').live, fp: null }" x-init="fp = flatpickr($refs.t, {
                enableTime: true,
                noCalendar: true,
                time_24hr: true,
                minuteIncrement: 30, // ← 30-minute grid
                defaultDate: v,
                allowInput: true,
                onChange: (sel) => v = sel[0].toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
            });
            $watch('v', val => fp.setDate(val, false));">
              <input type="text" x-ref="t"
                class="form-control @error('problems.' . $index . '.time') is-invalid @enderror"
                readonly>
            </div>

            @error("problems.$index.time")
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="col">
            <label class="form-label">C/T <span class="text-danger">*</span></label>
            <input type="number" wire:model.blur="problems.{{ $index }}.cycle_time"
              class="form-control @error("problems.$index.cycle_time") is-invalid @enderror"
              min="1">
            @error("problems.$index.cycle_time")
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="col">
            <label class="form-label">Remarks</label>
            <input type="text" wire:model.blur="problems.{{ $index }}.remarks"
              class="form-control @error("problems.$index.remarks") is-invalid @enderror"
              rows="2" placeholder="Optional" />
            @error("problems.$index.remarks")
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="col-auto align-self-end mb-2">
            <button type="button" class="btn btn-link text-danger btn-sm"
              wire:click="removeProblem({{ $index }})">Remove</button>
          </div>
        </div>
      </div>
    @endforeach

    <div class="d-flex justify-content-between">
      <button type="button" class="btn btn-outline-secondary" wire:click="addProblem">+ Add
        Problem</button>
      @if (count($problems) > 0)
        <div>
          <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save
            Problems</button>
          <button type="button" class="btn btn-outline-danger"
            wire:click="resetStep">Reset</button>
        </div>
      @endif
    </div>
  </x-lockable-card>
</div>
