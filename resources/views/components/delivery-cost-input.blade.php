@props(['label', 'wireKey', 'currencyKey', 'value'])

<div class="space-y-1">
    <label class="block text-sm font-medium text-slate-700">
        {{ $label }}
    </label>

    <div x-data="{
        raw: '{{ number_format($value, 2, '.', ',') }}',
        format(val) {
            let cleaned = (val || '').toString().replace(/[^0-9.]/g, '');
            let parts = cleaned.split('.');
            let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let decimal = parts[1] !== undefined ? '.' + parts[1].slice(0, 2) : '';
            return intPart + decimal;
        }
    }" class="flex rounded-md shadow-sm">

        {{-- Currency select --}}
        <select wire:model="{{ $currencyKey }}"
            class="inline-flex items-center rounded-l-md border border-slate-300 bg-slate-50
                   px-2.5 py-2 text-xs font-medium text-slate-700
                   focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="" disabled>Currency</option>
            <option value="IDR" {{ old($currencyKey) === 'IDR' ? 'selected' : '' }}>IDR</option>
            <option value="USD" {{ old($currencyKey) === 'USD' ? 'selected' : '' }}>USD</option>
            <option value="CNY" {{ old($currencyKey) === 'CNY' ? 'selected' : '' }}>CNY</option>
        </select>

        {{-- Amount input --}}
        <input type="text" x-model.lazy="raw"
            @blur="
                raw = format(raw);
                $wire.set('{{ $wireKey }}', parseFloat((raw || '').replace(/,/g, '')) || 0);
            "
            @input.debounce.300ms="
                if (!raw.match(/^\d*(\.\d{0,2})?$/)) return;
                $wire.set('{{ $wireKey }}', parseFloat((raw || '').replace(/,/g, '')) || 0);
            "
            class="block w-full rounded-r-md border border-l-0 border-slate-300 bg-white
                   px-3 py-2 text-sm text-slate-900
                   focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            placeholder="0.00">
    </div>

    @error($wireKey)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
