{{-- Requirement Uploads Review — Admin Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', 'Review Uploads')
@section('page-title', 'Review Uploads')
@section('page-subtitle', 'Manage and approve department document submissions')

<div x-data="{ decisionPanelOpen: false }"
    @open-decision-modal.window="decisionPanelOpen = true"
    @close-decision-modal.window="decisionPanelOpen = false">

    {{-- Page header w/ overall count --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                <i class="bx bx-check-shield text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Review Uploads</h1>
                <p class="text-sm text-slate-500 mt-0.5">Showing {{ $rows->total() }} uploads</p>
            </div>
        </div>
        <button wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 text-sm font-semibold shadow-sm transition-all">
            <i class="bx bx-download text-base"></i> Export Data
        </button>
    </div>

    {{-- Toolbar --}}
    <div class="glass-card px-5 py-4 mb-5 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                <input type="text"
                    wire:model.live.debounce.300ms="q"
                    placeholder="Search by file, requirement, dept…"
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
            </div>

            {{-- Status --}}
            <select wire:model.live="status"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none min-w-[130px]">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>

            {{-- MIME Filter --}}
            <select wire:model.live="mime_like"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none w-36">
                <option value="">Any type</option>
                <option value="pdf">PDF only</option>
                <option value="image">Images</option>
                <option value="spread">Excel / Sheets</option>
                <option value="word">Word docs</option>
            </select>

            {{-- Per page --}}
            <select wire:model.live="perPage"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
        </div>

        {{-- Date range & toggles row --}}
        <div class="flex flex-wrap justify-between items-center gap-3 pt-3 border-t border-slate-100">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide mr-1">Date</span>
                <input type="date" wire:model.live="date_from"
                    class="rounded-lg border border-slate-200 text-xs py-1.5 px-2 focus:ring-2 focus:ring-indigo-400 outline-none text-slate-600">
                <span class="text-slate-300">→</span>
                <input type="date" wire:model.live="date_to"
                    class="rounded-lg border border-slate-200 text-xs py-1.5 px-2 focus:ring-2 focus:ring-indigo-400 outline-none text-slate-600">

                <div class="flex items-center ml-2 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                    <button wire:click="setRange('7d')" class="px-2.5 py-1 text-xs font-medium rounded-md hover:bg-white hover:text-indigo-600 transition-colors {{ request('range') == '7d' ? 'bg-white shadow text-indigo-700' : 'text-slate-600' }}">7d</button>
                    <button wire:click="setRange('30d')" class="px-2.5 py-1 text-xs font-medium rounded-md hover:bg-white hover:text-indigo-600 transition-colors {{ request('range') == '30d' ? 'bg-white shadow text-indigo-700' : 'text-slate-600' }}">30d</button>
                    <button wire:click="setRange('month')" class="px-2.5 py-1 text-xs font-medium rounded-md hover:bg-white hover:text-indigo-600 transition-colors {{ request('range') == 'month' ? 'bg-white shadow text-indigo-700' : 'text-slate-600' }}">Month</button>
                </div>
                <button wire:click="clearDateRange" class="ml-1 text-xs text-indigo-600 hover:underline">Clear</button>
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none">
                <div class="relative">
                    <input type="checkbox" wire:model.live="only_expiring" class="sr-only peer">
                    <div class="w-8 h-4 rounded-full bg-slate-200 peer-checked:bg-rose-500 transition-colors"></div>
                    <div class="absolute top-0.5 left-0.5 h-3 w-3 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                </div>
                <span class="text-xs font-medium text-slate-600">Expiring ≤ 30d</span>
            </label>
        </div>
    </div>

    {{-- Main table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <td class="px-4 py-3 w-10">
                            <input type="checkbox"
                                wire:click="togglePageSelection($event.target.checked)"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                        </td>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 select-none group" wire:click="sortBy('requirements.name')">
                            Requirement <span class="group-hover:text-indigo-500">{!! $this->sortIcon('requirements.name') !!}</span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 select-none group" wire:click="sortBy('departments.name')">
                            Department <span class="group-hover:text-indigo-500">{!! $this->sortIcon('departments.name') !!}</span>
                        </th>
                        <th class="px-4 py-3">File</th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 select-none group" wire:click="sortBy('status')">
                            Status <span class="group-hover:text-indigo-500">{!! $this->sortIcon('status') !!}</span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:bg-slate-100 select-none group" wire:click="sortBy('valid_until')">
                            Validity <span class="group-hover:text-indigo-500">{!! $this->sortIcon('valid_until') !!}</span>
                        </th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $u)
                        @php
                            $badgeColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-rose-100 text-rose-700'];
                            $badge = $badgeColors[$u->status] ?? 'bg-slate-100 text-slate-600';
                            $daysLeft = $u->valid_until ? now()->diffInDays($u->valid_until, false) : null;
                        @endphp
                        <tr wire:key="upload-{{ $u->id }}" class="hover:bg-slate-50/50 transition-colors {{ in_array($u->id, $selected) ? 'bg-indigo-50/30' : '' }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $u->id }}"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-800">{{ $u->req_name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $u->req_code }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-800">{{ $u->dept_name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->dept_code ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-slate-700 truncate max-w-[180px]" title="{{ $u->original_name }}">{{ $u->original_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ Str::limit(str_replace('application/', '', $u->mime_type), 15) }} · {{ number_format($u->size / 1024, 1) }} KB</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">
                                        {{ ucfirst($u->status) }}
                                    </span>
                                    @if ($u->review_notes)
                                        <i class="bx bx-comment-detail text-slate-400 text-sm" title="Has notes"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-slate-600">{{ $u->valid_from?->format('d M y') ?? '—' }} → {{ $u->valid_until?->format('d M y') ?? '—' }}</p>
                                @if (!is_null($daysLeft))
                                    @php
                                        $expColor = $daysLeft < 0 ? 'bg-rose-100 text-rose-700' : ($daysLeft <= 7 ? 'bg-rose-50 text-rose-600 border border-rose-200' : ($daysLeft <= 14 ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'text-slate-500'));
                                    @endphp
                                    <span class="inline-flex mt-0.5 rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $expColor }}">
                                        {{ $daysLeft < 0 ? 'Expired' : "in {$daysLeft}d" }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ URL::signedRoute('uploads.download', ['upload' => $u->id]) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600 transition-colors" title="Download">
                                    <i class="bx bx-download text-lg"></i>
                                </a>
                                @can('approve-requirements')
                                    <button wire:click="openDecision({{ $u->id }})"
                                        class="inline-flex items-center justify-center px-3 h-8 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold transition-colors">
                                        Decide
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <i class="bx bx-folder-open text-4xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500 mt-2">No uploads match your filters.</p>
                                <button wire:click="$set('q', ''); $set('status', 'all')" class="mt-2 text-xs text-indigo-600 hover:underline">Clear filters</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
            <p class="text-xs text-slate-400">
                Showing {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() ?? 0 }}
            </p>
            {{ $rows->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>

    {{-- Sticky Bulk Action Bar --}}
    @if(count($selected) > 0)
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 animate-slide-up">
            <div class="glass-card premium-shadow px-4 py-3 rounded-2xl flex items-center gap-4 border border-slate-200/60 bg-white/90 backdrop-blur-md">
                <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">{{ count($selected) }}</span>
                    <span class="text-sm font-medium text-slate-700">selected</span>
                </div>
                <div class="flex items-center gap-2">
                    @can('approve-requirements')
                        <button wire:click="bulkApprove" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold shadow-sm transition-colors">
                            <i class="bx bx-check"></i> Approve
                        </button>
                        <button wire:click="bulkReject" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold shadow-sm transition-colors">
                            <i class="bx bx-x"></i> Reject
                        </button>
                    @endcan
                    <button wire:click="clearSelection" class="ml-2 text-xs font-medium text-slate-500 hover:text-slate-700">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Alpine Slide-over for Decision --}}
    <div x-show="decisionPanelOpen" style="display: none;" class="relative z-50">
        {{-- Backdrop --}}
        <div x-show="decisionPanelOpen"
            x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="decisionPanelOpen = false"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    {{-- Slide-over panel --}}
                    <div x-show="decisionPanelOpen"
                        x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-md">

                        <div class="flex h-full flex-col bg-white shadow-2xl">
                            {{-- Header --}}
                            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                                <h2 class="text-lg font-bold text-slate-800">Review Submission</h2>
                                <button @click="decisionPanelOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 overflow-y-auto px-6 py-5">
                                @if ($active)
                                    <div class="space-y-6">
                                        {{-- File context --}}
                                        <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100/50">
                                            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Requirement</p>
                                            <p class="text-sm font-bold text-slate-800">{{ $active['req_name'] }} <span class="font-normal text-slate-500">({{ $active['req_code'] }})</span></p>

                                            <div class="mt-3">
                                                <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Department</p>
                                                <p class="text-sm font-medium text-slate-800">{{ $active['dept_name'] }}</p>
                                            </div>

                                            <div class="mt-3">
                                                <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Validity Claimed</p>
                                                <p class="text-sm font-medium text-slate-800">{{ $active['valid_from'] ?? '—' }} <span class="text-slate-400 mx-1">→</span> {{ $active['valid_until'] ?? '—' }}</p>
                                            </div>
                                        </div>

                                        {{-- File details --}}
                                        <div>
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Attached File</p>
                                            <div class="flex items-start justify-between gap-4 p-3 rounded-xl border border-slate-200">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-slate-800 truncate" title="{{ $active['original_name'] }}">{{ $active['original_name'] }}</p>
                                                    <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($active['mime_type'], 20) }} · {{ number_format($active['size'] / 1024, 1) }} KB</p>
                                                </div>
                                                <a href="{{ $active['download_url'] }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-indigo-600 transition-colors" title="Download">
                                                    <i class="bx bx-download text-lg"></i>
                                                </a>
                                            </div>

                                            @if (Str::startsWith($active['mime_type'], 'image/'))
                                                <div class="mt-3 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center min-h-[150px]">
                                                    <img src="{{ $active['preview_url'] }}" class="max-w-full h-auto object-contain" alt="preview">
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Notes --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Review Notes (Optional)</label>
                                            <textarea wire:model.defer="review_notes" rows="3"
                                                class="w-full rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none p-3"
                                                placeholder="Remarks for the department (e.g. why it was rejected)"></textarea>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <div class="spinner-border text-indigo-500" role="status"></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Actions --}}
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                                <button @click="decisionPanelOpen = false" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>

                                @if ($uploadId)
                                    <div class="flex gap-2">
                                        <button wire:click="reject({{ $uploadId }}); decisionPanelOpen = false"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 px-4 py-2 text-sm font-semibold transition-all">
                                            <i class="bx bx-x text-lg"></i> Reject
                                        </button>
                                        <button wire:click="approve({{ $uploadId }}); decisionPanelOpen = false"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 text-sm font-semibold shadow-sm shadow-emerald-200 transition-all">
                                            <i class="bx bx-check text-lg"></i> Approve
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.delay class="fixed inset-0 z-[100] bg-white/40 backdrop-blur-[2px] flex items-center justify-center">
        <div class="glass-card px-6 py-4 flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm font-medium text-slate-600">Please wait…</span>
        </div>
    </div>
</div>

@push('scripts')
<style>
@keyframes slide-up {
    0% { transform: translate(-50%, 100%); opacity: 0; }
    100% { transform: translate(-50%, 0); opacity: 1; }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
@endpush
