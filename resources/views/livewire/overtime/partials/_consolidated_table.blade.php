{{-- ===== CONSOLIDATED DETAILS TABLE ===== --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative">

    @if($viewMode === 'flattened')
        {{-- FLATTENED VIEW --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-separate border-spacing-0">
                {{-- Sticky header (PR style) --}}
                <thead class="sticky top-0 z-10">
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100">
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Form Ref
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Employee
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Department
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Time & Date
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Task
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            Status
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @php $hasDetails = false; @endphp

                    @foreach ($headers as $form)
                        @foreach ($form->details as $detail)
                            @php $hasDetails = true; @endphp
                        <tr wire:key="detail-{{ $detail->id }}"
                            class="hover:bg-slate-50/50 transition-colors group">

                                {{-- Form Ref --}}
                                <td class="px-4 py-3">
                                    <a href="{{ route('overtime.detail', $form->id) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-colors">
                                        #{{ $form->id }}
                                        <i class="bx bx-link-external text-[9px]"></i>
                                    </a>
                                    @if(strtoupper($form->workflow_status) === 'REJECTED')
                                        <div class="mt-1 text-[9px] font-black text-rose-500 uppercase tracking-widest">Rejected Form</div>
                                    @endif
                                </td>

                                {{-- Employee --}}
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-xs">{{ $detail->name }}</div>
                                    <div class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $detail->NIK }}</div>
                                </td>

                                {{-- Department --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">{{ $form->department->name ?? '—' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $form->branch ?? 'HQ' }}</div>
                                </td>

                                {{-- Time --}}
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-700 text-xs">
                                        {{ date('H:i', strtotime($detail->start_time)) }} - {{ date('H:i', strtotime($detail->end_time)) }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ date('d M', strtotime($detail->start_date)) }}
                                    </div>
                                </td>

                                {{-- Task --}}
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-600 max-w-[200px] truncate" title="{{ $detail->job_desc }}">
                                        {{ $detail->job_desc }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusClasses = match ($detail->status) {
                                            'Approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'Rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                            default    => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black tracking-wide ring-1 ring-inset {{ $statusClasses }}">
                                        {{ strtoupper($detail->status ?? 'Pending') }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @can('pushToPayroll', $form)
                                            @if($form->workflow_status === 'APPROVED' && !$detail->status)
                                                <button wire:click="pushDetail({{ $form->id }}, {{ $detail->id }})"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all"
                                                    title="Push to Payroll">
                                                    <i class='bx bx-cloud-upload text-sm'></i>
                                                </button>
                                                <button wire:click="rejectDetail({{ $form->id }}, {{ $detail->id }})"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all"
                                                    title="Reject Detail">
                                                    <i class='bx bx-x text-sm'></i>
                                                </button>
                                            @endif
                                        @endcan
                                        
                                        @if (!$canApprove && !Auth::user()->can('overtime.pushToPayroll'))
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach

                        @if (!$hasDetails)
                            <tr>
                                <td colspan="7" class="px-4 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                                            <i class='bx bx-group text-3xl opacity-50'></i>
                                        </div>
                                        <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No employee details found</h5>
                                        <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">There are no overtime records to display for the current filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- GROUPED VIEW --}}
        <div class="divide-y divide-slate-100" x-data="{
            expandedForms: @js(array_fill_keys($headers->pluck('id')->toArray(), true))
        }">
            @php $hasDetails = false; @endphp

            @foreach ($headers as $form)
                @if($form->details->count() > 0)
                    @php $hasDetails = true; @endphp

                    {{-- Form Header --}}
                    <div class="form-group bg-slate-50/50 px-4 py-4 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Form Checkbox --}}
                                @if ($canApprove && $form->can_approve)
                                    <input type="checkbox" x-model="selectedIds" value="{{ $form->id }}"
                                        class="form-checkbox h-4 w-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer transition-all">
                                @endif

                                {{-- Form Info --}}
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('overtime.detail', $form->id) }}"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        <i class='bx bx-file text-base'></i>
                                        #{{ $form->id }}
                                    </a>

                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-medium text-slate-700">{{ $form->department->name ?? '—' }}</span>
                                        <span class="text-slate-400">•</span>
                                        <span class="text-slate-500">{{ $form->details->count() }} employees</span>
                                        <span class="text-slate-400">•</span>
                                        <span class="text-slate-500">{{ $form->branch ?? 'HQ' }}</span>
                                    </div>

                                    @if(strtoupper($form->workflow_status) === 'REJECTED')
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black tracking-wide bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                            REJECTED FORM
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Expand/Collapse Toggle --}}
                            <button @click="expandedForms[{{ $form->id }}] = !expandedForms[{{ $form->id }}]"
                                class="h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                                <i class='bx bx-chevron-down text-slate-400 text-sm transition-transform'
                                   :class="expandedForms[{{ $form->id }}] ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Form Details --}}
                    <div class="details-section bg-white"
                         x-show="expandedForms[{{ $form->id }}]"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 max-h-0"
                         x-transition:enter-end="opacity-100 max-h-screen"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 max-h-screen"
                         x-transition:leave-end="opacity-0 max-h-0"
                         style="display: none;">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-separate border-spacing-0">
                                <thead class="bg-slate-25">
                                    <tr>
                                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Employee
                                        </th>
                                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Time & Date
                                        </th>
                                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Task
                                        </th>
                                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($form->details as $detail)
                                        <tr wire:key="grouped-detail-{{ $detail->id }}"
                                            class="hover:bg-slate-50/50 transition-colors">

                                            {{-- Employee --}}
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-slate-800 text-sm">{{ $detail->name }}</div>
                                                <div class="text-[11px] font-medium text-slate-400 mt-0.5">{{ $detail->NIK }}</div>
                                            </td>

                                            {{-- Time --}}
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-slate-700 text-sm">
                                                    {{ date('H:i', strtotime($detail->start_time)) }} - {{ date('H:i', strtotime($detail->end_time)) }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">
                                                    {{ date('d M', strtotime($detail->start_date)) }}
                                                </div>
                                            </td>

                                            {{-- Task --}}
                                            <td class="px-4 py-3">
                                                <div class="text-sm text-slate-600 max-w-[250px] truncate" title="{{ $detail->job_desc }}">
                                                    {{ $detail->job_desc }}
                                                </div>
                                            </td>

                                            {{-- Status --}}
                                            <td class="px-4 py-3 text-center">
                                                @php
                                                    $statusClasses = match ($detail->status) {
                                                        'Approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                        'Rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                                        default    => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black tracking-wide ring-1 ring-inset {{ $statusClasses }}">
                                                    {{ strtoupper($detail->status ?? 'Pending') }}
                                                </span>
                                            </td>

                                            {{-- Actions --}}
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    @can('pushToPayroll', $form)
                                                        @if($form->workflow_status === 'APPROVED' && !$detail->status)
                                                            <button wire:click="pushDetail({{ $form->id }}, {{ $detail->id }})"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all"
                                                                title="Push to Payroll">
                                                                <i class='bx bx-cloud-upload text-sm'></i>
                                                            </button>
                                                            <button wire:click="rejectDetail({{ $form->id }}, {{ $detail->id }})"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all"
                                                                title="Reject Detail">
                                                                <i class='bx bx-x text-sm'></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach

            @if (!$hasDetails)
                <div class="px-4 py-20 text-center">
                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                            <i class='bx bx-group text-3xl opacity-50'></i>
                        </div>
                        <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No employee details found</h5>
                        <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">There are no overtime records to display for the current filters.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

</div>
