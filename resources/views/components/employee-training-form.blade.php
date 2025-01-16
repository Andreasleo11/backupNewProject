<div>
    <form action="{{ $action }}" method="POST" class="needs-validation" novalidate>
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">Select an employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>
                        {{ $employee->Nama }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">Please select an employee.</div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required>{{ $description }}</textarea>
            <div class="invalid-feedback">Please provide a description.</div>
        </div>

        <div class="mb-3">
            <label for="last_training_at" class="form-label">Last Training Date</label>
            <input type="date" name="last_training_at" id="last_training_at" class="form-control"
                value="{{ $lastTrainingAt }}" required>
            <div class="invalid-feedback">Please provide a valid training date.</div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('employee_trainings.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new TomSelect('#employee_id', {
                create: false, // Disable creating new options
                maxItems: 1, // Limit to a single selection
                placeholder: 'Select an employee',
            });
        });
    </script>
</div>
