@props(['employees'])

<div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200">
    {{-- High-Performance Loading Bar --}}
    <div wire:loading
        wire:target="search, branch, deptCode, employmentType, perPage, sort_by, gotoPage, nextPage, previousPage, resetFilters"
        class="absolute top-0 left-0 right-0 h-1 z-[60] overflow-hidden bg-slate-100">
        <div class="h-full bg-indigo-600 animate-[loading_1.5s_infinite_linear] shadow-[0_0_10px_rgba(79,70,229,0.5)]"
            style="width: 30%;"></div>
    </div>

    <div class="overflow-x-auto custom-scrollbar transition-all duration-300"
        wire:loading.class="opacity-40 grayscale-[50%] pointer-events-none"
        wire:target="search, branch, deptCode, employmentType, perPage, sort_by, gotoPage, nextPage, previousPage, resetFilters">
        <table class="min-w-full divide-y divide-slate-200 border-separate border-spacing-0">
            <thead class="bg-slate-50">
                <tr>
                    <th
                        class="sticky left-0 z-30 bg-slate-50 px-8 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                        <button wire:click="sort_by('name')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Employee Info
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('name') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('nik')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            NIK
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('nik') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('dept_code')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Department
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('dept_code') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('position')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Position
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('position') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('branch')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Branch
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('branch') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('employment_type')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Status
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('employment_type') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('start_date')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Join Date
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('start_date') }}</span>
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                        <button wire:click="sort_by('grade_level')"
                            class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                            Grade
                            <span
                                class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('grade_level') }}</span>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        {{-- Sticky first column --}}
                        <td
                            class="sticky left-0 z-10 bg-white group-hover:bg-slate-50/50 px-8 py-3 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100">
                            <div class="flex items-center gap-4">
                                <div
                                    class="h-9 w-9 flex items-center justify-center rounded-lg bg-gradient-to-br from-slate-800 to-slate-900 text-white text-[10px] font-bold shadow-lg shadow-slate-200/50">
                                    {{ substr($employee->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <button wire:click="openAudit('{{ $employee->nik }}')"
                                        class="text-sm font-bold text-slate-900 leading-tight hover:text-blue-600 transition-colors text-left">
                                        {{ $employee->name }}
                                    </button>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <div
                                            class="text-[9px] font-bold text-blue-600 uppercase tracking-widest tabular-nums">
                                            {{ $employee->nik }}</div>
                                        <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                                        {{-- Relationship Indicators --}}
                                        <div class="flex items-center gap-1.5">
                                            @if ($employee->warningLogs->count() > 0)
                                                <span class="flex h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"
                                                    title="Active Warning Logs"></span>
                                            @endif
                                            @if ($employee->evaluationData->isNotEmpty())
                                                <span
                                                    class="text-[8px] font-black text-emerald-600 bg-emerald-50 px-1 rounded"
                                                    title="Last Evaluation Score">
                                                    {{ $employee->evaluationData->sortByDesc('Month')->first()->total ?? 'A' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <div class="text-xs font-semibold text-slate-700 uppercase tracking-tight">
                                {{ $employee->nik }}</div>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-xs font-semibold text-slate-600 uppercase">
                            {{ $employee->dept_code }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <div class="text-xs font-bold text-slate-800">{{ $employee->position }}</div>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-3 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $employee->branch === 'JAKARTA' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                                @if ($employee->branch === 'JAKARTA')
                                    <i class='bx bx-buildings mr-1 opacity-50'></i>
                                @else
                                    <i class='bx bx-map-pin mr-1 opacity-50'></i>
                                @endif
                                {{ $employee->branch }}
                            </span>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php
                                $isActive =
                                    is_null($employee->end_date) ||
                                    \Carbon\Carbon::parse($employee->end_date)->isFuture();
                            @endphp
                            @if ($isActive)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 uppercase tracking-widest">
                                    <span class="h-1 w-1 rounded-full bg-emerald-600"></span>
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-inset ring-slate-500/10 uppercase tracking-widest">
                                    <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                                    Terminated
                                </span>
                            @endif
                        </td>
                        <td
                            class="px-6 py-3 whitespace-nowrap text-[10px] font-bold text-slate-500 uppercase tracking-tight">
                            {{ $employee->start_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-[11px] font-medium text-slate-500">
                            <span class="font-bold text-slate-700">{{ $employee->grade_code }}</span>
                            <span
                                class="text-[9px] font-black text-slate-400 uppercase ml-1 px-1.5 py-0.5 bg-slate-50 rounded border border-slate-100">{{ $employee->grade_level }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center">
                            <div
                                class="mx-auto h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i class='bx bx-group text-4xl text-slate-300'></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">No employees found
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">Try adjusting your search.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($employees->hasPages())
        <div
            class="px-8 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <span class="h-1 w-1 rounded-full bg-blue-500"></span>
                Page <span class="text-slate-900">{{ $employees->currentPage() }}</span> /
                {{ $employees->lastPage() }}
                <span class="mx-2 text-slate-200">|</span>
                Total <span class="text-slate-900">{{ number_format($employees->total()) }}</span> Records
            </div>
            <div class="flex-1 md:flex-none">
                {{ $employees->links('livewire::simple-bootstrap') }}
            </div>
        </div>
    @endif
</div>
