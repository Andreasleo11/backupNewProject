<div class="space-y-6">
    {{-- Header Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Filters Group --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative group">
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       class="rounded-xl border-slate-200 bg-white/50 pl-10 pr-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white min-w-[240px]"
                       placeholder="Search doc, creator...">
                <i class="bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
            </div>

            {{-- Department Filter --}}
            <select wire:model.live="departmentId"
                    class="rounded-xl border-slate-200 bg-white/50 px-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white min-w-[180px]">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->dept_no }})</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select wire:model.live="status"
                    class="rounded-xl border-slate-200 bg-white/50 px-4 py-2.5 text-xs font-bold shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white min-w-[160px]">
                <option value="">All Statuses</option>
                <option value="DRAFT">Draft</option>
                <option value="IN_REVIEW">In Review</option>
                <option value="APPROVED">Approved</option>
                <option value="REJECTED">Rejected</option>
            </select>

            {{-- Reset Filters --}}
            @if($search || $departmentId || $status)
                <button wire:click="clearFilters"
                        class="p-2.5 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-all"
                        title="Clear Filters">
                    <i class="bx bx-reset text-lg"></i>
                </button>
            @endif
        </div>

        {{-- CTA --}}
        @if ($showCreateButton)
            <a href="{{ route('monthly-budget-reports.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-95">
                <i class="bx bx-plus text-[1.1rem]"></i>
                New Report
            </a>
        @endif
    </div>

    {{-- Table Card --}}
    <div class="bg-white/70 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl overflow-hidden relative">
        {{-- Loading Overlay --}}
        <div wire:loading class="absolute inset-0 z-50 bg-white/40 backdrop-blur-[1px] flex items-center justify-center">
            <div class="flex flex-col items-center gap-2">
                <i class="bx bx-loader-alt animate-spin text-3xl text-indigo-600"></i>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Updating...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('doc_num')" class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                Document Info
                                @if($sortField === 'doc_num')
                                    <i class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-sm"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('department')" class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                Department
                                @if($sortField === 'department')
                                    <i class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-sm"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <button wire:click="sortBy('report_date')" class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors mx-auto">
                                Period
                                @if($sortField === 'report_date')
                                    <i class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-sm"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($reports as $report)
                        @php
                            $reportDate = Carbon\Carbon::parse($report->report_date);
                            $isDraft = $report->isDraft();
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors lowercase first-letter:uppercase">
                                        {{ $report->doc_num }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter mt-0.5">
                                        by {{ $report->user->name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-[10px]">
                                        {{ $report->dept_no }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 tracking-tight">{{ $report->department?->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-col items-center bg-white border border-slate-100 px-3 py-1 rounded-xl shadow-sm">
                                    <span class="text-[10px] font-black text-slate-900">{{ $reportDate->format('M Y') }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">{{ $reportDate->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    @if($isDraft)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-100">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                            </span>
                                            DRAFT
                                        </span>
                                    @else
                                        @include('partials.pr-status-badge', ['pr' => $report])
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Detail --}}
                                    <a href="{{ route('monthly-budget-reports.show', $report->id) }}"
                                        class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all active:scale-95"
                                        title="View Details">
                                        <i class='bx bx-show-alt text-lg'></i>
                                    </a>

                                    @if (
                                        ($authUser->id === $report->user->id && $isDraft) ||
                                            ($authUser->is_head && !$report->is_known_autograph))
                                        {{-- Edit --}}
                                        <a href="{{ route('monthly-budget-reports.edit', $report->id) }}"
                                            class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-all active:scale-95"
                                            title="Edit Draft">
                                            <i class='bx bx-edit-alt text-lg'></i>
                                        </a>

                                        {{-- Delete --}}
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $report->id,
                                            'route' => 'monthly-budget-reports.delete',
                                            'title' => 'Delete report confirmation',
                                            'body' => "Are you sure want to delete this report with id <strong>{$report->id}</strong>?",
                                            'iconOnly' => true,
                                            'push' => false
                                        ])
                                    @elseif ($authUser->id === $report->user->id && !$report->is_cancel && !$isDraft)
                                        {{-- Cancel --}}
                                        @include('partials.cancel-modal', [
                                            'id' => $report->id,
                                            'route' => 'monthly-budget-reports.cancel',
                                            'title' => "Cancel Report: <strong>{$report->doc_num}</strong>",
                                            'iconOnly' => true
                                        ])
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                                        <i class="bx bx-receipt text-3xl"></i>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">No reports match your filters</div>
                                    <button wire:click="clearFilters" class="text-xs font-bold text-indigo-600 hover:underline hover:text-indigo-700 transition-all">Clear all filters</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
