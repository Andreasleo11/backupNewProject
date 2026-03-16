@extends('new.layouts.app')

@section('title', 'Monthly Budget Reports')
@section('page-title', 'Budget Reports')
@section('page-subtitle', 'Monitor and manage your department’s monthly budget reports.')

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();

        $showCreateButton = false;
        if (!$authUser->is_head && !$authUser->is_gm && $authUser->department?->name !== 'MANAGEMENT') {
            $showCreateButton = true;
        }
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex items-center justify-end gap-3">
            @if ($showCreateButton)
                <a href="{{ route('monthly-budget-reports.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-95">
                    <i class="bx bx-plus text-[1rem]"></i>
                    Create New Report
                </a>
            @endif
        </div>

        {{-- Table card --}}
        <div class="bg-white/70 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between bg-white/30">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                    <i class="bx bx-list-ul text-indigo-500"></i>
                    Reports List
                </h3>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold">
                        TOTAL: {{ $reports->total() }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-slate-700">
                    <thead class="bg-slate-50/50 text-[10px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold">Document Info</th>
                            <th class="px-6 py-4 text-left font-bold">Department</th>
                            <th class="px-6 py-4 text-center font-bold">Period</th>
                            <th class="px-6 py-4 text-center font-bold">Status</th>
                            <th class="px-6 py-4 text-right font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($reports as $report)
                            @php
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $isDraft = $report->isDraft();
                            @endphp
                            <tr class="group hover:bg-indigo-50/30 transition-all duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $report->doc_num }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            ID: #{{ $report->id }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                            {{ $report->dept_no }}
                                        </div>
                                        <span class="font-medium text-slate-700">{{ $report->department?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center px-3 py-1 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-800">{{ $reportDate->format('M Y') }}</span>
                                        <span class="text-[9px] text-slate-400 font-medium">{{ $reportDate->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
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
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
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
                                                'buttonLabel' => '',
                                                'iconOnly' => true,
                                                'push' => false
                                            ])
                                        @elseif (!$report->is_cancel && !$report->is_known_autograph && !$isDraft)
                                            {{-- Cancel --}}
                                            @include('partials.cancel-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => route('monthly-budget-reports.cancel', $report->id),
                                                'title' => 'Cancel Confirmation',
                                                'buttonLabel' => '',
                                                'iconOnly' => true,
                                                'push' => false
                                            ])
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center">
                                            <i class="bx bx-file-blank text-3xl opacity-20"></i>
                                        </div>
                                        <div class="font-bold text-slate-500 uppercase tracking-widest text-[11px]">No reports found</div>
                                        <p class="text-xs max-w-xs mx-auto text-slate-400">
                                            You haven't created any budget reports yet. Start by creating a new one.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-white/30">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
