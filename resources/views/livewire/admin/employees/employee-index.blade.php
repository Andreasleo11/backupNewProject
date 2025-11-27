<div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm space-y-4">
    {{-- Header + filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Employee master</h1>
            <!-- <p class="mt-0.5 text-xs text-slate-500">
                Manage department codes, names, branches and active status.
            </p> -->
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Search nik or name..."
                       class="w-56 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm
                              focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M11 19a8 8 0 1 0-8-8 8 8 0 0 0 8 8z" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">#</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">NIK</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Date Birth</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Gender</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Dept Code</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Position</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Start Date</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Branch</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Employement Type</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Employement Scheme</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Grade Code</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Grade Level</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Jatah Cuti Tahun</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Organization Structure</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">End Date</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 text-slate-500">
                            {{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $employee->nik }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $employee->name }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $employee->date_birth }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->gender }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->dept_code }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->position }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->start_date }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->branch }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->employment_type }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->employment_scheme }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->grade_code }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->grade_level }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->jatah_cuti_tahun }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->organization_structure }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $employee->end_date }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="px-4 py-6 text-center text-xs text-slate-500">
                            No employees found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
    @if ($employees->hasPages())
        <div class="px-4 py-3">
            {{ $employees->links() }}
        </div>
    @endif
</div>
