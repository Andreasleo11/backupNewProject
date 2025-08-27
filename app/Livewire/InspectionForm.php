<?php

namespace App\Livewire;

use Livewire\Component;

class InspectionForm extends Component
{
    public $currentStep = 1;
    public $filledSteps = [];
    public $inspection_report_document_number;
    public $stepSessionKeys;

    protected $listeners = [
        'stepHeaderSaved' => 'handlerHeaderSaved',
        'stepDetailSaved' => 'markStepFilled',
        'stepProblemSaved' => 'markStepFilled',
        'stepFinalSubmit' => 'markStepFilled',
        'nextStep' => 'nextStep',
        'setStep' => 'setStep',
    ];

    public function mount()
    {
        $this->currentStep = session('lastStepVisited', 1); // fallback to 1
        $this->stepSessionKeys = [
            1 => 'stepHeaderSaved',
            2 => 'stepDetailSaved',
            3 => 'stepProblemSaved',
            4 => 'stepFinalSubmit',
        ];

        foreach ($this->stepSessionKeys as $step => $key) {
            if (session()->has($key)) {
                $this->filledSteps[$step] = true;
            }
        }

        // Optimal : also pre-fill document numbers if you want to keep continuity
        $this->inspection_report_document_number = session('stepHeaderSaved.document_number');
    }

    public function markStepFilled()
    {
        $this->filledSteps[$this->currentStep] = true;
    }

    public function handlerHeaderSaved($data)
    {
        $this->filledSteps[1] = true;
        $this->inspection_report_document_number = $data['document_number'] ?? null;
    }

    public function updatedCurrentStep($step)
    {
        session(['lastStepVisited' => $step]);
    }

    public function nextStep()
    {
        if ($this->currentStep < count($this->stepSessionKeys)) {
            $this->currentStep++;
            session(['lastStepVisited' => $this->currentStep]);
        }
    }

    public function setStep($step)
    {
        $this->currentStep = $step;
        session(['lastStepVisited' => $this->currentStep]);
    }

    public function render()
    {
        return view('livewire.inspection-form')->layout('layouts.guest');
    }
}
