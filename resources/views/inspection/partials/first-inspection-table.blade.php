{{-- resources/views/inspection/partials/first-inspection-table.blade.php --}}
@props(['rows'])

@php
    /** Helpers for coloured badges */
    $chip = fn($val) => strtolower($val) === 'ok'
        ? '<span class="badge text-bg-success"><i class="bi bi-check-lg me-1"></i>OK</span>'
        : '<span class="badge text-bg-danger"><i class="bi bi-x-lg me-1"></i>NG</span>';
@endphp

<div class="p-2">
    <div class="table-responsive">
        <table class="table table-borderless table-sm align-middle mb-0 table-striped table-hover">
            <thead class="table-light text-center">
                <tr>
                    <th style="width:35%">Appearance</th>
                    <th style="width:30%">Weight</th>
                    <th style="width:35%">Fitting&nbsp;Test</th>
                </tr>
            </thead>

            <tbody class="small">
                @forelse ($rows as $row)
                    @php
                        // highlight entire row if something failed
                        $rowClass = collect([$row->appearance])
                            ->filter(fn($v) => strtolower($v) !== 'ok')
                            ->isNotEmpty()
                            ? 'table-danger'
                            : '';
                    @endphp

                    <tr class="{{ $rowClass }}">
                        {{-- Appearance badge --}}
                        <td class="text-center">{!! $chip($row->appearance) !!}</td>

                        {{-- Weight, right-aligned --}}
                        <td class="text-center pe-4">
                            {{ rtrim(rtrim(number_format($row->weight, 2), '0'), '.') }}
                            <small class="text-muted">{{ $row->weight_uom }}</small>
                        </td>

                        {{-- Fitting Test badge --}}
                        <td class="text-center">{{ $row->fitting_test }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle me-1"></i> No first-inspection data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
