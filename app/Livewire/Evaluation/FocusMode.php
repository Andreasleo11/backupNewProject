<?php

namespace App\Livewire\Evaluation;

use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineApprovalService;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
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

        // Fetch their associated evaluation records for this period
        $records = EvaluationData::with('karyawan.department')
            ->whereIn('NIK', $employees->pluck('NIK'))
            ->where('evaluation_type', $this->type)
            ->whereMonth('Month', $this->month)
            ->whereYear('Month', $this->year)
            ->get();

        // Separate ungraded (total == 0) from graded (total > 0), so ungraded are tackled first
        $ungraded = $records->where('total', 0)->sortBy('karyawan.name')->pluck('id')->toArray();
        $graded   = $records->where('total', '>', 0)->sortBy('karyawan.name')->pluck('id')->toArray();

        $this->employeeIds = array_merge($ungraded, $graded);
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

        $record = EvaluationData::find($this->employeeIds[$this->currentIndex]);

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

        return EvaluationData::with('karyawan.department')->find($this->employeeIds[$this->currentIndex]);
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

        // Apply grades
        $record->update($cleanForm);

        // Recalculate
        $scoreCalculator = app(DisciplineScoreCalculatorService::class);
        if ($this->isNewSystem) {
            $total = $scoreCalculator->calculateTotal($cleanForm, $record->fresh());
        } else {
            // Old system takes base score + adds absence penalty, then calculateTotalOld subtracts 40
            $absentDeduction = ($record->Alpha * 10) + ($record->Izin * 2) + $record->Sakit + ($record->Telat * 0.5);
            $criteriaScore = $scoreCalculator->calculateTotalOld($cleanForm, $record->fresh()) - 40;
            $total = max(0, 40 - $absentDeduction) + $criteriaScore;
        }

        $record->update([
            'total'    => $total,
            'pengawas' => Auth::user()->name,
        ]);

        // Reset approval flags so it proceeds
        app(DisciplineApprovalService::class)->resetRejectedApprovals($record);

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
