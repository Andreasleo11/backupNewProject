<div>
    <form action="{{ $action }}" method="POST" class="space-y-5">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        {{-- Employee --}}
        <div class="space-y-1">
            <label for="employee_id" class="text-sm font-medium text-slate-700">
                Employee
            </label>
            <select
                name="employee_id"
                id="employee_id"
                class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                required
            >
                <option value="">Select an employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (string) $employeeId === (string) $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div class="space-y-1">
            <label for="description" class="text-sm font-medium text-slate-700">
                Description
            </label>
            <textarea
                name="description"
                id="description"
                rows="4"
                class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                required
            >{{ old('description', $description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last training date --}}
        <div class="space-y-1">
            <label for="last_training_at" class="text-sm font-medium text-slate-700">
                Last Training Date
            </label>
            <input
                type="date"
                name="last_training_at"
                id="last_training_at"
                value="{{ old('last_training_at', $lastTrainingAt) }}"
                class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                required
            >
            @error('last_training_at')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Evaluated --}}
        <div class="space-y-1">
            <span class="text-sm font-medium text-slate-700">
                Evaluated
            </span>
            <div class="flex items-center gap-6 mt-1 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input
                        class="w-4 h-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        type="radio"
                        name="evaluated"
                        id="evaluated_yes"
                        value="1"
                        {{ isset($evaluated) && (int) $evaluated === 1 ? 'checked' : '' }}
                    >
                    <span>Yes</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input
                        class="w-4 h-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        type="radio"
                        name="evaluated"
                        id="evaluated_no"
                        value="0"
                        {{ isset($evaluated) && (int) $evaluated === 0 ? 'checked' : '' }}
                    >
                    <span>No</span>
                </label>
            </div>
            @error('evaluated')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('employee_trainings.index') }}"
               class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                {{ $submitLabel }}
            </button>
        </div>
    </form>

    {{-- TomSelect init (reuse your existing JS, just without Bootstrap assumptions) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.TomSelect) {
                new TomSelect('#employee_id', {
                    create: false,
                    maxItems: 1,
                    placeholder: 'Select an employee',
                });
            }
        });
    </script>
</div>