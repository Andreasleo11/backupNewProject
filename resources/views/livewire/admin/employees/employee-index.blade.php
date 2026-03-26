@php
    function sort_icon($field, $sortBy, $sortDirection) {
        if ($sortBy !== $field) return '';
        return $sortDirection === 'asc' ? '↑' : '↓';
    }
@endphp

<div class="max-w-7xl mx-auto space-y-6 py-6" x-data="{ showColumnsModal: false }">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                Employee Master
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Centralized employee database and HR information.
            </p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Optional: Column Visibility Toggle or Export could go here --}}
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-slate-200 bg-white/50 p-4 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
             <div class="relative w-full sm:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    class="block w-full rounded-xl border-0 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all"
                    placeholder="Search NIK, Name, or Position...">
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto">
                 <select wire:model.live="perPage" class="rounded-xl border-0 py-2.5 pl-3 pr-8 text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Data Grid with Horizontal Scroll --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                         <th class="sticky left-0 z-10 bg-slate-50 px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                             <button wire:click="sort_by('name')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Employee Info
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ sort_icon('name', $sortBy, $sortDirection) }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">NIK</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Department</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Position</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Status</th>
                         <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                             <button wire:click="sort_by('start_date')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Join Date
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ sort_icon('start_date', $sortBy, $sortDirection) }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            {{-- Sticky first column --}}
                            <td class="sticky left-0 z-10 bg-white group-hover:bg-slate-50/50 px-6 py-4 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-slate-900">{{ $employee->name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $employee->employment_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $employee->nik }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-700">{{ $employee->dept_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-800">{{ $employee->position }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $employee->branch === 'JAKARTA' ? 'bg-purple-50 text-purple-700 ring-purple-600/20' : 'bg-orange-50 text-orange-700 ring-orange-600/20' }}">
                                    {{ $employee->branch }}
                                </span>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap">
                                 @php
                                     $isActive = is_null($employee->end_date) || \Carbon\Carbon::parse($employee->end_date)->isFuture();
                                 @endphp
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-500/10">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Terminated
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $employee->start_date }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $employee->grade_code }} <span class="text-slate-400">({{ $employee->grade_level }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="mx-auto h-24 w-24 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900">No employees found</h3>
                                <p class="mt-1 text-slate-500">Try adjusting your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($employees->hasPages())
             <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
    
    <style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>
</div>
