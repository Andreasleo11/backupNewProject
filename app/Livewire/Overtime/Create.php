<?php

namespace App\Livewire\Overtime;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Department;
use App\Models\Employee;
use App\Models\HeaderFormOvertime;
use Illuminate\Support\Facades\Auth;
use App\Services\OvertimeFormService;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use WithFileUploads;

    public $dept_id;
    public $branch;
    public $design;
    public $excel_file;
    public $items = [];
    public $isExcelMode = false;
    public $is_after_hour = 1;

    protected $casts = [
        'isExcelMode' => 'boolean',
    ];

    public function mount()
    {
        $this->dept_id = auth()->user()->department_id;
        $this->items[] = $this->emptyItem();
    }

    public function rules()
    {
        $rules = [
            'dept_id' => 'required|exists:departments,id',
            'branch' => 'required|in:Jakarta,Karawang',
            'design' => 'nullable|in:0,1',
            'is_after_hour' => 'required|in:0,1',
            'excel_file' => 'nullable|file|mimes:xlsx,xls',
        ];

        if ($this->isExcelMode) {
            $rules['excel_file'] = 'required|file|mimes:xlsx,xls';
        } else {
            $rules['items'] = 'required|array|min:1';

            foreach ($this->items as $index => $item) {
                $rules["items.$index.nik"] = ['required', 'string', Rule::exists('employees', 'NIK')];
                $rules["items.$index.name"] = 'required|string';
                $rules["items.$index.overtime_date"] = 'required|date';
                $rules["items.$index.job_desc"] = 'required|string';
                $rules["items.$index.start_date"] = 'required|date';
                $rules["items.$index.start_time"] = 'required';
                $rules["items.$index.end_date"] = 'required|date';
                $rules["items.$index.end_time"] = 'required';
                $rules["items.$index.break"] = 'required|numeric|min:0|max:180';
                $rules["items.$index.remarks"] = 'required|string';
            }
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'dept_id.required' => 'Department is required.',
            'dept_id.exists' => 'Selected department is invalid.',
            'branch.required' => 'Branch is required.',
            'branch.in' => 'Branch must be either Jakarta or Karawang.',
            'design.in' => 'Design must be Yes (1) or No (0).',
            'is_after_hour.required' => 'After hour is required.',
            'is_after_hour.required' => 'After hour must be Yes (1) or No (0)',
            'excel_file.file' => 'The uploaded file must be a valid file.',
            'excel_file.mimes' => 'The Excel file must be in .xls or .xlsx format.',
        ];

        foreach ($this->items as $index => $item) {
            $humanIndex = $index + 1;

            $messages["items.$index.nik.required"] = "Row $humanIndex: NIK is required.";
            $messages["items.$index.nik.string"] = "Row $humanIndex: NIK must be a string.";
            $messages["items.$index.nik.exists"] = "Row $humanIndex: NIK is not registered.";

            $messages["items.$index.name.required"] = "Row $humanIndex: Name is required.";
            $messages["items.$index.name.string"] = "Row $humanIndex: Name must be a string.";

            $messages["items.$index.overtime_date.required"] = "Row $humanIndex: Overtime date is required.";
            $messages["items.$index.overtime_date.date"] = "Row $humanIndex: Overtime date must be a valid date.";

            $messages["items.$index.job_desc.required"] = "Row $humanIndex: Job description is required.";
            $messages["items.$index.job_desc.string"] = "Row $humanIndex: Job description must be a string.";

            $messages["items.$index.start_date.required"] = "Row $humanIndex: Start date is required.";
            $messages["items.$index.start_date.date"] = "Row $humanIndex: Start date must be a valid date.";

            $messages["items.$index.start_time.required"] = "Row $humanIndex: Start time is required.";

            $messages["items.$index.end_date.required"] = "Row $humanIndex: End date is required.";
            $messages["items.$index.end_date.date"] = "Row $humanIndex: End date must be a valid date.";

            $messages["items.$index.end_time.required"] = "Row $humanIndex: End time is required.";

            $messages["items.$index.break.required"] = "Row $humanIndex: Break is required.";
            $messages["items.$index.break.numeric"] = "Row $humanIndex: Break must be a number.";
            $messages["items.$index.break.min"] = "Row $humanIndex: Break must be at least 0 minutes.";
            $messages["items.$index.break.max"] = "Row $humanIndex: Break cannot exceed 180 minutes.";

            $messages["items.$index.remarks.required"] = "Row $humanIndex: Remarks are required.";
            $messages["items.$index.remarks.string"] = "Row $humanIndex: Remarks must be a string.";
        }

        return $messages;
    }

    public function emptyItem()
    {
        return [
            'nik' => '',
            'name' => '',
            'overtime_date' => '',
            'job_desc' => '',
            'start_date' => '',
            'start_time' => '',
            'end_date' => '',
            'end_time' => '',
            'break' => '',
            'remarks' => ''
        ];
    }

    public function addItem()
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $name)
    {
        $parts = explode('.', $name);
        if (count($parts) === 2) {
            $index = $parts[0];
            $field = $parts[1];

            if ($field === 'nik') {
                $emp = Employee::where('NIK', $value)->first();
                if ($emp) $this->items[$index]['name'] = (string) $emp->Nama;
            } elseif ($field === 'name') {
                $emp = Employee::where('Nama', $value)->first();
                if ($emp) $this->items[$index]['nik'] = (string) $emp->NIK;
            }
        }
    }

    public function updatedDeptId($value)
    {
        $this->design = null;
        foreach ($this->items as $index => $item) {
            $this->items[$index]['nik'] = '';
            $this->items[$index]['name'] = '';
        }
    }

    public function updatedExcelFile()
    {
        $this->validateOnly('excel_file');
    }

    public function submit()
    {
        $validated = $this->validate();
        $user = Auth::user();
        $result = OvertimeFormService::create(collect($validated), $user);

        return $result instanceof HeaderFormOvertime
            ? redirect()->route('formovertime.detail', $result->id)->with('success', 'Overtime created successfully.')
            : redirect()->route('overtime.index')->with('error', 'Tidak ada data valid yang dimasukkan, header dihapus otomatis.');
    }

    public function render()
    {
        return view('livewire.overtime.create', [
            'departements' => Department::orderBy('name')->get(),
            'employees' => $this->dept_id
                ? Employee::whereHas('department', fn($q) => $q->where('id', $this->dept_id))
                ->select('NIK', 'Nama')->orderBy('Nama')->get()
                : collect(),
        ]);
    }
}
