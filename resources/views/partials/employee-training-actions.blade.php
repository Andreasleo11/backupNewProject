<td class="whitespace-nowrap text-right">
    <div class="inline-flex items-center gap-2">

        {{-- Show --}}
        <a href="{{ route('employee_trainings.show', $training->id) }}"
           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Show
        </a>

        {{-- Edit --}}
        <a href="{{ route('employee_trainings.edit', $training->id) }}"
           class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-800 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1">
            Edit
        </a>

        {{-- Delete --}}
        <form action="{{ route('employee_trainings.destroy', $training->id) }}"
              method="POST"
              onsubmit="return confirm('Are you sure you want to delete this training record?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-1">
                Delete
            </button>
        </form>

        {{-- Evaluate (only if not yet evaluated) --}}
        @if (! $training->evaluated)
            <form action="{{ route('employee_trainings.evaluate', $training->id) }}"
                  method="POST"
                  onsubmit="return confirm('Mark this training as evaluated?');">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800 shadow-sm hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1">
                    Evaluate
                </button>
            </form>
        @endif

    </div>
</td>
