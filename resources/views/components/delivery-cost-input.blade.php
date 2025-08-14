@props(['label', 'wireKey', 'currencyKey', 'value'])

<label class="form-label">{{ $label }}</label>
<div class="input-group">
    <select wire:model="{{ $currencyKey }}" class="form-select">
        <option value="" disabled>--Select Currency--</option>
        <option value="IDR" selected>IDR</option>
        <option value="USD">USD</option>
        <option value="CNY">CNY</option>
    </select>
    <input type="text" class="form-control" x-data="{
        raw: '{{ number_format($value, 2, '.', ',') }}',
        format(val) {
            let parts = val.replace(/[^0-9.]/g, '').split('.');
            let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let decimal = parts[1] !== undefined ? '.' + parts[1].slice(0, 2) : '';
            return intPart + decimal;
        }
    }" x-model.lazy="raw"
        @blur="raw = format(raw); $wire.set('{{ $wireKey }}', parseFloat(raw.replace(/,/g, '')) || 0);"
        @input.debounce.300ms="if (!raw.match(/^\d*(\.\d{0,2})?$/)) return;
                               $wire.set('{{ $wireKey }}', parseFloat(raw.replace(/,/g, '')) || 0);">
</div>
