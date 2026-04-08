<?php

namespace App\Livewire\Overtime;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Services\OvertimeFormService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('new.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $formId = null;
    public ?OvertimeForm $form = null;

    // Header fields
    public ?int $dept_id = null;
    public ?string $branch = null;
    public ?string $design = null;
    public int $is_after_hour = 1;
    public string $description = '';

    // Create-mode exclusively
    public $excel_file = null;
    public bool $isExcelMode = false;

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

    // Auxiliary data
    public array $employees = [];
    public array $recentJobs = [];
    public array $validationErrors = [];
    public bool $isCheckingPayroll = false;

    public function mount(?int $id = null): void
    {
        $this->formId = $id;

        $this->employees = $this->fetchEmployees();
        $this->recentJobs = OvertimeFormDetail::select('job_desc')
            ->whereNotNull('job_desc')
            ->distinct()
            ->limit(15)
            ->pluck('job_desc')
            ->toArray();

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
                'payroll_status' => 'pending', // pending, safe, exists
            ])->toArray();

            // Populate globals from the first item if exists
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
            $this->dept_id = $user->department_id;
            
            $this->global_overtime_date = now()->format('Y-m-d');
            $this->global_end_date = now()->format('Y-m-d');

            $this->addEmptyRow();
        }
    }

    public function downloadTemplate()
    {
        $this->authorize('create', OvertimeForm::class);
        return \Maatwebsite\Excel\Facades\Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array {
                return [['12345', 'John Doe', '2026-10-01', 'Stock Opname', '2026-10-01', '17:00', '2026-10-01', '20:00', '30', 'Urgent target output']];
            }
            public function headings(): array {
                return ['NIK', 'Nama', 'Tanggal Lembur', 'Pekerjaan', 'Mulai Tanggal', 'Mulai Jam', 'Selesai Tanggal', 'Selesai Jam', 'Istirahat (Menit)', 'Keterangan'];
            }
        }, 'Overtime_Template.xlsx');
    }

    // ── Observers to apply globals to rows ────────────────────────────────

    public function updatedGlobalOvertimeDate($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['overtime_date'] = $value; $this->items[$i]['start_date'] = $value; }
    }
    public function updatedGlobalEndDate($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['end_date'] = $value; }
    }
    public function updatedGlobalStartTime($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['start_time'] = $value; }
    }
    public function updatedGlobalEndTime($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['end_time'] = $value; }
    }
    public function updatedGlobalBreak($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['break'] = $value; }
    }
    public function updatedGlobalJobDesc($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['job_desc'] = $value; }
    }

    public function checkPayrollStatus(): void
    {
        $this->isCheckingPayroll = true;
        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        
        foreach ($this->items as $index => &$item) {
            if (empty($item['nik']) || empty($item['overtime_date'])) {
                continue;
            }
            
            $result = $service->checkDetailExists([
                'nik' => $item['nik'],
                'overtime_date' => $item['overtime_date']
            ]);
            
            $item['payroll_status'] = $result['exists'] ? 'exists' : 'safe';
            $item['payroll_msg'] = $result['message'] ?? '';
        }
        
        $this->isCheckingPayroll = false;
        
        $hasDuplicates = collect($this->items)->contains('payroll_status', 'exists');
        if ($hasDuplicates) {
            $this->dispatch('flash', type: 'warning', message: 'Terdapat data yang sudah ada di JPayroll.');
        } else {
            $this->dispatch('flash', type: 'success', message: 'Seluruh data aman (tidak ada duplikat di Payroll).');
        }
    }
    public function updatedGlobalRemarks($value) {
        foreach($this->items as $i => $item) { $this->items[$i]['remarks'] = $value; }
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

        if (! $this->formId) {
            $rules['dept_id'] = 'required|exists:departments,id';
        }

        if (! $this->formId && $this->isExcelMode) {
            $rules['excel_file'] = 'required|file|mimes:xlsx,xls|max:5120';
        } else {
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
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'dept_id.required'       => 'Department is required.',
            'branch.required'        => 'Branch is required.',
            'excel_file.required'    => 'Please upload an Excel file.',
        ];

        if (! $this->isExcelMode || $this->formId) {
            foreach ($this->items as $i => $item) {
                $n = $i + 1;
                $messages["items.$i.nik.required"]            = "Row $n: NIK missing.";
                $messages["items.$i.nik.exists"]              = "Row $n: NIK invalid.";
                $messages["items.$i.start_time.required"]     = "Row $n: Start time missing.";
                $messages["items.$i.end_time.required"]       = "Row $n: End time missing.";
                $messages["items.$i.end_date.after_or_equal"] = "Row $n: End date invalid.";
            }
        }

        return $messages;
    }

    public function updated($field): void
    {
        $this->resetErrorBag($field);
    }

    public function updatedDeptId(): void
    {
        if ($this->formId) return;

        $this->design = null;
        foreach ($this->items as $i => $item) {
            $this->items[$i]['nik']  = '';
            $this->items[$i]['name'] = '';
        }
        $this->employees = $this->fetchEmployees();
    }

    public function addEmptyRow(): void
    {
        $this->items[] = [
            'id'            => null,
            'nik'           => '',
            'name'          => '',
            'overtime_date' => $this->global_overtime_date,
            'job_desc'      => $this->global_job_desc,
            'start_date'    => $this->global_overtime_date,
            'start_time'    => $this->global_start_time,
            'end_date'      => $this->global_end_date,
            'end_time'      => $this->global_end_time,
            'break'         => $this->global_break,
            'remarks'       => $this->global_remarks,
            'payroll_status' => 'pending',
        ];
    }

    public function removeRow(int $index): void
    {
        if (count($this->items) <= 1) return;

        $row = $this->items[$index];
        if (! empty($row['id'])) {
            $this->removedDetailIds[] = (int) $row['id'];
        }

        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function submit(): mixed
    {
        try {
            $validated = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->validationErrors = $this->getErrorBag()->toArray();
            $this->dispatch('flash', type: 'error', message: 'Please fix the highlighted errors before submitting.');
            return null;
        }

        // Safety check for payroll duplicates
        $hasDuplicates = collect($this->items)->contains('payroll_status', 'exists');
        if ($hasDuplicates) {
            $this->dispatch('flash', type: 'error', message: 'Tidak dapat mengirim! Terdapat baris yang sudah ada di JPayroll.');
            return null;
        }

        try {
            if ($this->formId) {
                $this->authorize('update', $this->form);
                DB::transaction(function () {
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
                });

                session()->flash('success', 'Overtime form updated successfully.');
                return redirect()->route('overtime.detail', $this->formId);
            } else {
                $header = OvertimeFormService::create(collect($validated));
                session()->flash('success', 'Overtime form created successfully.');
                return redirect()->route('overtime.detail', $header->id);
            }
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Overtime Form Error: ' . $e->getMessage());
            $this->dispatch('flash', type: 'error', message: 'Failed to save form.');
            return null;
        }
    }

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
        return auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('verificator');
    }

    public function render(): mixed
    {
        $this->validationErrors = $this->getErrorBag()->toArray();

        return view('livewire.overtime.form', [
            'departments'    => Department::orderBy('name')->get(),
            'isMoulding'     => $this->isMouldingDept(),
            'canOverrideDept' => $this->canOverrideDept(),
        ]);
    }
}
