<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Audit Log</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">History of all locker assignments and releases.</p>
        </div>
        
        <a href="{{ route('lockers.dashboard') }}" 
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-700 text-sm font-bold border border-slate-200 shadow-sm hover:bg-slate-50 transition-all">
            <i class='bx bx-arrow-back text-lg'></i>
            Back to Dashboard
        </a>
    </div>

    {{-- Audit Table Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Locker</th>
                        <th class="px-6 py-4">Performed By</th>
                        <th class="px-6 py-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->logs as $log)
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-500">{{ $log->created_at->format('M d, Y H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $actionClasses = match($log->description) {
                                        'created' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'updated' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'deleted' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        default => 'bg-slate-50 text-slate-600 border-slate-100'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-tighter border {{ $actionClasses }}">
                                    {{ $log->description }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->subject && $log->subject_type === 'App\Models\Locker')
                                    <span class="text-sm font-bold text-slate-700">Locker {{ $log->subject->locker_number }}</span>
                                @elseif($log->subject && $log->subject_type === 'App\Models\LockerAssignment')
                                    <span class="text-sm font-bold text-slate-700">Locker {{ $log->subject->locker->locker_number ?? 'Unknown' }}</span>
                                @else
                                    <span class="text-sm font-medium text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-500">
                                        {{ substr($log->causer->name ?? 'SYS', 0, 2) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">{{ $log->causer->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs truncate text-[10px] text-slate-400 font-medium italic">
                                    @if($log->properties && count($log->properties))
                                        {{ json_encode($log->properties) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                No activity logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->logs->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>

</div>
