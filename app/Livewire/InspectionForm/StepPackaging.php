<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Illuminate\Support\Str;
use Livewire\Component;

class StepPackaging extends Component
{
    use ClearsNestedSession;

    public $second_inspection_document_number;

    public $packagings = [];

    public $periodKey;

    public array $sessionSaved = [];

    public array $baselineKey = [];

    public ?string $savedAt = null;

    public bool $isSaved = false;

    protected $rules = [
        'packagings.*.second_inspection_document_number' => 'required|string',
        'packagings.*.snp' => 'required|integer|min:1',
        'packagings.*.box_label' => 'required|string',
        'packagings.*.judgement' => 'required|in:OK,NG',
        'packagings.*.remarks' => 'nullable|string|required_if:packagings.*.judgement,NG',
    ];

    protected $messages = [
        'packagings.*.second_inspection_document_number.required' => 'The second inspection document number is required.',
        'packagings.*.second_inspection_document_number.string' => 'The second inspection document number must be a string.',
        'packagings.*.snp.required' => 'The snp is required.',
        'packagings..*.snp.integer' => 'The snp must be an integer.',
        'packagings.*.snp.min' => 'The snp must be at least 1.',
        'packagings.*.box_label.required' => 'The box label is required.',
        'packagings.*.box_label.string' => 'The box label must be a string.',
        'packagings.*.judgement.required' => 'The judgement is required.',
        'packagings.*.judgement.in' => 'The judgement must be either OK or NG.',
        'packagings.*.remarks.required' => 'Remarks are require if the judgement is NG.',
        'packagings.*.remarks.string' => 'Remarks must be a string.',
    ];

    protected function norm($v)
    {
        return $v === '' ? null : $v;
    }

    protected function ensureRowKeys(): void
    {
        foreach ($this->packagings as $i => $row) {
            if (empty($row['row_key'])) {
                $this->packagings[$i]['row_key'] = (string) Str::uuid();
            }
        }
    }

    protected function buildBaselineMap(): void
    {
        $this->baselineKey = [];
        foreach ($this->sessionSaved as $row) {
            if (! empty($row['row_key'])) {
                $this->baselineKey[$row['row_key']] = $row;
            }
        }
    }

    public function isRowFieldSaved(string $rowKey, string $field): bool
    {
        $cur = $this->norm(
            data_get(collect($this->packagings)->firstWhere('row_key', $rowKey), $field),
        );
        $base = $this->norm(data_get($this->baselineKey[$rowKey] ?? [], $field));

        return $cur !== null && $cur === $base;
    }

    public function mount($second_inspection_document_number = null)
    {
        $this->second_inspection_document_number = $second_inspection_document_number;
        $this->periodKey = 'p'.session('stepDetailSaved.period');

        $this->packagings = session("stepDetailSaved.packagings.{$this->periodKey}", []);
        $this->sessionSaved = session("stepDetailSaved.packagings.{$this->periodKey}", []);
        $this->savedAt = session("stepDetailSaved.packagings_meta.{$this->periodKey}.savedAt");
        $this->isSaved = ! empty($this->packagings);

        if (empty($this->packagings)) {
            $this->addPackaging();
        } else {
            foreach ($this->packagings as &$item) {
                if (empty($item['second_inspection_document_number'])) {
                    $item['second_inspection_document_number'] =
                        $this->second_inspection_document_number;
                }
            }
        }

        $this->ensureRowKeys();

        if (! empty($this->sessionSaved)) {
            foreach ($this->sessionSaved as $i => $row) {
                if (empty($row['row_key'])) {
                    $this->sessionSaved[$i]['row_key'] = (string) \Illuminate\Support\Str::uuid();
                }
            }
        }
        $this->buildBaselineMap();
    }

    public function addPackaging()
    {
        $this->packagings[] = [
            'row_key' => (string) Str::uuid(),
            'second_inspection_document_number' => $this->second_inspection_document_number,
        ];
    }

    public function removePackaging($index)
    {
        unset($this->packagings[$index]);
        $this->packagings = array_values($this->packagings);
    }

    public function updated($property, $value)
    {
        $this->validateOnly($property);

        if (Str::endsWith($property, '.judgement')) {
            $index = (int) Str::between($property, 'packagings.', '.judgement');

            if ($value === 'OK') {
                unset($this->packagings[$index]['remarks']);
            }
        }
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.packagings.{$this->periodKey}", $this->packagings);
        $this->savedAt = now()->toIso8601String();
        session()->put(
            "stepDetailSaved.packagings_meta.{$this->periodKey}.savedAt",
            $this->savedAt,
        );

        $this->sessionSaved = $this->packagings;
        $this->buildBaselineMap();
        $this->isSaved = ! empty($this->packagings);

        $this->dispatch('packagingSaved', savedAt: $this->savedAt);
        $this->dispatch('toast', message: 'Packaging saved successfully!');
    }

    public function resetStep()
    {
        $this->packagings = [];
        $this->forgetNestedKey('stepDetailSaved.packagings', $this->periodKey);
        $this->forgetNestedKey('stepDetailSaved.packagings_meta', $this->periodKey);
        $this->resetValidation();
        $this->addPackaging();

        $this->sessionSaved = [];
        $this->baselineKey = [];
        $this->savedAt = null;
        $this->isSaved = false;

        $this->dispatch('packagingReset');
        $this->dispatch('toast', message: 'Packaging reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-packaging');
    }
}
