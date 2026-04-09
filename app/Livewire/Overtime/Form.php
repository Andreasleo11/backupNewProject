<?php

namespace App\Livewire\Overtime;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Services\OvertimeFormService;
use App\Livewire\Overtime\WithOvertimeActions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
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
    public string $global_job_desc = '';
    public string $global_start_time = '';
    public string $global_end_date = '';
    public string $global_end_time = '';
    public string $global_break = '0';
    public string $global_remarks = '';

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
        return collect($this->items)->filter(fn($i) => !empty($i['nik']))->count();
    }

    #[Computed]
    public function totalNetHours(): string
    {
        $totalMinutes = 0;
        foreach ($this->items as $item) {
            if (empty($item['start_time']) || empty($item['end_time'])) continue;
            try {
                $start = \Carbon\Carbon::parse($item['start_time']);
                $end = \Carbon\Carbon::parse($item['end_time']);
                if ($end->lt($start)) $end->addDay();
                
                $diff = $start->diffInMinutes($end);
                $net = $diff - (int)($item['break'] ?? 0);
                if ($net > 0) $totalMinutes += $net;
            } catch (\Exception $e) {}
        }

        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;
        return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
    }

    #[Computed]
    public function conflictCount(): int
    {
        return collect($this->items)->filter(fn($i) => ($i['payroll_status'] ?? 'pending') === 'exists')->count();
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

            $this->dept_id      = $this->form->dept_id;
            $this->branch       = $this->form->branch;
            $this->design       = $this->form->is_design !== null ? (string) $this->form->is_design : null;
            $this->is_after_hour = (int) $this->form->is_after_hour;
            $this->description  = $this->form->description ?? '';

            $this->items = $this->form->details->map(fn ($d) => [
                'id'            => $d->id,
                'nik'           => $d->NIK ?? '',
                'name'          => $d->name ?? '',
                'overtime_date' => $d->overtime_date?->format('Y-m-d') ?? '',
                'job_desc'      => $d->job_desc ?? '',
                'start_date'    => $d->start_date?->format('Y-m-d') ?? '',
                'start_time'    => $d->start_time ? substr($d->start_time, 0, 5) : '',
                'end_date'      => $d->end_date?->format('Y-m-d') ?? '',
                'end_time'      => $d->end_time ? substr($d->end_time, 0, 5) : '',
                'break'         => $d->break ?? '',
                'remarks'       => $d->remarks ?? '',
                'payroll_status' => 'pending', 
                'payroll_voucher_id' => $d->payroll_voucher_id ?? null,
            ])->toArray();

            if (count($this->items) > 0) {
                $first = $this->items[0];
                $this->global_overtime_date = $first['overtime_date'];
                $this->global_job_desc      = $first['job_desc'];
                $this->global_start_time    = $first['start_time'];
                $this->global_end_date      = $first['end_date'];
                $this->global_end_time      = $first['end_time'];
                $this->global_break         = $first['break'];
                $this->global_remarks       = $first['remarks'];
            }
        } else {
            $this->authorize('create', OvertimeForm::class);
            $user = auth()->user();
            $this->dept_id = $user->employee->department->id;
            
            $this->global_overtime_date = now()->format('Y-m-d');
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

        $this->employees = Employee::whereHas('department', fn($q) => $q->where('id', $this->dept_id))
            ->whereNull('end_date') // Only active employees
            ->select('nik', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function rules(): array
    {
        $rules = [
            'branch'        => 'required|in:Jakarta,Karawang',
            'design'        => 'nullable|in:0,1',
            'is_after_hour' => 'required|in:0,1',
            'description'   => 'nullable|string|max:500',
        ];

        if (! $this->formId) {
            $rules['dept_id'] = 'required|exists:departments,id';
        }

        $rules['items'] = 'required|array|min:1';
        foreach ($this->items as $i => $item) {
            $rules["items.$i.nik"]           = ['required', 'string', Rule::exists('employees', 'nik')];
            $rules["items.$i.name"]          = 'required|string';
            $rules["items.$i.overtime_date"] = 'required|date';
            $rules["items.$i.job_desc"]      = 'required|string|max:500';
            $rules["items.$i.start_date"]    = 'required|date';
            $rules["items.$i.start_time"]    = 'required';
            $rules["items.$i.end_date"]      = 'required|date|after_or_equal:items.'.$i.'.start_date';
            $rules["items.$i.end_time"]      = 'required|after:items.'.$i.'.start_time';
            $rules["items.$i.break"]         = 'required|numeric|min:0|max:180';
            $rules["items.$i.remarks"]       = 'nullable|string|max:250';
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
            $messages["items.$i.nik.required"]            = "Row $n: No employee chosen.";
            $messages["items.$i.nik.exists"]              = "Row $n: Identity invalid.";
            $messages["items.$i.job_desc.required"]       = "Row $n: Task description is required.";
            $messages["items.$i.start_time.required"]     = "Row $n: Start time missing.";
            $messages["items.$i.end_time.required"]       = "Row $n: End time missing.";
            $messages["items.$i.end_time.after"]          = "Row $n: End time must be after start time.";
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
            $this->items[$i]['nik']  = '';
            $this->items[$i]['name'] = '';
        }
        $this->refreshEmployees();
    }

    public function validateStage0(): bool
    {
        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch'  => 'required|in:Jakarta,Karawang',
            'is_after_hour' => 'required|in:0,1',
        ];

        $this->validate($rules, [
            'dept_id.required' => 'A department selection is required.',
            'branch.required'  => 'Please choose a work location.',
        ]);

        return true;
    }

    public function validateStep1(): bool
    {
        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch'  => 'required|string',
            'global_overtime_date' => 'required|date',
            'global_job_desc'      => 'required|string|min:3',
            'global_start_time'    => 'required',
            'global_end_time'      => 'required|after:global_start_time',
            'global_break'         => 'required|numeric|min:0|max:180',
        ];

        $this->validate($rules, [
            'dept_id.required'              => 'A department selection is required.',
            'global_overtime_date.required' => 'Please set an effectivity date.',
            'global_job_desc.required'      => 'The main objective is required.',
            'global_start_time.required'    => 'Start time is required.',
            'global_end_time.required'      => 'End time is required.',
            'global_end_time.after'         => 'The end time must be later than the start time.',
        ]);
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
        if (!$emp) return;

        // Replace empty placeholder if it's the only row
        if (count($this->items) === 1 && empty($this->items[0]['nik'])) {
            $this->items[0]['nik'] = $emp->nik;
            $this->items[0]['name'] = $emp->name;
            $this->resetErrorBag('items.0.nik'); // CLEAR RECENT SUBMISSION ERRORS
        } else {
            $this->items[] = [
                'id'            => null,
                'nik'           => $emp->nik,
                'name'          => $emp->name,
                'overtime_date' => $this->global_overtime_date,
                'job_desc'      => $this->global_job_desc,
                'start_date'    => $this->global_overtime_date,
                'start_time'    => $this->global_start_time,
                'end_date'      => $this->global_end_date,
                'end_time'      => $this->global_end_time,
                'break'         => $this->global_break,
                'remarks'       => $this->global_remarks,
                'payroll_status' => 'pending',
                'payroll_voucher_id' => null,
                'is_imported'   => false,
            ];
        }

        $this->resetIntegrity();
    }

    public function resetIntegrity(): void
    {
        $this->isIntegrityChecked = false;
        $this->integrityResults = [];
    }

    public function runIntegrityCheck(): void
    {
        $this->isIntegrityChecked = false;
        $this->integrityResults = [
            'structural' => 'loading',
            'local'      => 'pending',
            'payroll'    => 'pending',
        ];

        // 1. Structural Check
        foreach ($this->items as $i => $item) {
            if (empty($item['nik'])) {
                $this->addError("items.$i.nik", "Selection Required");
            }
        }

        try {
            $this->validate();
            $this->integrityResults['structural'] = 'passed';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->integrityResults['structural'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Please fix the highlighted errors before submitting.');
            return;
        }

        // 2. Local Duplicate Check (Proposed entries not yet pushed)
        $this->integrityResults['local'] = 'loading';
        $hasLocalConflict = false;
        
        foreach ($this->items as $i => $item) {
            $exists = OvertimeFormDetail::query()
                ->where('NIK', $item['nik'])
                ->where('overtime_date', $item['overtime_date'])
                ->whereNull('payroll_voucher_id') // Not yet pushed
                ->when($this->formId, fn($q) => $q->where('header_id', '!=', $this->formId)) // Edit Mode Safety
                ->exists();

            if ($exists) {
                $this->addError("items.$i.nik", "Already proposed in another draft/local form.");
                $hasLocalConflict = true;
            }
        }

        if ($hasLocalConflict) {
            $this->integrityResults['local'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Terdapat baris yang sudah ada di Database Lokal.');
            return;
        }
        $this->integrityResults['local'] = 'passed';

        // 3. Payroll (External) Check
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
        if (!$this->isIntegrityChecked) {
            $this->runIntegrityCheck();
            if (!$this->isIntegrityChecked) return null;
        }

        DB::beginTransaction();
        try {
            if ($this->formId) {
                $this->form->update([
                    'branch'        => $this->branch,
                    'is_design'     => $this->design,
                    'is_after_hour' => $this->is_after_hour,
                    'description'   => $this->description ?: null,
                ]);

                if ($this->removedDetailIds) {
                    OvertimeFormDetail::whereIn('id', $this->removedDetailIds)->where('header_id', $this->form->id)->delete();
                }

                foreach ($this->items as $item) {
                    $detailData = [
                        'header_id'     => $this->form->id,
                        'NIK'           => $item['nik'],
                        'name'          => $item['name'],
                        'overtime_date' => $item['overtime_date'],
                        'job_desc'      => $item['job_desc'],
                        'start_date'    => $item['start_date'],
                        'start_time'    => $item['start_time'],
                        'end_date'      => $item['end_date'],
                        'end_time'      => $item['end_time'],
                        'break'         => $item['break'],
                        'remarks'       => $item['remarks'] ?? '',
                    ];

                    if (! empty($item['id'])) {
                        OvertimeFormDetail::where('id', $item['id'])->where('header_id', $this->form->id)->update($detailData);
                    } else {
                        OvertimeFormDetail::create($detailData);
                    }
                }

                DB::commit();
                $this->dispatch('flash', type: 'success', message: 'Overtime form updated successfully.');
                return redirect()->route('overtime.index');

            } else {
                $service = app(OvertimeFormService::class);
                $header = $service->create(collect($this->validate()));
                
                DB::commit();
                $this->dispatch('flash', type: 'success', message: 'Overtime form created successfully.');
                return redirect()->route('overtime.index');
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
            'departments'     => Department::orderBy('name')->get(),
            'recentJobs'      => $this->recentJobs,
            'isMoulding'      => $isMoulding,
            'canOverrideDept' => true, // Standard for high-density entry
        ]);
    }
}
