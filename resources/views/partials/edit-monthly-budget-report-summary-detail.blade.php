<div class="modal fade" id="edit-monthly-budget-report-summary-detail-{{ $item['id'] }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('monthly.budget.report.summary.detail.update', $item['id']) }}" method="post">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Detail for <strong>{{ $group['name'] }}</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start pb-5 px-4">
                    <div class="form-group mt-3">
                        <label for="dept_no" class="form-label">Dept No</label>
                        <input type="text" name="dept_no" class="form-control" disabled readonly
                            value="{{ old('dept_no', $item['dept_no']) }}">
                    </div>
                    <div class="form-group mt-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $group['name']) }}" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="text" name="quantity" class="form-control"
                            value="{{ old('quantity', $item['quantity']) }}" id="quantityInput{{ $item['id'] }}"
                            required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="uom" class="form-label">UoM</label>
                        <input type="text" name="uom" class="form-control" value="{{ old('uom', $item['uom']) }}"
                            required>
                    </div>
                    @if ($item['dept_no'] == '363')
                        <div class="form-group mt-3">
                            <label for="spec" class="form-label">Spec</label>
                            <input type="text" name="spec" class="form-control"
                                value="{{ old('spec', $item['spec']) }}" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="last_recorded_stock" class="form-label">Last Recorded Stock</label>
                            <input type="number" name="last_recorded_stock" class="form-control"
                                value="{{ old('last_recorded_stock', $item['last_recorded_stock']) }}" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="usage_per_month" class="form-label">Usage per Month</label>
                            <input type="text" name="usage_per_month" class="form-control"
                                value="{{ old('usage_per_month', $item['usage_per_month']) }}"" required>
                        </div>
                    @endif
                    <div class="form-group mt-3">
                        <label class="form-label" for="supplier">Supplier</label>
                        <input class="form-control" type="text" name="supplier"
                            value="{{ old('supplier', $item['supplier']) }}" required>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label" for="supplier">Cost Per Unit</label>
                        <input class="form-control" type="text" name="cost_per_unit"
                            id="costPerUnitInput{{ $item['id'] }}"
                            value="{{ old('cost_per_unit', $item['cost_per_unit'] ?? 0) }}">
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label" for="remark">Remark</label>
                        <textarea class="form-control" name="remark" id="remark" cols="30" rows="5" required>{{ old('remark', $item['remark']) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const quantityInput{{ $item['id'] }} = document.getElementById('quantityInput{{ $item['id'] }}');
    const costPerUnitInput{{ $item['id'] }} = document.getElementById('costPerUnitInput{{ $item['id'] }}');

    formatPrice(costPerUnitInput{{ $item['id'] }}, 'IDR');

    costPerUnitInput{{ $item['id'] }}.addEventListener('input', function() {
        const unitPrice = parseFloat(costPerUnitInput{{ $item['id'] }}.value.replace(/[^0-9.]/g,
            '')); // Convert to float for calculation
        const quantity = parseFloat(quantityInput{{ $item['id'] }}.value);
        // const subtotal = (quantity * unitPrice).toFixed(2);
        // subtotalInput.value = subtotal;
        formatPrice(costPerUnitInput{{ $item['id'] }}, 'IDR');
        // formatPrice(subtotalInput, currencyInput.value);
    });

    function formatPrice(input, currency) {
        // Replace non-numeric characters except period
        let price = input.value.replace(/[^0-9.]/g, '');

        let currencySymbol = '';
        if (currency === 'IDR') {
            currencySymbol = 'Rp ';
        } else if (currency === 'CNY') {
            currencySymbol = '¥ ';
        } else if (currency === 'USD') {
            currencySymbol = '$ ';
        }

        if (price.includes('.')) {
            // Handle decimal values
            let parts = price.split('.');
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add thousand separators with comma
            let decimalPart = parts[1];
            if (decimalPart.length > 2) {
                decimalPart = decimalPart.substring(0, 2); // Limit to 2 decimal places
            }
            input.value = currencySymbol + integerPart + '.' + decimalPart;
        } else {
            // Handle integer values
            let formattedPrice = price.replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add thousand separators with comma
            input.value = currencySymbol + formattedPrice;
        }
    }
</script>
