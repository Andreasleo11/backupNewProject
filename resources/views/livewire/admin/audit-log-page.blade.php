<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold tracking-tight">System Audit Log ✨</h1>
            <p class="text-sm text-slate-500 mt-1">Review all system activities, data modifications, and user actions.</p>
        </div>
        
        <!-- Search and Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" class="form-input w-full pl-9 py-2 rounded-xl border-slate-200 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Search logs..." />
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Date & Time</th>
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Event</th>
                        <th class="px-6 py-4 font-semibold">Subject</th>
                        <th class="px-6 py-4 font-semibold">Properties (Changes)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-slate-900">{{ $activity->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $activity->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($activity->causer)
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $activity->causer->name }}</div>
                                        <div class="text-xs text-slate-500">{{ class_basename($activity->causer_type) }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">System</div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-widest
                                @if($activity->event === 'created') bg-emerald-100 text-emerald-700
                                @elseif($activity->event === 'updated') bg-blue-100 text-blue-700
                                @elseif($activity->event === 'deleted') bg-rose-100 text-rose-700
                                @else bg-slate-100 text-slate-700 @endif
                            ">
                                {{ $activity->event }}
                            </span>
                            <div class="text-xs text-slate-500 mt-1.5">{{ $activity->description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($activity->subject_type)
                                <div class="text-sm font-medium text-slate-900">{{ class_basename($activity->subject_type) }}</div>
                                <div class="text-[11px] font-mono text-slate-500 mt-0.5">ID: {{ $activity->subject_id }}</div>
                            @else
                                <span class="text-slate-400 italic text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if(!empty($activity->properties) && count($activity->properties) > 0)
                                <div class="max-h-32 overflow-y-auto custom-scrollbar-thin bg-slate-900 text-emerald-400 p-3 rounded-xl text-[10px] font-mono whitespace-pre-wrap max-w-sm shadow-inner">
{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">No recorded properties</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="text-slate-300 mb-3"><i class="bi bi-shield-lock text-4xl"></i></div>
                            <h3 class="text-lg font-bold text-slate-800">No logs found</h3>
                            <p class="text-slate-500 text-sm mt-1">There are no audit logs matching your search criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>
