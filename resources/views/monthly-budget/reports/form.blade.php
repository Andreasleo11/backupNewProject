<div class="space-y-6">
    {{-- Main Form Card --}}
    <div class="bg-white/70 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl overflow-hidden relative">
        {{-- Accent bar --}}
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

        <div class="p-6 sm:p-8">
            <div class="space-y-8">
                {{-- Header Fields --}}
                <div class="grid gap-6 md:grid-cols-3">
                    {{-- Department --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">
                            Department <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="dept_no"
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white outline-none">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                @if ($department->name !== 'MANAGEMENT')
                                    <option value="{{ $department->dept_no }}">{{ $department->name }}
                                        ({{ $department->dept_no }})</option>
                                @endif
                            @endforeach
                        </select>
                        @error('dept_no')
                            <p class="mt-1 text-[10px] text-rose-600 font-bold ml-1 uppercase">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Period --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">
                            Report Period <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="report_date"
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white outline-none">
                        @error('report_date')
                            <p class="mt-1 text-[10px] text-rose-600 font-bold ml-1 uppercase">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Method Toggle --}}
                    <div class="flex flex-col justify-end pb-1">
                        <div
                            class="flex items-center justify-between gap-4 bg-slate-50/50 rounded-xl p-2.5 border border-slate-100 min-h-[46px]">
                            <label
                                class="inline-flex items-center gap-3 text-[10px] font-black text-slate-500 uppercase tracking-widest cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" wire:model.live="useExcel" class="sr-only peer">
                                    <div
                                        class="w-10 h-5 bg-slate-300 rounded-full peer peer-checked:bg-indigo-600 transition-colors">
                                    </div>
                                    <div
                                        class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5">
                                    </div>
                                </div>
                                <span class="peer-checked:text-indigo-600 transition-colors">Import Excel</span>
                            </label>

                            @if ($useExcel)
                                <button type="button" wire:click="$dispatch('download-template')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-100 px-3 py-1.5 text-[10px] font-black text-indigo-600 hover:bg-indigo-100 hover:-translate-y-0.5 active:scale-95 transition-all">
                                    <i class="bx bx-download"></i>
                                    TEMPLATE
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Manual Input Section --}}
                <div x-show="!$wire.useExcel" x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6 pt-4 border-t border-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <h3
                                class="text-[11px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                                <i class="bx bx-list-check text-indigo-600 text-lg"></i>
                                Budget Items Details
                            </h3>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">Enter
                                individual expense requests for this period</p>
                        </div>
                        <button type="button" wire:click="addItem"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-[10px] font-black text-white shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 hover:scale-[1.02] hover:-translate-y-0.5 active:scale-95 uppercase tracking-widest">
                            <i class="bx bx-plus text-sm"></i>
                            Add Item
                        </button>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center w-12">
                                        #</th>
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                        Item Name</th>
                                    @if ($this->isDept363)
                                        <th
                                            class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                            Spec</th>
                                    @endif
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24">
                                        UoM</th>
                                    @if ($this->isDept363)
                                        <th
                                            class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">
                                            Stock</th>
                                        <th
                                            class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">
                                            Usage</th>
                                    @endif
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                        Remark</th>
                                    <th
                                        class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-12 text-center">
                                        -</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($items as $index => $item)
                                    <tr class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-3 text-[10px] font-black text-slate-300 text-center">
                                            {{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="items.{{ $index }}.name"
                                                class="w-full rounded-lg border-slate-200 bg-white px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all"
                                                placeholder="Hardware, Office Supply...">
                                            @error("items.{$index}.name")
                                                <p class="text-[9px] text-rose-500 font-bold mt-1 uppercase">
                                                    {{ $message }}</p>
                                            @enderror
                                        </td>
                                        @if ($this->isDept363)
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model="items.{{ $index }}.spec"
                                                    class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">
                                            </td>
                                        @endif
                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="items.{{ $index }}.uom"
                                                class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-center uppercase outline-none">
                                            @error("items.{$index}.uom")
                                                <p class="text-[9px] text-rose-500 font-bold mt-1 uppercase">
                                                    {{ $message }}</p>
                                            @enderror
                                        </td>
                                        @if ($this->isDept363)
                                            <td class="px-4 py-3 text-center">
                                                <input type="number"
                                                    wire:model="items.{{ $index }}.last_recorded_stock"
                                                    class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-center outline-none">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="text"
                                                    wire:model="items.{{ $index }}.usage_per_month"
                                                    class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-center outline-none">
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" wire:model.live="items.{{ $index }}.quantity"
                                                class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-black text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-center outline-none">
                                            @error("items.{$index}.quantity")
                                                <p class="text-[9px] text-rose-500 font-bold mt-1 uppercase">
                                                    {{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="items.{{ $index }}.remark"
                                                class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all"
                                                placeholder="...">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="p-1.5 rounded-lg bg-rose-50 text-rose-400 hover:text-rose-600 hover:bg-rose-100 hover:scale-110 active:scale-90 transition-all">
                                                <i class="bx bx-trash text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center gap-4">
                                                <div
                                                    class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 group-hover:scale-110 transition-transform duration-500">
                                                    <i class="bx bx-receipt text-3xl"></i>
                                                </div>
                                                <div class="space-y-1">
                                                    <p
                                                        class="text-[11px] font-black text-slate-300 uppercase tracking-widest">
                                                        No items added yet</p>
                                                    <p
                                                        class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter italic">
                                                        Click "Add Item" to start your budget request</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Footer Totals --}}
                        <div class="bg-indigo-900 px-6 py-5 flex justify-between items-center shadow-inner">
                            <div class="flex items-center gap-8">
                                <div class="space-y-0.5">
                                    <span
                                        class="block text-[8px] font-black text-indigo-400 uppercase tracking-[0.2em]">ITEM
                                        COUNT</span>
                                    <span
                                        class="block text-lg font-black text-white leading-none">{{ count($items) }}</span>
                                </div>
                                <div class="h-8 w-px bg-indigo-800"></div>
                                <div class="space-y-0.5">
                                    <span
                                        class="block text-[8px] font-black text-indigo-400 uppercase tracking-[0.2em]">GROSS
                                        QUANTITY</span>
                                    <span
                                        class="block text-lg font-black text-white leading-none">{{ $this->totalQuantity() }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    class="bg-indigo-800/50 text-[10px] font-black text-indigo-200 px-4 py-2 rounded-xl border border-indigo-700/50 uppercase tracking-widest">
                                    Live Calculations Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Excel Upload Section --}}
                <div x-show="$wire.useExcel" x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="space-y-4 pt-4 border-t border-slate-50">
                    <div
                        class="bg-indigo-50/50 border-2 border-dashed border-indigo-200 rounded-3xl p-10 group transition-all hover:bg-indigo-100/50 hover:border-indigo-400 relative overflow-hidden">
                        <input type="file" wire:model="excel_file"
                            class="absolute inset-0 z-10 opacity-0 cursor-pointer" accept=".xlsx,.xls">

                        <div class="relative z-0 flex flex-col items-center justify-center text-center space-y-4">
                            <div
                                class="w-20 h-20 rounded-2xl bg-white shadow-xl shadow-indigo-200/50 flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform duration-500">
                                <i class="bx bx-upload text-4xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-indigo-900 uppercase tracking-widest">
                                    @if ($excel_file)
                                        {{ $excel_file->getClientOriginalName() }}
                                    @else
                                        Drop Excel here or click to browse
                                    @endif
                                </h4>
                                <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mt-1">
                                    Supported formats: .xlsx, .xls (Max 10MB)</p>
                            </div>

                            @if ($excel_file)
                                <div
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black border border-emerald-100 uppercase tracking-widest">
                                    <i class="bx bx-check-circle text-xs"></i>
                                    File Selected
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('excel_file')
                        <p class="text-[10px] text-rose-600 font-bold uppercase ml-4">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Persistent Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-10 border-t border-slate-50">
                    <button type="button" wire:click="saveDraft"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-white border border-slate-200 px-8 py-3.5 text-xs font-black text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 hover:-translate-y-0.5 active:scale-95 uppercase tracking-widest">
                        Save As Draft
                    </button>

                    <button type="button" wire:click="signAndSubmit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-indigo-600 px-10 py-3.5 text-xs font-black text-white shadow-2xl shadow-indigo-200 transition-all hover:bg-indigo-700 hover:scale-[1.02] hover:-translate-y-0.5 active:scale-95 group uppercase tracking-widest">
                        <i class="bx bx-send mr-2 text-sm group-hover:translate-x-1 transition-transform"></i>
                        Sign & Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Template download helper form (invisible) --}}
    <form action="{{ route('monthly.budget.download.excel.template') }}" method="post" id="templateForm"
        class="hidden">
        @csrf
        <input type="hidden" name="dept_no" value="{{ $dept_no }}">
    </form>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('download-template', () => {
                document.getElementById('templateForm').submit();
            });
        });
    </script>
</div>
