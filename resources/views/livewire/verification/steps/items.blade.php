<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-4">
    {{-- LEFT PANE: Master List of Items --}}
    <div class="lg:col-span-4">
        <div class="bg-white border border-slate-300 shadow-sm rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    <i class="bi bi-box-seam mr-1.5"></i> Items ({{ count($items) }})
                </div>
                <button class="inline-flex items-center justify-center font-bold rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs px-2.5 py-1.5 shadow-sm transition-colors"
                    wire:click="addItem" data-bs-toggle="tooltip" title="Add a new item to the list" aria-label="Add Item">
                    <i class="bi bi-plus-lg mr-1"></i> Add Item
                </button>
            </div>

            <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                @forelse ($items as $i => $row)
                    @php
                        $vq = (int)($row['verify_quantity'] ?? 0);
                        $price = (float)($row['price'] ?? 0);
                        $defects = $row['defects'] ?? [];
                        $hi = collect($defects)->where('severity', 'HIGH')->count();
                        $md = collect($defects)->where('severity', 'MEDIUM')->count();
                        $lo = collect($defects)->where('severity', 'LOW')->count();

                        // calculate percent metrics
                        $okPct = $vq > 0 ? ((int)($row['can_use'] ?? 0) / $vq) * 100 : 0;
                        $ngPct = $vq > 0 ? ((int)($row['cant_use'] ?? 0) / $vq) * 100 : 0;
                        $errBag = collect($errors->getBag('default')->get("items.$i.*"))->collapse();
                    @endphp
                    <div wire:key="master-item-{{ $i }}"
                        class="w-full text-left flex justify-between items-center py-3.5 px-4 transition-colors hover:bg-slate-55/70 cursor-pointer @if($activeItem == $i) bg-blue-50 text-blue-900 border-l-4 border-blue-600 rounded-l-none @endif"
                        wire:click="selectItem({{ $i }})">
                        <div class="flex-1 min-w-0 mr-3">
                            <span class="block font-bold text-sm truncate @if($activeItem == $i) text-blue-950 @else text-slate-800 @endif">
                                {{ $row['part_name'] ?: 'Untitled Part' }}
                            </span>
                            <span class="block text-xs text-slate-455 mt-1">
                                Qty: {{ number_format($vq) }} | {{ $row['currency'] ?? 'IDR' }} {{ number_format($price, 2) }}
                            </span>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @if ($okPct >= 95)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">OK: {{ number_format($okPct, 0) }}%</span>
                                @elseif($okPct > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">OK: {{ number_format($okPct, 0) }}%</span>
                                @endif
                                @if ($ngPct > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">NG: {{ number_format($ngPct, 0) }}%</span>
                                @endif
                                @if ($hi + $md + $lo > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200"><i class="bi bi-bug-fill text-red-500 mr-0.5"></i>{{ $hi + $md + $lo }}</span>
                                @endif
                                @if ($errBag->isNotEmpty())
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">Error</span>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 text-slate-400">
                            <i class="bi bi-chevron-right text-xs"></i>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-400 py-12 px-4">
                        <i class="bi bi-box2 text-4xl d-block mb-3 text-slate-300"></i>
                        <span class="text-xs font-bold text-slate-500">No items added yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT PANE: Details & Defects Editor --}}
    <div class="lg:col-span-8">
        @if ($activeItem !== null && isset($items[$activeItem]))
            @php
                $row = $items[$activeItem];
                $vq = (int)($row['verify_quantity'] ?? 0);
                $price = (float)($row['price'] ?? 0);
                $defects = $row['defects'] ?? [];

                $okPct = $vq > 0 ? ((int)($row['can_use'] ?? 0) / $vq) * 100 : 0;
                $ngPct = $vq > 0 ? ((int)($row['cant_use'] ?? 0) / $vq) * 100 : 0;
                $lineTotal = $vq * $price;
            @endphp
            <div wire:key="detail-editor-{{ $activeItem }}"
                 class="bg-white border border-slate-300 shadow-sm rounded-xl overflow-hidden">
                
                {{-- Detail Header --}}
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Item Detail</span>
                            <span class="block text-base font-extrabold text-slate-800 truncate max-w-sm sm:max-w-md">
                                {{ $row['part_name'] ?: 'Untitled Item' }}
                            </span>
                        </div>
                        
                        {{-- Action Toolbar --}}
                        <div class="flex items-center gap-1.5 self-start sm:self-center shrink-0">
                            <button type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-55 text-slate-600 transition-colors shadow-sm @if($activeItem === 0) opacity-50 cursor-not-allowed @endif"
                                @if($activeItem > 0) wire:click="moveItemUp({{ $activeItem }})" @endif
                                data-bs-toggle="tooltip" title="Move item up">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                            <button type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-55 text-slate-600 transition-colors shadow-sm @if($activeItem === count($items) - 1) opacity-50 cursor-not-allowed @endif"
                                @if($activeItem < count($items) - 1) wire:click="moveItemDown({{ $activeItem }})" @endif
                                data-bs-toggle="tooltip" title="Move item down">
                                <i class="bi bi-arrow-down"></i>
                            </button>
                            <button type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-55 text-slate-650 transition-colors shadow-sm"
                                wire:click="insertItemBelow({{ $activeItem }})"
                                data-bs-toggle="tooltip" title="Insert new row below">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                            <button type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-55 text-slate-650 transition-colors shadow-sm"
                                wire:click="duplicateItem({{ $activeItem }})"
                                data-bs-toggle="tooltip" title="Duplicate item">
                                <i class="bi bi-files"></i>
                            </button>
                            <button type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-lg border border-red-205 bg-white hover:bg-red-50 text-red-650 transition-colors shadow-sm"
                                @click="if (confirm('Delete this item from the list?')) $wire.removeItem({{ $activeItem }})"
                                data-bs-toggle="tooltip" title="Delete item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OK Ratio %</span>
                            <span class="inline-flex items-center text-sm font-extrabold @if($okPct >= 95) text-green-700 @elseif($okPct >= 75) text-amber-700 @else text-red-700 @endif">
                                {{ number_format($okPct, 2) }}%
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Scrap Ratio %</span>
                            <span class="inline-flex items-center text-sm font-extrabold @if($ngPct > 15) text-red-700 @elseif($ngPct > 5) text-amber-700 @else text-green-700 @endif">
                                {{ number_format($ngPct, 2) }}%
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Logged Defects</span>
                            <span class="inline-flex items-center text-sm font-extrabold text-slate-700 gap-1.5">
                                <i class="bi bi-bug text-red-500"></i> {{ count($defects) }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Line Total</span>
                            <span class="inline-flex items-center text-sm font-extrabold text-slate-900 truncate">
                                {{ $row['currency'] ?? 'IDR' }} {{ number_format($lineTotal, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tabs Navigation Bar --}}
                <div class="flex border-b border-slate-200 px-5 mt-4">
                    <button type="button" 
                        class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider border-b-2 transition-colors focus:outline-none @if($activeTab === 'details') border-blue-600 text-blue-600 @else border-transparent text-slate-400 hover:text-slate-600 @endif"
                        wire:click="$set('activeTab', 'details')">
                        <i class="bi bi-list-ul mr-1.5"></i> General Details
                    </button>
                    <button type="button" 
                        class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider border-b-2 transition-colors focus:outline-none @if($activeTab === 'defects') border-blue-600 text-blue-600 @else border-transparent text-slate-400 hover:text-slate-600 @endif"
                        wire:click="$set('activeTab', 'defects')">
                        <i class="bi bi-bug-fill mr-1.5"></i> Defects ({{ count($defects) }})
                    </button>
                </div>

                {{-- Inputs Grid & Defects List --}}
                <div class="p-6 flex flex-col gap-6">
                    
                    @if($activeTab === 'details')
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            {{-- Part Name (6 columns) --}}
                            <div class="md:col-span-6">
                                <label for="fld-item-detail-partname" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Part Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" autocomplete="off" list="dl-part-detail-name"
                                    wire:key="fld-part-name-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.part_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    placeholder="Type or select part..." 
                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.part_name"
                                    id="fld-item-detail-partname">
                                <datalist id="dl-part-detail-name">
                                    @if(!empty($partSuggestions[$activeItem]))
                                        @foreach($partSuggestions[$activeItem] as $suggestion)
                                            <option value="{{ $suggestion }}"></option>
                                        @endforeach
                                    @endif
                                </datalist>
                                @error('items.' . $activeItem . '.part_name')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Price (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-price" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Unit Price <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="0.01" id="fld-item-detail-price"
                                    wire:key="fld-price-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.price') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.price"
                                    inputmode="decimal">
                                @error('items.' . $activeItem . '.price')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Currency (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-currency" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Currency
                                </label>
                                <input type="text" id="fld-item-detail-currency"
                                    wire:key="fld-currency-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.currency') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live.defer="items.{{ $activeItem }}.currency"
                                    placeholder="{{ $defaultCurrency }}">
                                @error('items.' . $activeItem . '.currency')
                                    <div class="text-[10px] text-red-655 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Received Qty (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-recqty" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Received Qty <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="1" id="fld-item-detail-recqty"
                                    wire:key="fld-rec-qty-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.rec_quantity') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.rec_quantity"
                                    inputmode="numeric">
                                @error('items.' . $activeItem . '.rec_quantity')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Verified Qty (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-verifyqty" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Verified Qty <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="1" id="fld-item-detail-verifyqty"
                                    wire:key="fld-verify-qty-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.verify_quantity') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.verify_quantity"
                                    inputmode="numeric">
                                @error('items.' . $activeItem . '.verify_quantity')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Can Use Qty (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-canuse" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Can Use Qty <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="1" id="fld-item-detail-canuse"
                                    wire:key="fld-can-use-{{ $activeItem }}"
                                    class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.can_use') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.can_use"
                                    inputmode="numeric">
                                @error('items.' . $activeItem . '.can_use')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Can't Use Qty (3 columns) --}}
                            <div class="md:col-span-3">
                                <label for="fld-item-detail-cantuse" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Can't Use Qty <span class="text-red-500">*</span>
                                </label>
                                <div class="flex rounded-lg shadow-sm">
                                    <input type="number" step="1" id="fld-item-detail-cantuse"
                                        wire:key="fld-cant-use-{{ $activeItem }}"
                                        class="w-full rounded-l-lg border-slate-350 text-slate-900 bg-slate-50/50 text-xs py-1.5 px-2.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all @error('items.' . $activeItem . '.cant_use') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                        wire:model.live.debounce.300ms="items.{{ $activeItem }}.cant_use"
                                        inputmode="numeric">
                                    <button class="inline-flex items-center justify-center px-2.5 border border-l-0 border-slate-350 bg-white hover:bg-slate-55 text-slate-550 hover:text-slate-800 transition-colors shrink-0 rounded-r-lg focus:outline-none focus:ring-4 focus:ring-blue-50/50" type="button"
                                        wire:click="fillCantUseFromDefects({{ $activeItem }})"
                                        data-bs-toggle="tooltip" title="Copy sum from logged defects">
                                        <i class="bi bi-arrow-down-up text-xs"></i>
                                    </button>
                                </div>
                                @error('items.' . $activeItem . '.cant_use')
                                    <div class="text-[10px] text-red-650 mt-1 flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if($activeTab === 'defects')
                        {{-- Logged Defects Section (Unified - Compact Row List) --}}
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Defect Entries</span>
                                <button type="button" class="inline-flex items-center justify-center font-bold rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs px-3.5 py-1.5 shadow-sm transition-colors"
                                    wire:click="openDefectPicker({{ $activeItem }})" aria-label="Open Catalog">
                                    <i class="bi bi-plus-lg mr-1"></i> Defect
                                </button>
                            </div>

                            @if (empty($defects))
                                <div class="text-center text-slate-400 py-10 border border-dashed border-slate-250 rounded-xl bg-slate-55/10">
                                    <i class="bi bi-shield-check text-3xl text-green-500 mb-2 d-block"></i>
                                    <span class="text-xs font-bold text-slate-600">No defects logged for this item.</span>
                                </div>
                            @else
                                <div class="border border-slate-250 rounded-xl overflow-hidden shadow-sm">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                            <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                                <tr>
                                                    <th class="py-2.5 px-3 w-8 text-center">#</th>
                                                    <th class="py-2.5 px-3 min-w-[200px]">Defect Name</th>
                                                    <th class="py-2.5 px-3 w-32">Source</th>
                                                    <th class="py-2.5 px-3 w-24 text-right">Quantity</th>
                                                    <th class="py-2.5 px-3 min-w-[150px]">Notes</th>
                                                    <th class="py-2.5 px-3 w-10"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200 bg-white">
                                                @foreach ($defects as $d => $def)
                                                    <tr wire:key="defect-row-{{ $activeItem }}-{{ $d }}" class="hover:bg-slate-50/30 transition-colors">
                                                        {{-- # --}}
                                                        <td class="py-2 px-3 text-center font-bold text-slate-400 border-r border-slate-100">
                                                            {{ $d + 1 }}
                                                        </td>
                                                        
                                                        {{-- Name --}}
                                                        <td class="py-2 px-3">
                                                            <div class="flex flex-col gap-1">
                                                                <input type="text" autocomplete="off" list="dl-defect-name-{{ $activeItem }}-{{ $d }}"
                                                                    wire:key="fld-def-name-{{ $activeItem }}-{{ $d }}"
                                                                    id="fld-items-{{ $activeItem }}-defects-{{ $d }}-name"
                                                                    placeholder="Defect Name *"
                                                                    class="w-full rounded-md border-slate-300 text-slate-900 bg-slate-50/30 text-xs py-1.5 px-2 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all @error('items.' . $activeItem . '.defects.' . $d . '.name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                                                    wire:model.live.debounce.300ms="items.{{ $activeItem }}.defects.{{ $d }}.name"
                                                                    x-on:focus-field.window="
                                                                        if ($event.detail.key === 'items.{{ $activeItem }}.defects.{{ $d }}.name') $el.focus()
                                                                    ">
                                                                <datalist id="dl-defect-name-{{ $activeItem }}-{{ $d }}">
                                                                    @if(!empty($defectSuggestions[$d]))
                                                                        @foreach($defectSuggestions[$d] as $sug)
                                                                            <option value="{{ $sug['name'] }}">{{ $sug['code'] }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </datalist>
                                                            </div>
                                                            @error('items.' . $activeItem . '.defects.' . $d . '.name')
                                                                <div class="text-[10px] text-red-655 mt-0.5 flex items-center gap-0.5">
                                                                    <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </td>

                                                        {{-- Source --}}
                                                        <td class="py-2 px-3">
                                                            <select wire:key="fld-def-src-{{ $activeItem }}-{{ $d }}"
                                                                class="w-full rounded-md border-slate-300 text-slate-800 bg-slate-55/40 text-xs py-1.5 px-2 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                                                                wire:model.live="items.{{ $activeItem }}.defects.{{ $d }}.source">
                                                                @foreach (\App\Domain\Verification\Enums\DefectSource::cases() as $src)
                                                                    <option value="{{ $src->value }}">{{ $src->value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        {{-- Quantity --}}
                                                        <td class="py-2 px-3 text-right">
                                                            <input type="number" step="0.0001" wire:key="fld-def-qty-{{ $activeItem }}-{{ $d }}"
                                                                id="fld-items-{{ $activeItem }}-defects-{{ $d }}-quantity"
                                                                placeholder="Qty *"
                                                                class="w-full rounded-md border-slate-300 text-slate-900 bg-slate-50/30 text-xs py-1.5 px-2 text-right focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all @error('items.' . $activeItem . '.defects.' . $d . '.quantity') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                                                wire:model.live.debounce.300ms="items.{{ $activeItem }}.defects.{{ $d }}.quantity"
                                                                inputmode="decimal">
                                                            @error('items.' . $activeItem . '.defects.' . $d . '.quantity')
                                                                <div class="text-[10px] text-red-655 mt-0.5 flex items-center gap-0.5 text-left">
                                                                    <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </td>

                                                        {{-- Notes --}}
                                                        <td class="py-2 px-3">
                                                            <input type="text" wire:key="fld-def-notes-{{ $activeItem }}-{{ $d }}"
                                                                placeholder="Notes details..."
                                                                class="w-full rounded-md border-slate-300 text-slate-800 bg-slate-50/30 text-xs py-1.5 px-2 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all @error('items.' . $activeItem . '.defects.' . $d . '.notes') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                                                wire:model.live="items.{{ $activeItem }}.defects.{{ $d }}.notes">
                                                            @error('items.' . $activeItem . '.defects.' . $d . '.notes')
                                                                <div class="text-[10px] text-red-655 mt-0.5 flex items-center gap-0.5">
                                                                    <i class="bi bi-exclamation-circle"></i>{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </td>

                                                        {{-- Action --}}
                                                        <td class="py-2 px-3 text-center">
                                                            <button type="button" 
                                                                class="inline-flex items-center justify-center p-1.5 border border-red-100 hover:border-red-200 text-red-650 hover:bg-red-50 rounded transition-colors shadow-sm focus:outline-none"
                                                                wire:click="removeDefect({{ $activeItem }}, {{ $d }})"
                                                                data-bs-toggle="tooltip" title="Remove defect">
                                                                <i class="bi bi-trash text-xs"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

            </div>
        @else
            <div class="bg-white border border-slate-300 shadow-sm rounded-xl p-12 text-center text-slate-400">
                <i class="bi bi-box-seam text-5xl d-block mb-4 text-slate-350"></i>
                <h4 class="font-bold text-slate-800 text-base mb-1">No Item Selected</h4>
                <p class="text-xs text-slate-450 max-w-sm mx-auto mb-6">Select an item from the left panel list or add a new one to view and edit its values.</p>
                <button type="button" class="inline-flex items-center justify-center font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2.5 shadow-sm transition-colors"
                    wire:click="addItem">
                    <i class="bi bi-plus-lg mr-1.5"></i> Add First Item
                </button>
            </div>
        @endif
    </div>

    {{-- Catalog Picker Modal --}}
    @if (!is_null($pickerForItem))
        <div class="fixed inset-0 z-50 overflow-y-auto animate-fade-in" style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px);">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-3xl rounded-xl bg-white p-6 shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900">Select defect from catalog</h3>
                        <button type="button" class="text-slate-455 hover:text-slate-600 transition-colors" wire:click="closeDefectPicker">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 py-4">
                        <div class="mb-4">
                            <input class="w-full rounded-lg border-slate-350 text-slate-900 bg-slate-50/50 text-sm py-2.5 px-3.5 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 transition-all ps-3" placeholder="Search code or name..."
                                wire:model.live.debounce.300ms="defectSearch">
                        </div>
                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                                <thead class="bg-slate-50">
                                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <th class="py-3.5 px-4">Code</th>
                                        <th class="py-3.5 px-4">Name</th>
                                        <th class="py-3.5 px-4">Severity</th>
                                        <th class="py-3.5 px-4">Source</th>
                                        <th class="py-3.5 px-4 text-right">Default Qty</th>
                                        <th class="py-3.5 px-4"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($catalogResults as $c)
                                        <tr wire:click="pickCatalogDefect({{ $c['id'] }})" class="hover:bg-slate-55/70 transition-colors cursor-pointer">
                                            <td class="py-3 px-4 font-bold text-slate-900">{{ $c['code'] }}</td>
                                            <td class="py-3 px-4">{{ $c['name'] }}</td>
                                            <td class="py-3 px-4">
                                                @include('partials.severity-badge', [
                                                    'severity' => $c['severity'],
                                                ])
                                            </td>
                                            <td class="py-3 px-4">
                                                @include('partials.source-chip', ['source' => $c['source']])
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">
                                                {{ number_format((int) $c['quantity']) }}
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <button class="inline-flex items-center justify-center font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 shadow-sm transition-colors"
                                                    wire:click.stop="pickCatalogDefect({{ $c['id'] }})">Use</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-slate-400 py-8">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <span class="text-slate-500">No matching defects in catalog.</span>
                                                    <button type="button" class="mt-2 inline-flex items-center justify-center font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs px-3 py-2 border border-slate-300 transition-colors shadow-sm"
                                                        wire:click="addCustomDefect({{ $pickerForItem }})">
                                                        <i class="bi bi-pencil-square mr-1"></i> Create Custom Defect
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-200 mt-2">
                        <button type="button" class="inline-flex items-center justify-center font-semibold rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs px-3.5 py-2 border border-slate-200 transition-colors"
                            wire:click="addCustomDefect({{ $pickerForItem }})">
                            <i class="bi bi-pencil-square mr-1.5"></i> Custom Defect
                        </button>
                        <button class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors" wire:click="closeDefectPicker">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
