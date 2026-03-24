<?php

namespace App\Livewire\Overtime;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use App\Services\OvertimeFormService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Unified Livewire component for Creating and Editing Overtime Forms.
 * 
 * Create Mode (when $formId is null)
 * Edit Mode (when $formId is present)
 */
class Form extends Component
{
    use WithFileUploads;

    public ?int $formId = null;
    public ?HeaderFormOvertime $form = null;

    // Header fields
    public ?int $dept_id = null;
    public ?string $branch = null;
    public ?string $design = null;
    public int $is_after_hour = 1;
    public string $description = '';

    // Create-mode exclusively
    public $excel_file = null;
    public bool $isExcelMode = false;

    // Detail rows
    public array $items = [];
    public array $removedDetailIds = [];

    // Auxiliary data
    public array $employees = [];
    public array $recentJobs = [];
    public array $validationErrors = [];

    public function mount(?int $id = null): void
    {
        $this->formId = $id;

        if ($this->formId) {
            // -- Edit Mode Setup --
            $this->form = HeaderFormOvertime::with(['details', 'department'])->findOrFail($id);
            $this->authorize('update', $this->form);

            if (! in_array($this->form->status, ['waiting-creator', 'waiting-dept-head'], true)) {
                abort(403, 'This form can no longer be edited.');
            }

            $this->dept_id      = $this->form->dept_id;
            $this->branch       = $this->form->branch;
            $this->design       = $this->form->is_design !== null ? (string) $this->form->is_design : null;
            $this->is_after_hour = (int) $this->form->is_after_hour;
            $this->description  = $this->form->description ?? '';

            // Populate existing detail rows
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
            ])->toArray();
        } else {
            // -- Create Mode Setup --
            $this->authorize('create', HeaderFormOvertime::class);
            
            $user = auth()->user();
            $this->dept_id = $user->department_id;
            
            $this->addEmptyRow();
        }

        $this->employees = $this->fetchEmployees();
        $this->recentJobs = DetailFormOvertime::select('job_desc')
            ->whereNotNull('job_desc')
            ->distinct()
            ->limit(15)
            ->pluck('job_desc')
            ->toArray();
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array {
                return [
                    ['12345', 'John Doe', '2026-10-01', 'Monthly Stock Opname', '2026-10-01', '17:00', '2026-10-01', '20:00', '30', 'Urgent target output'],
                ];
            }
            public function headings(): array {
                return ['NIK', 'Nama', 'Tanggal Lembur', 'Pekerjaan', 'Mulai Tanggal', 'Mulai Jam', 'Selesai Tanggal', 'Selesai Jam', 'Istirahat (Menit)', 'Keterangan'];
            }
        }, 'Overtime_Template.xlsx');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function rules(): array
    {
        $rules = [
            'branch'        => 'required|in:Jakarta,Karawang',
            'design'        => 'nullable|in:0,1',
            'is_after_hour' => 'required|in:0,1',
            'description'   => 'nullable|string|max:500',
        ];

        // Dept ID is required in create mode, readonly in edit mode
        if (! $this->formId) {
            $rules['dept_id'] = 'required|exists:departments,id';
        }

        if (! $this->formId && $this->isExcelMode) {
            // Create via Excel mode
            $rules['excel_file'] = 'required|file|mimes:xlsx,xls|max:5120';
        } else {
            // Manual entries (both Create and Edit)
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
                $rules["items.$i.remarks"]       = 'required|string|max:250';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'dept_id.required'       => 'Department is required.',
            'dept_id.exists'         => 'Selected department is invalid.',
            'branch.required'        => 'Branch is required.',
            'branch.in'              => 'Branch must be Jakarta or Karawang.',
            'is_after_hour.required' => 'After hour field is required.',
            'excel_file.required'    => 'Please upload an Excel file.',
            'excel_file.mimes'       => 'File must be .xlsx or .xls format.',
            'excel_file.max'         => 'File must not exceed 5 MB.',
        ];

        if (! $this->isExcelMode || $this->formId) {
            foreach ($this->items as $i => $item) {
                $n = $i + 1;
                $messages["items.$i.nik.required"]            = "Row $n: NIK is required.";
                $messages["items.$i.nik.exists"]              = "Row $n: NIK not found in employee records.";
                $messages["items.$i.name.required"]           = "Row $n: Name is required.";
                $messages["items.$i.overtime_date.required"]  = "Row $n: OT date is required.";
                $messages["items.$i.overtime_date.date"]      = "Row $n: OT date must be a valid date.";
                $messages["items.$i.job_desc.required"]       = "Row $n: Job description is required.";
                $messages["items.$i.start_date.required"]     = "Row $n: Start date is required.";
                $messages["items.$i.start_time.required"]     = "Row $n: Start time is required.";
                $messages["items.$i.end_date.required"]       = "Row $n: End date is required.";
                $messages["items.$i.end_date.after_or_equal"] = "Row $n: End date must be on or after Start date.";
                $messages["items.$i.end_time.required"]       = "Row $n: End time is required.";
                $messages["items.$i.break.required"]          = "Row $n: Break is required.";
                $messages["items.$i.break.numeric"]           = "Row $n: Break must be a number (minutes).";
                $messages["items.$i.remarks.required"]        = "Row $n: Remarks are required.";
            }
        }

        return $messages;
    }

    // ── Lifecycle hooks ───────────────────────────────────────────────────────

    public function updated(string $field): void
    {
        $this->resetErrorBag($field);
    }

    public function updatedDeptId(): void
    {
        if ($this->formId) return; // Cannot change dept if editing

        $this->design = null;
        foreach ($this->items as $i => $item) {
            $this->items[$i]['nik']  = '';
            $this->items[$i]['name'] = '';
        }
        $this->employees = $this->fetchEmployees();
    }

    public function updatedExcelFile(): void
    {
        $this->validateOnly('excel_file');
    }

    // ── Row helpers ───────────────────────────────────────────────────────────

    public function addEmptyRow(): void
    {
        $this->items[] = [
            'id'            => null,
            'nik'           => '',
            'name'          => '',
            'overtime_date' => '',
            'job_desc'      => '',
            'start_date'    => '',
            'start_time'    => '',
            'end_date'      => '',
            'end_time'      => '',
            'break'         => '',
            'remarks'       => '',
        ];
    }

    public function removeRow(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        $row = $this->items[$index];
        if (! empty($row['id'])) {
            $this->removedDetailIds[] = (int) $row['id'];
        }

        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    // ── Submission ────────────────────────────────────────────────────────────

    public function submit(): mixed
    {
        try {
            $validated = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($key, $msg);
                }
            }
            $this->validationErrors = $this->getErrorBag()->toArray();
            $this->dispatch('flash', type: 'error', message: 'Please fix the highlighted errors before submitting.');
            return null;
        }

        try {
            if ($this->formId) {
                // -- Edit Mode Update --
                DB::transaction(function () {
                    // Update header
                    $this->form->update([
                        'branch'        => $this->branch,
                        'is_design'     => $this->design,
                        'is_after_hour' => $this->is_after_hour,
                        'description'   => $this->description ?: null,
                    ]);

                    // Delete removed rows
                    if ($this->removedDetailIds) {
                        DetailFormOvertime::whereIn('id', $this->removedDetailIds)
                            ->where('header_id', $this->form->id)
                            ->delete();
                    }

                    // Upsert items
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
                            'remarks'       => $item['remarks'],
                        ];

                        if (! empty($item['id'])) {
                            DetailFormOvertime::where('id', $item['id'])
                                ->where('header_id', $this->form->id)
                                ->update($detailData);
                        } else {
                            DetailFormOvertime::create($detailData);
                        }
                    }
                });

                session()->flash('success', 'Overtime form updated successfully.');
                return redirect()->route('overtime.detail', $this->formId);

            } else {
                // -- Create Mode --
                $header = OvertimeFormService::create(collect($validated));

                session()->flash('success', 'Overtime form created successfully.');
                return redirect()->route('overtime.detail', $header->id);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($key, $msg);
                }
            }
            $this->validationErrors = $this->getErrorBag()->toArray();
            
            $firstError = collect($e->errors())->flatten()->first() ?? 'Form validation failed.';
            $this->dispatch('flash', type: 'error', message: $firstError);
            
            return null;
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Overtime Form Submission Error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => auth()->id(),
                'form_id'   => $this->formId,
                'trace'     => $e->getTraceAsString(),
            ]);
            report($e);
            $this->dispatch('flash', type: 'error', message: 'Failed to save overtime form! Please try again.');

            return null;
        }
    }

    // ── Data helpers ──────────────────────────────────────────────────────────

    protected function fetchEmployees(): array
    {
        return $this->dept_id
            ? Employee::whereHas('department', fn ($q) => $q->where('id', $this->dept_id))
                ->orderBy('name')
                ->get(['nik', 'name'])
                ->map(fn ($e) => ['nik' => $e->nik, 'name' => $e->name])
                ->values()
                ->toArray()
            : [];
    }

    public function isMouldingDept(): bool
    {
        return (bool) Department::find($this->dept_id)?->name === 'MOULDING';
    }

    public function canOverrideDept(): bool
    {
        return auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('HR');
    }

    public function render(): mixed
    {
        $this->validationErrors = $this->getErrorBag()->toArray();

        return view('livewire.overtime.form', [
            'departments'    => Department::orderBy('name')->get(),
            'isMoulding'     => $this->isMouldingDept(),
            'canOverrideDept' => $this->canOverrideDept(),
        ])->layout('new.layouts.app');
    }
}
