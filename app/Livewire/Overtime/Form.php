<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Services\OvertimeFormService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('new.layouts.app')]
class Form extends Component
{
    use WithFileUploads, WithOvertimeActions;

    public ?int $formId = null;

    public ?OvertimeForm $form = null;

    // Header fields
    public ?int $dept_id = null;

    public ?string $branch = null;

    public ?string $design = null;

    public int $is_after_hour = 1;

    public string $description = '';

    // Shared Default Fields (The UX Overhaul)
    public string $global_overtime_date = '';

    public string $global_start_date = '';

    public string $global_job_desc = '';

    public string $global_start_time = '';

    public string $global_end_date = '';

    public string $global_end_time = '';

    public string $global_break = '0';

    public string $global_remarks = '';

    // Date Override Settings
    public bool $show_date_override = false;

    public string $global_custom_end_date = '';

    // Detail rows
    public array $items = [];

    public array $removedDetailIds = [];

    public array $employees = [];

    // Bulk Management
    public bool $showBulkTray = false;

    // Local State
    public bool $isIntegrityChecked = false;

    public array $integrityResults = [];

    public array $validationErrors = [];

    public bool $isCheckingPayroll = false;

    public bool $syncEnabled = true;

    public bool $showTechnicalLogs = false;

    /**
     * Computed Property: List of departments for the selection.
     */
    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get();
    }

    /**
     * Computed Property: Recent job descriptions for the datalist suggestions.
     */
    #[Computed]
    public function recentJobs(): array
    {
        return OvertimeFormDetail::select('job_desc')
            ->whereNotNull('job_desc')
            ->distinct()
            ->limit(15)
            ->pluck('job_desc')
            ->toArray();
    }

    #[Computed]
    public function headcount(): int
    {
        return collect($this->items)->filter(fn ($i) => ! empty($i['nik']))->count();
    }

    #[Computed]
    public function totalNetHours(): string
    {
        $totalMinutes = 0;
        foreach ($this->items as $item) {
            if (empty($item['start_time']) || empty($item['end_time'])) {
                continue;
            }
            try {
                $start = \Carbon\Carbon::parse($item['start_time']);
                $end = \Carbon\Carbon::parse($item['end_time']);
                if ($end->lt($start)) {
                    $end->addDay();
                }

                $diff = $start->diffInMinutes($end);
                $net = $diff - (int) ($item['break'] ?? 0);
                if ($net > 0) {
                    $totalMinutes += $net;
                }
            } catch (\Exception $e) {
            }
        }

        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;

        return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
    }

    #[Computed]
    public function conflictCount(): int
    {
        return collect($this->items)->filter(fn ($i) => ($i['payroll_status'] ?? 'pending') === 'exists')->count();
    }

    public function mount(?int $id = null): void
    {
        $this->formId = $id;

        if ($this->formId) {
            $this->form = OvertimeForm::with(['details', 'department'])->findOrFail($id);
            $this->authorize('update', $this->form);

            if (! in_array(strtoupper($this->form->workflow_status), ['DRAFT', 'SUBMITTED', 'RETURNED'], true)) {
                abort(403, 'This form can no longer be edited.');
            }

            $this->dept_id = $this->form->dept_id;
            $this->branch = $this->form->branch;
            $this->design = $this->form->is_design !== null ? (string) $this->form->is_design : null;
            $this->is_after_hour = (int) $this->form->is_after_hour;
            $this->description = $this->form->description ?? '';

            $this->items = $this->form->details->map(fn ($d) => [
                'id' => $d->id,
                'nik' => $d->NIK ?? '',
                'name' => $d->name ?? '',
                'overtime_date' => $d->overtime_date?->format('Y-m-d') ?? '',
                'job_desc' => $d->job_desc ?? '',
                'start_date' => $d->start_date?->format('Y-m-d') ?? '',
                'start_time' => $d->start_time ? substr($d->start_time, 0, 5) : '',
                'end_date' => $d->end_date?->format('Y-m-d') ?? '',
                'end_time' => $d->end_time ? substr($d->end_time, 0, 5) : '',
                'break' => $d->break ?? '',
                'remarks' => $d->remarks ?? '',
                'payroll_status' => 'pending',
                'payroll_voucher_id' => $d->payroll_voucher_id ?? null,
            ])->toArray();

            if (count($this->items) > 0) {
                $first = $this->items[0];
                $this->global_overtime_date = $first['overtime_date'];
                $this->global_start_date = $first['start_date'];
                $this->global_job_desc = $first['job_desc'];
                $this->global_start_time = $first['start_time'];
                $this->global_end_date = $first['end_date'];
                $this->global_break = $first['break'];
                $this->global_remarks = $first['remarks'];

                // Check if end_date differs from overtime date (overtime extends to next day)
                $this->global_custom_end_date = $first['end_date'];
                $this->show_date_override = $first['end_date'] !== $first['overtime_date'] || $first['start_date'] !== $first['overtime_date'];
            }
        } else {
            $this->authorize('create', OvertimeForm::class);
            $user = auth()->user();
            $this->dept_id = $user->employee?->department?->id;
            $this->global_overtime_date = now()->format('Y-m-d');
            $this->global_start_date = now()->format('Y-m-d');
            $this->global_end_date = now()->format('Y-m-d');
        }

        $this->refreshEmployees();
    }

    public function addRow(): void
    {
        $this->addEmptyRow();
    }

    public function refreshEmployees(): void
    {
        if (! $this->dept_id) {
            $this->employees = [];

            return;
        }

        $this->employees = Employee::whereHas('department', fn ($q) => $q->where('id', $this->dept_id))
            ->whereNull('end_date') // Only active employees
            ->select('nik', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function rules(): array
    {
        $rules = [
            'branch' => 'required|in:Jakarta,Karawang',
            'design' => 'nullable|in:0,1',
            'is_after_hour' => 'required|in:0,1',
            'description' => 'nullable|string|max:500',
        ];

        if (! $this->formId) {
            $rules['dept_id'] = 'required|exists:departments,id';
        }

        $rules['items'] = 'required|array|min:1';
        foreach ($this->items as $i => $item) {
            $rules["items.$i.nik"] = ['required', 'string', Rule::exists('employees', 'nik')];
            $rules["items.$i.name"] = 'required|string';
            $rules["items.$i.overtime_date"] = 'required|date';
            $rules["items.$i.job_desc"] = 'required|string|max:500';
            $rules["items.$i.start_date"] = 'required|date';
            $rules["items.$i.start_time"] = 'required';
            $rules["items.$i.end_date"] = 'required|date|after_or_equal:items.' . $i . '.start_date';
            $rules["items.$i.end_time"] = 'required';
            $rules["items.$i.break"] = 'required|numeric|min:0|max:180';
            $rules["items.$i.remarks"] = 'nullable|string|max:250';

            // Add time validation only if dates are the same (same-day entries)
            if ($item['start_date'] === $item['end_date']) {
                $rules["items.$i.end_time"] .= '|after:items.' . $i . '.start_time';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'dept_id.required' => 'Department selection is required.',
            'branch.required' => 'Branch is required.',
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $messages["items.$i.nik.required"] = "Row $n: No employee chosen.";
            $messages["items.$i.nik.exists"] = "Row $n: Identity invalid.";
            $messages["items.$i.job_desc.required"] = "Row $n: Task description is required.";
            $messages["items.$i.start_time.required"] = "Row $n: Start time missing.";
            $messages["items.$i.end_time.required"] = "Row $n: End time missing.";
            $messages["items.$i.end_time.after"] = "Row $n: End time must be after start time (same day entries only).";
            $messages["items.$i.end_date.after_or_equal"] = "Row $n: End date invalid.";
        }

        return $messages;
    }

    protected function syncErrors(): void
    {
        $this->validationErrors = $this->getErrorBag()->toArray();
    }

    public function updated($field): void
    {
        $this->resetErrorBag($field);
        $this->resetIntegrity();

        if (str_starts_with($field, 'items.')) {
            $this->validateOnly($field);

            // BRIDGE: If name is typed, we must also validate NIK to show "Selection Required" error
            if (str_ends_with($field, '.name')) {
                $nikField = str_replace('.name', '.nik', $field);
                $this->validateOnly($nikField);
            }

            if (str_ends_with($field, '.start_time')) {
                $endField = str_replace('.start_time', '.end_time', $field);
                $this->validateOnly($endField);
            }
        }
    }

    public function updatedDeptId(): void
    {
        if ($this->formId) {
            $this->refreshEmployees();

            return;
        }

        $this->design = null;
        foreach ($this->items as $i => $item) {
            $this->items[$i]['nik'] = '';
            $this->items[$i]['name'] = '';
        }
        $this->refreshEmployees();
    }

    public function validateStage0(): bool
    {
        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch' => 'required|in:Jakarta,Karawang',
            'is_after_hour' => 'required|in:0,1',
        ];

        $this->validate($rules, [
            'dept_id.required' => 'A department selection is required.',
            'branch.required' => 'Please choose a work location.',
        ]);

        return true;
    }

    public function validateStep1(): bool
    {


        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch' => 'required|string',
            'global_overtime_date' => 'required|date',
            'global_start_date' => 'required|date',
            'global_job_desc' => 'required|string|min:3',
            'global_start_time' => 'required',
            'global_end_time' => 'required',
            'global_break' => 'required|numeric|min:0|max:180',
        ];

        // Add time validation only for same-day mode
        if (! $this->show_date_override) {
            $rules['global_end_time'] .= '|after:global_start_time';
        }

        // Add custom end date validation for multi-day mode
        if ($this->show_date_override) {
            $rules['global_custom_end_date'] = 'required|date|after_or_equal:global_overtime_date';
        }

        $messages = [
            'dept_id.required' => 'A department selection is required.',
            'global_overtime_date.required' => 'Please set an effectivity date.',
            'global_start_date.required' => 'Please set a start date.',
            'global_job_desc.required' => 'The main objective is required.',
            'global_start_time.required' => 'Start time is required.',
            'global_end_time.required' => 'End time is required.',
            'global_end_time.after' => 'The end time must be later than the start time.',
            'global_custom_end_date.required' => 'End date is required for multi-day overtime.',
            'global_custom_end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];

        $this->validate($rules, $messages);

        // 2. Advanced structural validation (moved from runIntegrityCheck)
        try {
            $startDateTime = \Carbon\Carbon::parse($this->global_start_date . ' ' . $this->global_start_time);
            $endDateTime = \Carbon\Carbon::parse(
                ($this->show_date_override ? $this->global_custom_end_date : $this->global_start_date) . ' ' . $this->global_end_time
            );
            $overtimeDate = \Carbon\Carbon::parse($this->global_overtime_date);

            // End time must be after start time (additional check for multi-day)
            if ($endDateTime->lte($startDateTime)) {
                $this->addError('global_end_time', 'End time must be after start time.');
            }

            // Work period should not exceed 24 hours
            $duration = $startDateTime->diffInHours($endDateTime, false);
            if ($duration > 24) {
                $this->addError('global_end_time', 'Work period cannot exceed 24 hours.');
            } elseif ($duration <= 0) {
                $this->addError('global_end_time', 'Work period must be at least 1 hour.');
            }

            // Overtime date should be within reasonable range of work dates
            $workStartDate = \Carbon\Carbon::parse($this->global_start_date);
            $workEndDate = $this->show_date_override ?
                \Carbon\Carbon::parse($this->global_custom_end_date) :
                $workStartDate;

            // Overtime date should not be more than 30 days in the past or 7 days in the future
            $today = \Carbon\Carbon::today();
            if ($overtimeDate->lt($today->copy()->subDays(30))) {
                $this->addError('global_overtime_date', 'Overtime date cannot be more than 30 days in the past.');
            } elseif ($overtimeDate->gt($today->copy()->addDays(7))) {
                $this->addError('global_overtime_date', 'Overtime date cannot be more than 7 days in the future.');
            }

            // Overtime date should be within work period ± 3 days
            if ($overtimeDate->lt($workStartDate->copy()->subDays(3)) ||
                $overtimeDate->gt($workEndDate->copy()->addDays(3))) {
                $this->addError('global_overtime_date', 'Overtime date should be within 3 days of work dates.');
            }

            // Break time validation
            if (isset($this->global_break) && $this->global_break !== '') {
                $breakHours = floatval($this->global_break);
                if ($breakHours < 0) {
                    $this->addError('global_break', 'Break time cannot be negative.');
                } elseif ($breakHours >= $duration) {
                    $this->addError('global_break', 'Break time must be less than total work hours.');
                }
            }

        } catch (\Exception $e) {
            $this->addError('global_start_time', 'Invalid date/time format.');
        }

        // Check if any errors were added during advanced validation
        if ($this->getErrorBag()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    public function toggleEmployee(string $nik): void
    {
        // 1. Check if already in items
        $existingIndex = null;
        foreach ($this->items as $i => $item) {
            if ($item['nik'] === $nik) {
                $existingIndex = $i;
                break;
            }
        }

        // 2. If exists -> REMOVE
        if ($existingIndex !== null) {
            $this->removeRow($existingIndex);

            // If we removed the last row, the parent removeRow logic might add back an empty one
            // We want to ensure there's at least one row, which removeRow handles.
            return;
        }

        // 3. If NOT exists -> ADD
        $emp = Employee::where('nik', $nik)->first();
        if (! $emp) {
            return;
        }

        // Replace empty placeholder if it's the only row
        $startDate = $this->show_date_override && $this->global_start_date
            ? $this->global_start_date
            : $this->global_overtime_date;

        $endDate = $this->show_date_override && $this->global_custom_end_date
            ? $this->global_custom_end_date
            : $this->global_overtime_date;

        if (count($this->items) === 1 && empty($this->items[0]['nik'])) {
            $this->items[0]['nik'] = $emp->nik;
            $this->items[0]['name'] = $emp->name;
            $this->items[0]['overtime_date'] = $this->global_overtime_date;
            $this->items[0]['start_date'] = $startDate;
            $this->items[0]['end_date'] = $endDate;
            $this->resetErrorBag('items.0.nik'); // CLEAR RECENT SUBMISSION ERRORS
        } else {
            $this->items[] = [
                'id' => null,
                'nik' => $emp->nik,
                'name' => $emp->name,
                'overtime_date' => $this->global_overtime_date,
                'job_desc' => $this->global_job_desc,
                'start_date' => $startDate,
                'start_time' => $this->global_start_time,
                'end_date' => $endDate,
                'end_time' => $this->global_end_time,
                'break' => $this->global_break,
                'remarks' => $this->global_remarks,
                'payroll_status' => 'pending',
                'payroll_voucher_id' => null,
                'is_imported' => false,
            ];
        }

        $this->resetIntegrity();
    }

    public function resetIntegrity(): void
    {
        $this->isIntegrityChecked = false;
        $this->integrityResults = [];
    }

    /**
     * Run comprehensive integrity checks on the overtime form
     *
     * Checks performed:
     * 1. Structural validation (employee selection + individual row data validation)
     * 2. Local conflicts (within-form duplicates/overlaps, database conflicts)
     * 3. Payroll system conflicts
     */
    public function runIntegrityCheck(): void
    {
        $this->isIntegrityChecked = false;

        // Clear previous validation errors
        $this->resetErrorBag();

        $this->integrityResults = [
            'structural' => 'loading',
            'local' => 'pending',
            'payroll' => 'pending',
        ];

        // 1. Structural Check - Roster data validation
        $hasStructuralErrors = false;
        foreach ($this->items as $i => $item) {
            // Basic employee selection
            if (empty($item['nik'])) {
                $this->addError("items.$i.nik", 'Employee selection required.');
                $hasStructuralErrors = true;
                continue;
            }

            // Individual row structural validation (dates, times, breaks)
            try {
                $startDateTime = \Carbon\Carbon::parse($item['start_date'] . ' ' . $item['start_time']);
                $endDateTime = \Carbon\Carbon::parse($item['end_date'] . ' ' . $item['end_time']);
                $overtimeDate = \Carbon\Carbon::parse($item['overtime_date']);

                // End time must be after start time
                if ($endDateTime->lte($startDateTime)) {
                    $this->addError("items.$i.nik", 'End time must be after start time.');
                    $hasStructuralErrors = true;
                }

                // Work period should not exceed 24 hours
                $duration = $startDateTime->diffInHours($endDateTime, false);
                if ($duration > 24) {
                    $this->addError("items.$i.nik", 'Work period cannot exceed 24 hours.');
                    $hasStructuralErrors = true;
                } elseif ($duration <= 0) {
                    $this->addError("items.$i.nik", 'Work period must be at least 1 hour.');
                    $hasStructuralErrors = true;
                }

                // Overtime date should be within reasonable range of work dates
                $workStartDate = \Carbon\Carbon::parse($item['start_date']);
                $workEndDate = \Carbon\Carbon::parse($item['end_date']);

                // Overtime date should not be more than 30 days in the past or 7 days in the future
                $today = \Carbon\Carbon::today();
                if ($overtimeDate->lt($today->copy()->subDays(30))) {
                    $this->addError("items.$i.nik", 'Overtime date cannot be more than 30 days in the past.');
                    $hasStructuralErrors = true;
                } elseif ($overtimeDate->gt($today->copy()->addDays(7))) {
                    $this->addError("items.$i.nik", 'Overtime date cannot be more than 7 days in the future.');
                    $hasStructuralErrors = true;
                }

                // Overtime date should be within work period ± 3 days
                if ($overtimeDate->lt($workStartDate->copy()->subDays(3)) ||
                    $overtimeDate->gt($workEndDate->copy()->addDays(3))) {
                    $this->addError("items.$i.nik", 'Overtime date should be within 3 days of work dates.');
                    $hasStructuralErrors = true;
                }

                // Break time validation
                if (isset($item['break']) && $item['break'] !== '' && $item['break'] !== null) {
                    $breakHours = floatval($item['break']);
                    if ($breakHours < 0) {
                        $this->addError("items.$i.nik", 'Break time cannot be negative.');
                        $hasStructuralErrors = true;
                    } elseif ($breakHours >= $duration) {
                        $this->addError("items.$i.nik", 'Break time must be less than total work hours.');
                        $hasStructuralErrors = true;
                    }
                }

            } catch (\Exception $e) {
                $this->addError("items.$i.nik", 'Invalid date/time format.');
                $hasStructuralErrors = true;
            }
        }

        if ($hasStructuralErrors) {
            $this->integrityResults['structural'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Please fix the highlighted data errors before submitting.');
            return;
        }

        $this->integrityResults['structural'] = 'passed';

        // 2. Local Conflict Check - Within-form and database conflicts
        $this->integrityResults['local'] = 'loading';
        $hasLocalConflict = false;

        // 2a. Check for exact time period duplicates within this form
        $timeSignatures = [];
        foreach ($this->items as $i => $item) {
            if (empty($item['nik']) || empty($item['start_date']) || empty($item['start_time']) || empty($item['end_date']) || empty($item['end_time'])) {
                continue;
            }

            $timeSig = $item['nik'] . '|' . $item['start_date'] . '|' . $item['start_time'] . '|' . $item['end_date'] . '|' . $item['end_time'];

            if (isset($timeSignatures[$timeSig])) {
                $this->addError("items.$i.nik", 'Duplicate time period for this employee within this form.');
                $hasLocalConflict = true;
            } else {
                $timeSignatures[$timeSig] = true;
            }
        }



        // 2b. Check for overlapping time periods for the same employee within this form
        $byNik = collect($this->items)->groupBy('nik');
        foreach ($byNik as $nik => $rows) {
            if ($rows->count() < 2) {
                continue;
            }

            // Sort by start time
            $sorted = $rows->sortBy(fn ($r) => $r['start_date'] . ' ' . $r['start_time']);
            $prev = null;

            foreach ($sorted as $currentIndex => $current) {
                if ($prev) {
                    try {
                        $prevEnd = \Carbon\Carbon::parse($prev['end_date'] . ' ' . $prev['end_time']);
                        $currStart = \Carbon\Carbon::parse($current['start_date'] . ' ' . $current['start_time']);

                        if ($currStart->lt($prevEnd)) {
                            // Find the actual index in $this->items by comparing key fields
                            foreach ($this->items as $idx => $item) {
                                if ($item['nik'] === $current['nik'] &&
                                    $item['start_date'] === $current['start_date'] &&
                                    $item['start_time'] === $current['start_time'] &&
                                    $item['end_date'] === $current['end_date'] &&
                                    $item['end_time'] === $current['end_time']) {
                                    $this->addError("items.$idx.nik", 'Overlapping time period with another entry for this employee.');
                                    $hasLocalConflict = true;
                                    break 2; // Break out of both inner loops
                                }
                            }
                            break; // Only report once per employee
                        }
                    } catch (\Exception $e) {
                        // Skip invalid date parsing
                        continue;
                    }
                }
                $prev = $current;
            }
        }

        // 2c. Check against existing database records for conflicts
        foreach ($this->items as $i => $item) {
            // 2c.i Check for exact overtime_date match (existing logic)
            $existsExact = OvertimeFormDetail::query()
                ->where('NIK', $item['nik'])
                ->where('overtime_date', $item['overtime_date'])
                ->whereNull('payroll_voucher_id') // Not yet pushed
                ->when($this->formId, fn ($q) => $q->where('header_id', '!=', $this->formId)) // Edit Mode Safety
                ->exists();

            if ($existsExact) {
                $this->addError("items.$i.nik", 'Already proposed in another draft/local form.');
                $hasLocalConflict = true;
                continue; // Skip the overlap check if we already have an exact match
            }

            // 2c.ii Check for time overlaps with existing records (same employee, overlapping work periods)
            try {
                $newStart = \Carbon\Carbon::parse($item['start_date'] . ' ' . $item['start_time']);
                $newEnd = \Carbon\Carbon::parse($item['end_date'] . ' ' . $item['end_time']);

                $overlaps = OvertimeFormDetail::query()
                    ->where('NIK', $item['nik'])
                    ->whereNull('payroll_voucher_id') // Not yet pushed
                    ->when($this->formId, fn ($q) => $q->where('header_id', '!=', $this->formId)) // Edit Mode Safety
                    ->whereRaw('? < CONCAT(end_date, " ", end_time)', [$newStart->format('Y-m-d H:i:s')])
                    ->whereRaw('CONCAT(start_date, " ", start_time) < ?', [$newEnd->format('Y-m-d H:i:s')])
                    ->exists();

                if ($overlaps) {
                    $this->addError("items.$i.nik", 'Work hours overlap with existing overtime records for this employee.');
                    $hasLocalConflict = true;
                }
            } catch (\Exception $e) {
                // Skip overlap check if date parsing fails
                continue;
            }
        }

        if ($hasLocalConflict) {
            $this->integrityResults['local'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Terdapat baris yang sudah ada di Database Lokal.');

            return;
        }
        $this->integrityResults['local'] = 'passed';

        // 3. Payroll System Check - External validation against JPayroll
        $this->integrityResults['payroll'] = 'loading';
        $this->checkPayrollStatus(silent: true);

        if (collect($this->items)->contains('payroll_status', 'exists')) {
            $this->integrityResults['payroll'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Terdapat baris yang sudah ada di JPayroll.');

            return;
        }

        $this->integrityResults['payroll'] = 'passed';
        $this->isIntegrityChecked = true;
    }

    public function submit(): mixed
    {
        if (! $this->isIntegrityChecked) {
            $this->runIntegrityCheck();
            if (! $this->isIntegrityChecked) {
                return null;
            }
        }

        DB::beginTransaction();
        try {
            if ($this->formId) {
                $this->form->update([
                    'branch' => $this->branch,
                    'is_design' => $this->design,
                    'is_after_hour' => $this->is_after_hour,
                    'description' => $this->description ?: null,
                ]);

                if ($this->removedDetailIds) {
                    OvertimeFormDetail::whereIn('id', $this->removedDetailIds)->where('header_id', $this->form->id)->delete();
                }

                foreach ($this->items as $item) {
                    $detailData = [
                        'header_id' => $this->form->id,
                        'NIK' => $item['nik'],
                        'name' => $item['name'],
                        'overtime_date' => $item['overtime_date'],
                        'job_desc' => $item['job_desc'],
                        'start_date' => $item['start_date'],
                        'start_time' => $item['start_time'],
                        'end_date' => $item['end_date'],
                        'end_time' => $item['end_time'],
                        'break' => $item['break'],
                        'remarks' => $item['remarks'] ?? '',
                    ];

                    if (! empty($item['id'])) {
                        OvertimeFormDetail::where('id', $item['id'])->where('header_id', $this->form->id)->update($detailData);
                    } else {
                        OvertimeFormDetail::create($detailData);
                    }
                }

                DB::commit();
                $this->dispatch('flash', type: 'success', message: 'Overtime form updated successfully.');

                return redirect()->route('overtime.detail', $this->form->id);

            } else {
                $service = app(OvertimeFormService::class);
                $header = $service->create(collect($this->validate()));

                DB::commit();
                $this->dispatch('flash', type: 'success', message: 'Overtime form created successfully.');

                return redirect()->route('overtime.detail', $header->id);
            }

        } catch (Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Overtime Form Error: ' . $e->getMessage());
            $this->dispatch('flash', type: 'error', message: 'Failed to save form: ' . $e->getMessage());

            return null;
        }
    }

    public function render()
    {
        $this->syncErrors();

        // Check for Moulding dept specific logic if needed
        $isMoulding = (bool) Department::find($this->dept_id)?->name === 'MOULDING';

        return view('livewire.overtime.form', [
            'departments' => Department::orderBy('name')->get(),
            'recentJobs' => $this->recentJobs,
            'isMoulding' => $isMoulding,
            'canOverrideDept' => true, // Standard for high-density entry
        ]);
    }
}
