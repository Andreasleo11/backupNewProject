@php
    use Carbon\Carbon;
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest mb-4">
                <i class='bx bx-history text-xs'></i>
                System Integrity
            </div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight">
                Audit Timeline
            </h1>
            <p class="text-sm text-slate-500 mt-2 font-medium">
                A complete, tamper-proof record of every locker action and incident.
            </p>
        </div>

        <a href="{{ route('lockers.dashboard') }}"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white text-slate-700 text-sm font-bold border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
            <i class='bx bx-grid-alt text-lg'></i>
            Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="sticky top-4 z-20 bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-sm border border-slate-100 p-2 mb-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
            {{-- Search --}}
            <div class="relative group">
                <i class='bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg group-focus-within:text-indigo-500 transition-colors'></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search actions..."
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border-none rounded-[1.5rem] text-sm focus:ring-2 focus:ring-indigo-500/20 transition-all"
                >
            </div>

            {{-- Type --}}
            <select
                wire:model.live="typeFilter"
                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-[1.5rem] text-sm focus:ring-2 focus:ring-indigo-500/20 transition-all"
            >
                <option value="">All Categories</option>
                <option value="App\Models\Locker">Unit Management</option>
                <option value="App\Models\LockerAssignment">Assignments</option>
                <option value="App\Models\LockerIncident">Incidents & Fines</option>
            </select>

            {{-- Date Range --}}
            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="flex-1 px-4 py-3.5 bg-slate-50 border-none rounded-[1.5rem] text-xs focus:ring-2 focus:ring-indigo-500/20 transition-all"
                >
                <span class="text-slate-300 font-bold">→</span>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="flex-1 px-4 py-3.5 bg-slate-50 border-none rounded-[1.5rem] text-xs focus:ring-2 focus:ring-indigo-500/20 transition-all"
                >
            </div>

            {{-- Reset --}}
            <button
                wire:click="resetFilters"
                class="px-6 py-3.5 bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest rounded-[1.5rem] hover:bg-slate-200 transition-all"
            >
                Clear
            </button>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="relative">

        {{-- Main Vertical Line --}}
        <div class="hidden md:block absolute left-4 top-0 bottom-0 w-px bg-slate-200"></div>

        @php
            $groupedLogs = $this->logs->groupBy(
                fn($log) => $log->created_at->format('Y-m-d')
            );
        @endphp

        <div class="space-y-12">
            @forelse($groupedLogs as $date => $dayLogs)
                {{-- Date Group --}}
                <section>
                    {{-- Date Heading --}}
                    <div class="relative flex items-center mb-8">
                        <div class="hidden md:block absolute left-4 -translate-x-1/2 w-4 h-4 rounded-full border-4 border-white bg-indigo-500 shadow"></div>
                        <div class="md:ml-12">
                            <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">
                                @if(Carbon::parse($date)->isToday())
                                    Today
                                @elseif(Carbon::parse($date)->isYesterday())
                                    Yesterday
                                @else
                                    {{ Carbon::parse($date)->format('F d, Y') }}
                                @endif
                            </h2>
                        </div>
                    </div>

                    {{-- Logs --}}
                    <div class="space-y-6">
                        @foreach($dayLogs as $log)
                            @php
                                $subjectType = $log->subject_type;
                                $message = ucfirst($log->description);
                                $lockerNumber = null;
                                $icon = 'bx-history';
                                $iconBg = 'bg-slate-100 text-slate-600';
                                if ($subjectType === 'App\\Models\\LockerAssignment') {
                                    $lockerNumber =
                                        $log->subject?->locker?->locker_number
                                        ?? data_get($log->properties, 'attributes.locker_id');
                                    $employee =
                                        $log->subject?->employee?->name
                                        ?? 'Unknown';
                                    if ($log->description === 'created') {
                                        $message = "Assigned to {$employee}";
                                        $icon = 'bx-user-plus';
                                        $iconBg = 'bg-blue-500 text-white';
                                    } elseif (
                                        $log->description === 'updated' &&
                                        (
                                            data_get($log->properties, 'attributes.released_at') ||
                                            data_get($log->properties, 'changes.released_at')
                                        )
                                    ) {
                                        $message = "Released from usage";
                                        $icon = 'bx-log-out-circle';
                                        $iconBg = 'bg-emerald-500 text-white';
                                    } else {
                                        $message = ucfirst($log->description) . ' assignment';
                                        $icon = 'bx-edit-alt';
                                        $iconBg = 'bg-amber-500 text-white';
                                    }
                                } elseif ($subjectType === 'App\\Models\\LockerIncident') {

                                    $lockerNumber =
                                        $log->subject?->assignment?->locker?->locker_number;
                                    $type =
                                        data_get($log->properties, 'attributes.type')
                                        ?? $log->subject?->type
                                        ?? 'incident';
                                    if ($log->description === 'created') {
                                        $fine =
                                            data_get($log->properties, 'attributes.fine_amount', 0);
                                        $message =
                                            ucfirst(str_replace('_', ' ', $type))
                                            . ' reported';
                                        if ($fine > 0) {
                                            $message .= ' (Fine: Rp ' . number_format($fine, 0) . ')';
                                        }
                                        $icon = 'bx-error';
                                        $iconBg = 'bg-rose-500 text-white';
                                    } elseif (
                                        $log->description === 'updated' &&
                                        data_get($log->properties, 'attributes.is_paid')
                                    ) {
                                        $message = 'Fine marked as paid';
                                        $icon = 'bx-check-double';
                                        $iconBg = 'bg-emerald-500 text-white';
                                    }
                                } elseif ($subjectType === 'App\\Models\\Locker') {
                                    $lockerNumber =
                                        $log->subject?->locker_number;
                                    $message =
                                        'Locker unit ' . $log->description;
                                    $icon = 'bx-box';
                                    $iconBg = 'bg-slate-800 text-white';

                                }
                            @endphp

                            {{-- Timeline Item --}}
                            <article
                                x-data="{ open: false }"
                                class="relative flex gap-4 md:gap-8 group"
                            >
                                {{-- Desktop Timeline Icon --}}
                                <div class="hidden md:flex relative z-10">
                                    <div class="w-8 flex justify-center">
                                        <div class="w-8 h-8 rounded-xl {{ $iconBg }} flex items-center justify-center shadow-lg border-4 border-white group-hover:scale-110 transition-transform">
                                            <i class='bx {{ $icon }} text-base'></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card --}}
                                <div
                                    class="flex-1 bg-white rounded-3xl border border-slate-100 p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all"
                                >
                                    {{-- Top --}}
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 cursor-pointer"
                                        @click="open = !open"
                                    >
                                        <div class="flex gap-4">
                                            {{-- Mobile Icon --}}
                                            <div class="md:hidden w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                                                <i class='bx {{ $icon }} text-lg'></i>
                                            </div>

                                            <div>
                                                {{-- Time --}}
                                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                        {{ $log->created_at->format('H:i') }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-300">•</span>
                                                    <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">
                                                        {{ $log->created_at->diffForHumans() }}
                                                    </span>
                                                </div>

                                                {{-- Message --}}
                                                <h3 class="text-base font-bold text-slate-800 leading-tight">
                                                    {{ $message }}
                                                </h3>

                                                {{-- Meta --}}
                                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                                    @if($lockerNumber)
                                                        <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                                            <i class='bx bx-box text-slate-400'></i>
                                                            <span class="text-[10px] font-black text-slate-600 uppercase">
                                                                {{ $lockerNumber }}
                                                            </span>
                                                        </div>
                                                    @endif

                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                                        <i class='bx bx-user text-slate-400'></i>
                                                        <span class="text-[10px] font-bold text-slate-600">
                                                            {{ $log->causer?->name ?? 'System' }}
                                                        </span>
                                                    </div>

                                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest border border-slate-100 text-slate-400">
                                                        {{ $log->description }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Chevron --}}
                                        <div class="flex items-center gap-2 text-slate-300 group-hover:text-indigo-400 transition-colors">
                                            <span class="hidden sm:block text-[10px] font-bold uppercase">
                                                Details
                                            </span>

                                            <i
                                                class='bx bx-chevron-down text-xl transition-transform duration-300'
                                                :class="open ? 'rotate-180' : ''"
                                            ></i>
                                        </div>
                                    </div>

                                    {{-- Expand --}}
                                    <div
                                        x-show="open"
                                        x-collapse
                                        x-cloak
                                    >
                                        <div class="mt-6 pt-6 border-t border-slate-100">
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                                {{-- JSON --}}
                                                <div>
                                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                                        Technical Details
                                                    </h4>

                                                    <div class="bg-slate-900 rounded-2xl overflow-hidden">
                                                        <pre class="p-4 text-[10px] text-emerald-400 font-mono overflow-x-auto leading-relaxed">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                    </div>
                                                </div>

                                                {{-- Verification --}}
                                                <div class="flex flex-col justify-end">

                                                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">

                                                        <p class="text-[10px] font-bold text-indigo-700 leading-relaxed italic">
                                                            This event was recorded via the
                                                            <span class="font-black">
                                                                {{ $log->log_name }}
                                                            </span>
                                                            channel and verified by the system.
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                {{-- Empty State --}}
                <div class="py-20 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mx-auto mb-6">
                        <i class='bx bx-ghost text-5xl'></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">
                        No events found
                    </h3>
                    <p class="text-slate-500 mt-2">
                        Adjust your filters to broaden the search.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    @if ($this->logs->hasPages())
        <div class="mt-12">
            {{ $this->logs->links() }}
        </div>
    @endif

</div>