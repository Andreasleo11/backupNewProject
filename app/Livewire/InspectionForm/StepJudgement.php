<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepJudgement extends Component
{
    use ClearsNestedSession;

    public $detail_inspection_report_document_number;

    public $pass_quantity;

    public $reject_quantity;

    public $periodKey;

    public array $sessionSaved = [];

    public ?string $savedAt = null;

    public bool $isSaved = false;

    protected $rules = [
        'detail_inspection_report_document_number' => 'required|string',
        'pass_quantity' => 'required|integer|min:0',
        'reject_quantity' => 'required|integer|min:0',
    ];

    protected $messages = [
        'detail_inspection_report_document_number.required' => 'The detail inspection report document number is required.',
        'detail_inspection_report_document_number.string' => 'The detail inspection report document number must be a string.',
        'pass_quantity.required' => 'The pass quantity is required.',
        'pass_quantity.integer' => 'The pass quantity must be an integer.',
        'pass_quantity.min' => 'The pass quantity must be at least 0.',
        'reject_quantity.required' => 'The reject quantity is required.',
        'reject_quantity.integer' => 'The reject quantity must be an integer.',
        'reject_quantity.min' => 'The reject quantity must at least 0.',
        'reject_quantity.not_in' => 'The reject quantity cannot be negative.',
        'pass_quantity.not_in' => 'The pass quantity cannot be negative.',
    ];

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    protected function norm($v)
    {
        return $v === '' ? null : $v;
    }

    protected function snapshot(): array
    {
        return [
            'detail_inspection_report_document_number' => $this->detail_inspection_report_document_number,
            'pass_quantity' => $this->pass_quantity,
            'reject_quantity' => $this->reject_quantity,
        ];
    }

    public function getHasBaselineProperty(): bool
    {
        return ! empty($this->sessionSaved);
    }

    public function isFieldSaved(string $field): bool
    {
        $cur = $this->norm(data_get($this, $field));
        $base = $this->norm(data_get($this->sessionSaved, $field));

        return $cur !== null && $cur === $base;
    }

    public function mount()
    {
        $this->periodKey = 'p'.session('stepDetailSaved.period');
        $saved = session("stepDetailSaved.judgements.{$this->periodKey}", []);
        $this->sessionSaved = $saved;
        $this->savedAt = session("stepDetailSaved.judgements_meta.{$this->periodKey}.savedAt");
        $this->isSaved = ! empty($saved);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = $this->snapshot();

        session()->put("stepDetailSaved.judgements.{$this->periodKey}", $data);
        $this->savedAt = now()->toIso8601String();
        session()->put(
            "stepDetailSaved.judgements_meta.{$this->periodKey}.savedAt",
            $this->savedAt,
        );

        $this->sessionSaved = $data;
        $this->isSaved = true;

        $this->dispatch('toast', message: 'Step Judgement saved sucessfully!');
        $this->dispatch('judgementSaved', savedAt: $this->savedAt);
        $this->dispatch('judgementSaved')->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function resetStep()
    {
        $this->detail_inspection_report_document_number = '';
        $this->pass_quantity = null;
        $this->reject_quantity = null;

        $this->resetValidation();

        $this->forgetNestedKey('stepDetailSaved.judgements', $this->periodKey);
        $this->forgetNestedKey('stepDetailSaved.judgements_meta', $this->periodKey);

        $this->sessionSaved = [];
        $this->savedAt = null;
        $this->isSaved = false;

        $this->dispatch('toast', message: 'Step Judgement reset successfully!');
        $this->dispatch('judgementReset');
        $this->dispatch('judgementReset')->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function render()
    {
        return view('livewire.inspection-form.step-judgement');
    }
}
