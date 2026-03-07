<?php

namespace App\Livewire\Evaluation;

use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\EvaluationLegacyApprovalService;
use App\Domain\Discipline\Services\EvaluationScoreCalculatorService;
use App\Models\EvaluationData;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FocusMode extends Component
{
    // Filter State (Passed from Alpine)
    public $type = 'regular';
    public $month;
    public $year;

    // Component Scope State
    public $employeeIds = []; // Ordering array: ungraded first, then graded
    public $currentIndex = 0;
    
    // Grading Form State
    public $form = [];
    public $isNewSystem = true;
    
    // Grading Configuration (Mirrored from Modals)
    public $newFieldsConfig = [
        'kemampuan_kerja'   => 'Kemampuan',
        'kecerdasan_kerja'  => 'Kecerdasan',
        'qualitas_kerja'    => 'Kualitas',
        'disiplin_kerja'    => 'Disiplin',
        'kepatuhan_kerja'   => 'Kepatuhan',
        'lembur'            => 'Lembur',
        'efektifitas_kerja' => 'Efektifitas',
        'relawan'           => 'Relawan',
        'integritas'        => 'Integritas',
    ];

    public $oldFieldsConfig = [
        'kerajinan_kerja' => 'Kerajinan',
        'kerapian_kerja'  => 'Kerapian',
        'prestasi'        => 'Prestasi',
        'loyalitas'       => 'Loyalitas',
        'perilaku_kerja'  => 'Perilaku',
    ];

    /**
     * Listen for the open-focus-mode event from Alpine.
     */
    protected $listeners = ['focusModeOpened' => 'initializeFocusMode'];

    public function mount()
    {
        $this->month = \Carbon\Carbon::now()->month;
        $this->year = \Carbon\Carbon::now()->year;
    }

    /**
     * Called when the Focus Mode button is clicked via Alpine.
     */
    public function initializeFocusMode($type, $month, $year)
    {
        $this->type = $type;
        $this->month = $month;
        $this->year = $year;
        $this->isNewSystem = in_array($type, ['yayasan', 'magang']);
        
        $this->loadEligibleEmployees();
    }

    /**
     * Resolves all employees the user can manage and sorts them.
     */
    private function loadEligibleEmployees()
    {
        $user = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        try {
            $employees = match ($this->type) {
                'yayasan' => $resolver->resolveYayasanForUser($user),
                'magang'  => $resolver->resolveMagangForUser($user),
                default   => $resolver->resolveForUser($user),
            };
        } catch (\Throwable) {
            $this->employeeIds = [];
            return;
        }

        // We extract NIKs to track employees instead of record IDs
        $niks = $employees->pluck('nik')->filter()->values()->toArray();

        // Fetch their associated evaluation records for this period
        $records = EvaluationData::query()
            ->whereIn('NIK', $niks)
            ->whereMonth('Month', $this->month)
            ->whereYear('Month', $this->year)
            ->get();

        $gradedNiks = $records->where('total', '>', 0)->pluck('NIK')->toArray();
        $ungradedNiks = array_values(array_diff($niks, $gradedNiks));

        // Sort both arrays by employee name
        $nameSortMap = $employees->pluck('name', 'nik');
        usort($ungradedNiks, fn($a, $b) => strcmp($nameSortMap[$a] ?? '', $nameSortMap[$b] ?? ''));
        usort($gradedNiks, fn($a, $b) => strcmp($nameSortMap[$a] ?? '', $nameSortMap[$b] ?? ''));

        $this->employeeIds = array_merge($ungradedNiks, $gradedNiks); // $employeeIds actually stores NIKs now
        $this->currentIndex = 0;
        
        $this->loadCurrentEmployeeData();
    }

    /**
     * Loads the form array with the current selected employee's grades.
     */
    public function loadCurrentEmployeeData()
    {
        $this->reset('form');
        
        if (empty($this->employeeIds) || !isset($this->employeeIds[$this->currentIndex])) {
            return;
        }

        $record = $this->getCurrentRecordProperty();
        if (!$record) return;

        if ($this->isNewSystem) {
            foreach (array_keys($this->newFieldsConfig) as $field) {
                $this->form[$field] = $record->{$field} ?? '';
            }
        } else {
            foreach (array_keys($this->oldFieldsConfig) as $field) {
                $this->form[$field] = $record->{$field} ?? '';
            }
        }
    }

    /**
     * Directly set a grade via the tactile buttons.
     */
    public function setGrade($field, $value)
    {
        $this->form[$field] = strtoupper($value);
    }

    /**
     * Advances to the next employee without saving.
     */
    public function skip()
    {
        if ($this->currentIndex < count($this->employeeIds) - 1) {
            $this->currentIndex++;
            $this->loadCurrentEmployeeData();
        }
    }

    /**
     * Goes backwards to re-grade a previous employee.
     */
    public function previous()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
            $this->loadCurrentEmployeeData();
        }
    }

    /**
     * Gets the currently loaded record.
     */
    public function getCurrentRecordProperty()
    {
        if (empty($this->employeeIds) || !isset($this->employeeIds[$this->currentIndex])) {
            return null;
        }

        $nik = $this->employeeIds[$this->currentIndex];
        
        $employee = \App\Infrastructure\Persistence\Eloquent\Models\Employee::with('department')
            ->where('nik', $nik)->first();
            
        if (!$employee) return null;

        $record = EvaluationData::with('karyawan.department')
            ->where('NIK', $nik)
            ->whereMonth('Month', $this->month)
            ->whereYear('Month', $this->year)
            ->first();
            
        if (!$record) {
            $record = new EvaluationData([
                'NIK' => $nik,
                'Month' => \Carbon\Carbon::create($this->year, $this->month, 1)->format('Y-m-d'),
                'Alpha' => 0,
                'Telat' => 0,
                'Izin' => 0,
                'Sakit' => 0,
            ]);
            $record->setRelation('karyawan', $employee);
        }

        return $record;
    }

    /**
     * Saves the grade for the current employee and moves to the next.
     */
    public function saveGrade()
    {
        $record = $this->getCurrentRecordProperty();
        
        if (!$record) return;

        // Strip non-A-E inputs and standardize to uppercase
        $cleanForm = [];
        $validChars = ['A', 'B', 'C', 'D', 'E'];
        
        $fieldsToProcess = $this->isNewSystem ? array_keys($this->newFieldsConfig) : array_keys($this->oldFieldsConfig);
        
        foreach ($fieldsToProcess as $field) {
            $val = strtoupper($this->form[$field] ?? '');
            if (!in_array($val, $validChars)) {
                $this->addError("form.$field", 'Harus A, B, C, D, atau E.');
                return; // halt and show error
            }
            $cleanForm[$field] = $val;
        }

        if (!$record->exists) {
            $record->department_id = $record->karyawan->department->id ?? null;
            $record->level = $record->karyawan->level ?? 5;
            $record->evaluation_type = $this->type;
        }

        $record->fill($cleanForm);

        // Recalculate
        $scoreCalculator = app(EvaluationScoreCalculatorService::class);
        if ($this->isNewSystem) {
            $total = $scoreCalculator->calculateTotal($cleanForm, $record);
        } else {
            $absentDeduction = ($record->Alpha * 10) + ($record->Izin * 2) + $record->Sakit + ($record->Telat * 0.5);
            $criteriaScore = $scoreCalculator->calculateTotalOld($cleanForm, $record) - 40;
            $total = max(0, 40 - $absentDeduction) + $criteriaScore;
        }

        $record->total = $total;
        $record->pengawas = Auth::user()->name;
        $record->save();

        // Reset approval flags so it proceeds
        app(EvaluationLegacyApprovalService::class)->resetRejectedApprovals($record);

        // Flash toast over Livewire
        $this->dispatch('toast', type: 'success', message: "Nilai {$record->karyawan->name} berhasil disimpan.");

        // Move to next automatically
        if ($this->currentIndex < count($this->employeeIds) - 1) {
            $this->currentIndex++;
            $this->loadCurrentEmployeeData();
        } else {
            // We've reached the end
            $this->dispatch('focusModeFinished');
        }
    }

    public function render()
    {
        return view('livewire.evaluation.focus-mode', [
            'currentRecord' => $this->currentRecord,
            'totalRecords' => count($this->employeeIds),
            'currentStep' => $this->currentIndex + 1,
        ]);
    }
}
