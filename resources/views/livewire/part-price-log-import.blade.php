<div class="container mt-3">
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Upload Price Log (Excel/CSV)</div>
        <div class="card-body">
            <form wire:submit.prevent="updatedFile">
                <div class="mb-3">
                    <input type="file" class="form-control @error('file') is-invalid @enderror" wire:model="file"
                        accept=".xlsx,.xls,.csv,.txt">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">Preview</button>
                <div wire:loading class="small text-muted ms-2">Parsing file…</div>
            </form>

            @if ($errorsBag)
                <div class="alert alert-warning mt-3">
                    <div class="fw-semibold mb-1">Some rows were skipped:</div>
                    <ul class="mb-0">
                        @foreach ($errorsBag as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($preview)
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Preview ({{ count($preview) }}
                            row{{ count($preview) > 1 ? 's' : '' }})</h6>
                        <button class="btn btn-success btn-sm" wire:click="import" wire:loading.attr="disabled">
                            Import
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Parent Item (part_code)</th>
                                    <th>Item Description</th>
                                    <th class="text-end">Price</th>
                                    <th>Currency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($preview as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $row['part_code'] }}</code></td>
                                        <td>{{ $row['description'] }}</td>
                                        <td class="text-end">{{ number_format($row['price'], 2, '.', ',') }}</td>
                                        <td>{{ $row['currency'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($imported)
                        <div class="alert alert-success mt-3">
                            Imported {{ $imported }} row(s) successfully.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Simple toast via Alpine (optional) --}}
    <div x-data="{ show: false, msg: '', type: 'success' }"
        x-on:notify.window="msg = $event.detail.message ?? 'Done'; type = $event.detail.type ?? 'success'; show = true; setTimeout(()=> show=false, 2200)"
        x-show="show" x-cloak class="position-fixed bottom-0 end-0 m-3">
        <div class="toast show align-items-center text-white"
            :class="type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-secondary')">
            <div class="d-flex">
                <div class="toast-body" x-text="msg"></div>
            </div>
        </div>
    </div>
</div>
