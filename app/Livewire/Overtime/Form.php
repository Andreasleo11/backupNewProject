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
    public string $global_start_date = '';
    public string $global_job_desc = '';
    public string $global_start_time = '';
    public string $global_end_date = '';
    public string $global_end_time = '';
    public string $global_break = '0';
    public string $global_remarks = '';

    // Date Override Settings
    public bool $show_date_override = false;
    public bool $excel_mode = false;
    public string $global_custom_end_date = '';

    // Detail rows
    public array $items = [];
    public array $removedDetailIds = [];
    public array $employees = [];

    // Bulk Management
    public bool $showBulkTray = false;
    public $rosterFile;
    public array $stagedRosterData = [];

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
                $this->global_start_date    = $first['start_date'];
                $this->global_job_desc      = $first['job_desc'];
                $this->global_start_time    = $first['start_time'];
                $this->global_end_date      = $first['end_date'];
                $this->global_break         = $first['break'];
                $this->global_remarks       = $first['remarks'];

                // Check if end_date differs from overtime date (overtime extends to next day)
                $this->global_custom_end_date = $first['end_date'];
                $this->show_date_override = $first['end_date'] !== $first['overtime_date'] || $first['start_date'] !== $first['overtime_date'];
            }
        } else {
            $this->authorize('create', OvertimeForm::class);
            $user = auth()->user();
            $this->dept_id = $user->employee->department->id;
            
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
            $rules["items.$i.end_time"]      = 'required';
            $rules["items.$i.break"]         = 'required|numeric|min:0|max:180';
            $rules["items.$i.remarks"]       = 'nullable|string|max:250';

            // Add time validation only if dates are the same (same-day entries)
            if ($item['start_date'] === $item['end_date']) {
                $rules["items.$i.end_time"] .= '|after:items.'.$i.'.start_time';
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
            $messages["items.$i.nik.required"]            = "Row $n: No employee chosen.";
            $messages["items.$i.nik.exists"]              = "Row $n: Identity invalid.";
            $messages["items.$i.job_desc.required"]       = "Row $n: Task description is required.";
            $messages["items.$i.start_time.required"]     = "Row $n: Start time missing.";
            $messages["items.$i.end_time.required"]       = "Row $n: End time missing.";
            $messages["items.$i.end_time.after"]          = "Row $n: End time must be after start time (same day entries only).";
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
        // In Excel Mode, the schedule fields are not required —
        // they will come from the uploaded file per-row.
        if ($this->excel_mode) {
            $this->validate([
                'dept_id' => 'required|exists:departments,id',
                'branch'  => 'required|string',
            ], [
                'dept_id.required' => 'A department selection is required.',
            ]);
            return true;
        }

        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch'  => 'required|string',
            'global_overtime_date' => 'required|date',
            'global_start_date'    => 'required|date',
            'global_job_desc'      => 'required|string|min:3',
            'global_start_time'    => 'required',
            'global_end_time'      => 'required',
            'global_break'         => 'required|numeric|min:0|max:180',
        ];

        // Add time validation only for same-day mode
        if (!$this->show_date_override) {
            $rules['global_end_time'] .= '|after:global_start_time';
        }

        // Add custom end date validation for multi-day mode
        if ($this->show_date_override) {
            $rules['global_custom_end_date'] = 'required|date|after_or_equal:global_overtime_date';
        }

        $messages = [
            'dept_id.required'              => 'A department selection is required.',
            'global_overtime_date.required' => 'Please set an effectivity date.',
            'global_start_date.required'    => 'Please set a start date.',
            'global_job_desc.required'      => 'The main objective is required.',
            'global_start_time.required'    => 'Start time is required.',
            'global_end_time.required'      => 'End time is required.',
            'global_end_time.after'         => 'The end time must be later than the start time.',
            'global_custom_end_date.required' => 'End date is required for multi-day overtime.',
            'global_custom_end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];

        $this->validate($rules, $messages);
        return true;
    }

    public function downloadRosterTemplate()
    {
        $headers = [
            'NIK', 
            'Overtime Date (YYYY-MM-DD)', 
            'Start Date (YYYY-MM-DD)', 
            'Start Time (HH:MM)', 
            'End Date (YYYY-MM-DD)', 
            'End Time (HH:MM)', 
            'Break (Mins)', 
            'Task', 
            'Remarks'
        ];
        
        $callback = function() use($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                '12345', 
                now()->format('Y-m-d'), 
                now()->format('Y-m-d'), 
                '17:00', 
                now()->format('Y-m-d'), 
                '19:00', 
                '0', 
                'Optional Override Task', 
                ''
            ]); 
            fputcsv($file, ['67890', '', '', '', '', '', '', '', 'Left blank implies using Global Settings']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'roster_import_template.csv', [
            'Content-type' => 'text/csv',
        ]);
    }

    public function updatedRosterFile()
    {
        $this->validate([
            'rosterFile' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $this->stagedRosterData = [];

        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new class {}, $this->rosterFile->getRealPath());
            $rows = $data[0] ?? [];
            
            // Skip the header if it says 'NIK'
            if (isset($rows[0][0]) && strtoupper(trim($rows[0][0])) === 'NIK') {
                $rows = array_slice($rows, 1);
            }

            // Defaults
            $defOvtDate = $this->global_overtime_date;
            $defStartDate = $this->show_date_override && $this->global_start_date ? $this->global_start_date : $defOvtDate;
            $defEndDate = $this->show_date_override && $this->global_custom_end_date ? $this->global_custom_end_date : $defOvtDate;

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;
                
                $rawNik = trim((string)($row[0] ?? ''));
                // Normalize to 5-digit zero-padded NIK (handles Excel float like 7073.0)
                if (is_numeric($rawNik)) {
                    $nik = str_pad((string)(int)$rawNik, 5, '0', STR_PAD_LEFT);
                } else {
                    $nik = str_pad($rawNik, 5, '0', STR_PAD_LEFT);
                }
                if (empty($nik) || $nik === '00000') continue;

                $isValid = true;
                $errors = [];
                $name = 'Unknown';

                $emp = Employee::where('nik', $nik)->first();
                if (!$emp) {
                    $isValid = false;
                    $errors[] = "NIK not found";
                } else {
                    $name = $emp->name;
                    if (collect($this->items)->contains('nik', $nik)) {
                        $isValid = false;
                        $errors[] = "Already in roster";
                    }
                }

                $this->stagedRosterData[] = [
                    'nik'           => $nik,
                    'name'          => $name,
                    'is_valid'      => $isValid,
                    'errors'        => $errors,
                    
                    // Specific Overrides fallback to global settings
                    'overtime_date' => !empty($row[1]) ? trim((string)$row[1]) : $defOvtDate,
                    'start_date'    => !empty($row[2]) ? trim((string)$row[2]) : $defStartDate,
                    'start_time'    => !empty($row[3]) ? trim((string)$row[3]) : $this->global_start_time,
                    'end_date'      => !empty($row[4]) ? trim((string)$row[4]) : $defEndDate,
                    'end_time'      => !empty($row[5]) ? trim((string)$row[5]) : $this->global_end_time,
                    'break'         => !empty($row[6]) ? trim((string)$row[6]) : $this->global_break,
                    'job_desc'      => !empty($row[7]) ? trim((string)$row[7]) : $this->global_job_desc,
                    'remarks'       => !empty($row[8]) ? trim((string)$row[8]) : $this->global_remarks,
                ];
            }
            
            $this->dispatch('flash', type: 'info', message: "Staged " . count($this->stagedRosterData) . " potential roster members.");

            // In Excel Mode: automatically signal the wizard to show Step 3
            // The user still gets to review the staging panel before committing.
            if ($this->excel_mode) {
                $this->dispatch('excel-roster-staged');
            }

        } catch (\Throwable $e) {
            $this->dispatch('flash', type: 'error', message: 'Failed to process roster file: ' . $e->getMessage());
        }

        $this->rosterFile = null;
    }

    public function commitStagedRoster()
    {
        $addedCount = 0;

        foreach ($this->stagedRosterData as $staged) {
            if ($staged['is_valid']) {
                if (count($this->items) === 1 && empty($this->items[0]['nik'])) {
                    $this->items[0]['nik']           = $staged['nik'];
                    $this->items[0]['name']          = $staged['name'];
                    $this->items[0]['overtime_date'] = $staged['overtime_date'];
                    $this->items[0]['start_date']    = $staged['start_date'];
                    $this->items[0]['start_time']    = $staged['start_time'];
                    $this->items[0]['end_date']      = $staged['end_date'];
                    $this->items[0]['end_time']      = $staged['end_time'];
                    $this->items[0]['break']         = $staged['break'];
                    $this->items[0]['job_desc']      = $staged['job_desc'];
                    $this->items[0]['remarks']       = $staged['remarks'];
                } else {
                    $this->items[] = [
                        'id'            => null,
                        'nik'           => $staged['nik'],
                        'name'          => $staged['name'],
                        'overtime_date' => $staged['overtime_date'],
                        'start_date'    => $staged['start_date'],
                        'start_time'    => $staged['start_time'],
                        'end_date'      => $staged['end_date'],
                        'end_time'      => $staged['end_time'],
                        'break'         => $staged['break'],
                        'job_desc'      => $staged['job_desc'],
                        'remarks'       => $staged['remarks'],
                        'payroll_status'=> 'pending'
                    ];
                }
                $addedCount++;
            }
        }

        $this->stagedRosterData = [];
        $this->resetIntegrity();
        $this->dispatch('flash', type: 'success', message: "Added $addedCount members successfully.");
    }

    public function cancelStagedRoster()
    {
        $this->stagedRosterData = [];
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
                'id'            => null,
                'nik'           => $emp->nik,
                'name'          => $emp->name,
                'overtime_date' => $this->global_overtime_date,
                'job_desc'      => $this->global_job_desc,
                'start_date'    => $startDate,
                'start_time'    => $this->global_start_time,
                'end_date'      => $endDate,
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
            'departments'     => Department::orderBy('name')->get(),
            'recentJobs'      => $this->recentJobs,
            'isMoulding'      => $isMoulding,
            'canOverrideDept' => true, // Standard for high-density entry
        ]);
    }
}
