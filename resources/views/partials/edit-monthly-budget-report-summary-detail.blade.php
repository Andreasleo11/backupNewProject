@php
    $id = $item['id'];
    $groupName = $group['name'];
    $subtotal = ($item['quantity'] ?? 0) * ($item['cost_per_unit'] ?? 0);
@endphp

<div x-data="{ 
        open: false,
        quantity: {{ $item['quantity'] ?? 0 }},
        costPerUnit: '{{ $item['cost_per_unit'] ?? 0 }}',
        get subtotal() {
            const price = parseFloat(this.costPerUnit.toString().replace(/[^0-9.]/g, '')) || 0;
            return price * this.quantity;
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        },
        formatInput(value) {
            let price = value.toString().replace(/[^0-9.]/g, '');
            let symbol = 'Rp ';
            
            if (price.includes('.')) {
                let parts = price.split('.');
                let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                let decimalPart = parts[1].substring(0, 2);
                return symbol + integerPart + '.' + decimalPart;
            } else {
                return symbol + price.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        }
    }" 
    x-on:open-modal.window="if ($event.detail.id === 'edit-monthly-budget-report-summary-detail-{{ $id }}') open = true"
    class="inline-block">

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-[100] bg-black/30 backdrop-blur-sm" @click="open = false" x-cloak></div>

    {{-- Modal --}}
    <div x-show="open" x-transition.scale.origin.top class="fixed inset-0 z-[110] flex items-center justify-center px-4" role="dialog" aria-modal="true" x-cloak>
        <div class="w-full max-w-lg rounded-2xl bg-white/90 backdrop-blur-xl shadow-2xl ring-1 ring-white/50 overflow-hidden transform transition-all border border-slate-200/50">
            <form action="{{ route('monthly.budget.report.summary.detail.update', $id) }}" method="post" @submit="costPerUnit = costPerUnit.toString().replace(/[^0-9.]/g, '')">
                @csrf
                @method('PUT')
                
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="bx bx-edit text-indigo-500"></i>
                        Edit Detail: <span class="text-indigo-600 font-black tracking-normal">{{ $groupName }}</span>
                    </h2>
                    <button type="button" @click="open = false" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dept No</label>
                            <input type="text" class="w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-bold text-slate-500" readonly value="{{ $item['dept_no'] }}">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Name</label>
                            <input type="text" name="name" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $groupName }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Quantity</label>
                            <input type="number" name="quantity" x-model="quantity" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Unit of Measure</label>
                            <input type="text" name="uom" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $item['uom'] }}" required>
                        </div>
                    </div>

                    @if ($item['dept_no'] == '363')
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Specification</label>
                            <input type="text" name="spec" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $item['spec'] }}" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Last Rec. Stock</label>
                                <input type="number" name="last_recorded_stock" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $item['last_recorded_stock'] }}" required>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Usage / Month</label>
                                <input type="text" name="usage_per_month" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $item['usage_per_month'] }}" required>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Supplier</label>
                        <input type="text" name="supplier" class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" value="{{ $item['supplier'] }}" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Cost Per Unit</label>
                            <input type="text" name="cost_per_unit" 
                                   x-model="costPerUnit" 
                                   x-on:input="costPerUnit = formatInput($event.target.value)"
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-900 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all bg-indigo-50/30">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 text-indigo-500">Subtotal</label>
                            <div class="w-full rounded-xl border border-indigo-100 bg-indigo-50/50 px-4 py-2.5 text-xs font-black text-indigo-600 shadow-inner flex items-center justify-end" x-text="formatCurrency(subtotal)">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Remark</label>
                        <textarea name="remark" rows="3" class="w-full rounded-xl border-slate-200 text-xs font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" required>{{ $item['remark'] }}</textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-6 bg-slate-50/50">
                    <button type="button" @click="open = false" 
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all hover:border-slate-300 active:scale-95 leading-none">
                        Discard
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center rounded-xl bg-slate-900 px-8 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-slate-900/20 transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-95 leading-none">
                        <i class="bx bx-check-double mr-2 text-base"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
