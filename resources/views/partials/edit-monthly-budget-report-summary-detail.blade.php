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
        if (!value) return 'Rp 0';
        let price = value.toString().replace(/[^0-9.]/g, '');
        if (price === '') return 'Rp 0';

        let symbol = 'Rp ';
        if (price.includes('.')) {
            let parts = price.split('.');
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let decimalPart = parts[1].substring(0, 2);
            return symbol + integerPart + '.' + decimalPart;
        } else {
            return symbol + price.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    },
    init() {
        if (this.costPerUnit > 0) {
            this.costPerUnit = this.formatInput(this.costPerUnit);
        }
    }
}" x-effect="document.body.style.overflow = open ? 'hidden' : ''"
    x-on:open-modal.window="if ($event.detail.id === 'edit-monthly-budget-report-summary-detail-{{ $id }}') open = true"
    class="inline-block">

    {{-- Backdrop --}}
    <template x-teleport="body">
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-md" @click="open = false" style="display: none;">
        </div>
    </template>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="open" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
            class="fixed inset-0 z-[110] flex items-start justify-center p-4 overflow-y-auto" role="dialog"
            aria-modal="true" style="display: none;">

            <div
                class="w-full max-w-xl rounded-2xl bg-white shadow-2xl transform transition-all border border-slate-200 flex flex-col my-auto">
                <form action="{{ route('monthly-budget-summary-detail.update', $id) }}" method="post"
                    @submit="costPerUnit = costPerUnit.toString().replace(/[^0-9.]/g, '')" class="flex flex-col h-full">
                    @csrf
                    @method('PUT')

                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/80">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="bx bx-edit-alt text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest leading-none">
                                    Edit Item Detail</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-1">
                                    {{ $groupName }}</p>
                            </div>
                        </div>
                        <button type="button" @click="open = false"
                            class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-7 overflow-y-auto flex-1 text-left">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Department
                                    No</label>
                                <div
                                    class="w-full rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs font-bold text-slate-500">
                                    {{ $item['dept_no'] }}
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Item
                                    Name</label>
                                <input type="text" name="name"
                                    class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                    value="{{ $groupName }}" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Quantity</label>
                                <input type="number" name="quantity" x-model="quantity"
                                    class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Unit
                                    of Measure (UoM)</label>
                                <input type="text" name="uom"
                                    class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                    value="{{ $item['uom'] }}" required>
                            </div>
                        </div>

                        @if ($item['dept_no'] == '363')
                            <div class="p-5 rounded-2xl bg-slate-50/50 border border-slate-100 space-y-5">
                                <div class="space-y-2">
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Specification</label>
                                    <input type="text" name="spec"
                                        class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                        value="{{ $item['spec'] }}" required>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Last
                                            Rec. Stock</label>
                                        <input type="number" name="last_recorded_stock"
                                            class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                            value="{{ $item['last_recorded_stock'] }}" required>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Usage
                                            / Month</label>
                                        <input type="text" name="usage_per_month"
                                            class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                            value="{{ $item['usage_per_month'] }}" required>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Supplier
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="supplier"
                                class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                value="{{ $item['supplier'] }}" placeholder="Enter supplier name..." required>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-5 rounded-2xl bg-indigo-50/30 border border-indigo-100/50">
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-indigo-600 uppercase tracking-[0.1em] ml-1">Cost
                                    Per Unit <span class="text-rose-500">*</span></label>
                                <input type="text" name="cost_per_unit" x-model="costPerUnit"
                                    x-on:input="costPerUnit = formatInput($event.target.value)"
                                    class="w-full rounded-xl border-slate-200 text-sm font-black text-indigo-600 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                    placeholder="Rp 0" required>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Subtotal
                                    (Preview)</label>
                                <div class="w-full rounded-xl border border-white bg-white/50 px-4 py-2 text-sm font-black text-slate-800 shadow-inner flex items-center justify-end h-[50px]"
                                    x-text="formatCurrency(subtotal)">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] ml-1">Remark
                                / Reason</label>
                            <textarea name="remark" rows="2"
                                class="w-full rounded-xl border-slate-200 text-xs font-medium text-slate-700 bg-white shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-4 py-3"
                                placeholder="Note for management..." required>{{ $item['remark'] }}</textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-5 bg-slate-50/80">
                        <button type="button" @click="open = false"
                            class="inline-flex items-center rounded-xl bg-white border border-slate-200 px-6 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-95">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-xl bg-indigo-600 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:scale-[1.02] active:scale-95">
                            Update Detail
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
