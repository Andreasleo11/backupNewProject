{{-- resources/views/inspection/partials/dimension-table.blade.php --}}
@props(['rows'])

@php
  $badge = fn($j) => strtolower($j) === 'ok'
      ? '<span class="badge text-bg-success"><i class="bi bi-check-lg me-1"></i>OK</span>'
      : '<span class="badge text-bg-danger"><i class="bi bi-x-lg me-1"></i>NG</span>';
@endphp

<div class="p-2">
  <div class="table-responsive">
    <table
      class="table table-borderless table-sm table-striped table-hover align-middle mb-0 text-center">
      <thead class="table-light">
        <tr>
          <th style="width:15%">Area/Section</th>
          <th style="width:25%">Lower&nbsp;Limit</th>
          <th style="width:25%">Upper&nbsp;Limit</th>
          <th style="width:15%">Judgement</th>
          <th style="width:20%">Remarks</th>
        </tr>
      </thead>

      <tbody class="small">
        @forelse ($rows as $row)
          @php $isOk = strtolower($row->judgement) === 'ok'; @endphp
          <tr class="{{ $isOk ? '' : 'table-danger' }}">

            <td class="fw-semibold">{{ $row->area }}</td>

            {{-- right-align numbers, unit on its own col --}}
            <td class="text-center pe-3">
              {{ rtrim(rtrim(number_format($row->lower_limit, 2), '0'), '.') }}
              {{ $row->limit_uom }}</td>
            <td class="text-center pe-3">
              {{ rtrim(rtrim(number_format($row->upper_limit, 2), '0'), '.') }}
              {{ $row->limit_uom }}</td>

            <td>{!! $badge($row->judgement) !!}</td>
            <td>{{ $row->remarks ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-3">
              <i class="bi bi-info-circle me-1"></i> No measurement data
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
