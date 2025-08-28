{{-- resources/views/inspection/partials/second-inspection.blade.php --}}
@props(['second']) {{-- Collection<SecondInspection> --}}

@php
  /** small helpers */
  $badge = fn($j) => strtolower($j) === 'ok'
      ? '<span class="badge text-bg-success"><i class="bi bi-check-lg me-1"></i>OK</span>'
      : '<span class="badge text-bg-danger"><i class="bi bi-x-lg me-1"></i>NG</span>';
@endphp

<div class="p-2">
  <div class="table-responsive mb-4">
    <table class="table table-borderless table-sm table-striped table-hover align-middle mb-0">
      <thead class="table-light text-center">
        @php
          $thClass = 'p-2';
        @endphp
        <tr>
          <th class="{{ $thClass }}" width="50%">Document&nbsp;No.</th>
          <th class="{{ $thClass }}">Lot Size Qty</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($second as $row)
          <tr>
            <td class="text-center">{{ $row->document_number }}</td>
            <td class="text-center">{{ number_format($row->lot_size_quantity) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="2" class="text-center text-muted py-3">
              <i class="bi bi-info-circle me-1"></i> No second-inspection data
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ══════════ Sampling Data ─═════════════════════════════════════════ --}}
@php $sampling = $second->flatMap->samplingData; @endphp
<h6 class="ms-2 mb-2 text-primary"><i class="bi bi-box-seam me-1"></i> Sampling</h6>
<div class="p-2">
  <div class="table-responsive mb-4">
    <table class="table table-sm table-bordered align-middle mb-0 text-center">
      <thead class="table-light">
        <tr>
          <th>Quantity</th>
          <th>Box Label</th>
          <th>Appearance</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($sampling as $s)
          <tr>
            <td>{{ $s->quantity }}</td>
            <td>{{ $s->box_label }}</td>
            <td>{!! $badge($s->appearance) !!}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center text-muted py-2">—</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ══════════ Packaging Data ─════════════════════════════════════════ --}}
@php $pack = $second->flatMap->packagingData; @endphp
<h6 class="ms-2 mb-2 text-primary"><i class="bi bi-box2-heart me-1"></i> Packaging</h6>
<div class="p-2">
  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0 text-center">
      <thead class="table-light">
        <tr>
          <th>SNP</th>
          <th>Box Label</th>
          <th>Judgement</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pack as $p)
          @php $isOk = strtolower($p->judgement) === 'ok'; @endphp
          <tr class="{{ $isOk ? '' : 'table-danger' }}">
            <td>{{ $p->snp }}</td>
            <td>{{ $p->box_label }}</td>
            <td>{!! $badge($p->judgement) !!}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center text-muted py-2">—</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
