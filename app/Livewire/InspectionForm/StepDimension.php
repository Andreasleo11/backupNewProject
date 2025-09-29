<?php

namespace App\Livewire\InspectionForm;

use App\Models\Upload;
use App\Traits\ClearsNestedSession;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StepDimension extends Component
{
    use ClearsNestedSession;

    public $dimensions = [];

    public $start_time;

    public $end_time;

    public $inspection_report_document_number;

    public $uploads;

    public $periodKey;

    public array $sessionSaved = [];

    public array $baselineByKey = [];

    public ?string $savedAt = null;

    public bool $isSaved = false;

    protected function norm($v)
    {
        return $v === '' ? null : $v;
    }

    protected function ensureRowKeys(): void
    {
        foreach ($this->dimensions as $i => $row) {
            if (! isset($row['row_key']) || ! $row['row_key']) {
                $this->dimensions[$i]['row_key'] = (string) Str::uuid();
            }
        }
    }

    protected function buildBaselineMap(): void
    {
        $this->baselineByKey = [];
        foreach ($this->sessionSaved as $row) {
            if (isset($row['row_key'])) {
                $this->baselineByKey[$row['row_key']] = $row;
            }
        }
    }

    public function isRowFieldSaved(string $rowKey, string $field): bool
    {
        $cur = $this->norm(
            data_get(collect($this->dimensions)->firstWhere('row_key', $rowKey), $field),
        );
        $base = $this->norm(data_get($this->baselineByKey[$rowKey] ?? [], $field));

        // dd($cur, $this->baselineByKey, $rowKey, $field);
        return $cur !== null && $cur === $base;
    }

    public function isRowGroupSaved(string $rowKey, array $fields): bool
    {
        foreach ($fields as $f) {
            if (! $this->isRowFieldSaved($rowKey, $f)) {
                return false;
            }
        }

        return true;
    }

    public function isTimeSaved(string $which): bool
    {
        if (empty($this->sessionSaved)) {
            return false;
        }
        $first = $this->sessionSaved[0] ?? null;
        if (! $first) {
            return false;
        }

        $key = $which === 'start' ? 'start_datetime' : 'end_datetime';
        $baseline = data_get($first, $key);
        if (! $baseline) {
            return false;
        }

        $baselineTime = Carbon::parse($baseline)->format('H:i');
        $current = $which === 'start' ? $this->start_time : $this->end_time;

        $cur = $this->norm($current);
        $base = $this->norm($baselineTime);

        return $cur !== null && $cur === $base;
    }

    protected function rules(): array
    {
        $fifteen = function ($attribute, $value, $fail) {
            $minutes = \Carbon\Carbon::createFromFormat('H:i', $value)->minute;
            if ($minutes % 15 !== 0) {
                $fail('The '.$attribute.' must be in 15-minute increments (00, 15, 30, 45).');
            }
        };

        $rules = [
            'start_time' => ['required', 'date_format:H:i', $fifteen],
            'end_time' => ['required', 'date_format:H:i', $fifteen],
        ];

        foreach ($this->dimensions as $i => $row) {
            $rules["dimensions.$i.inspection_report_document_number"] = 'required|string';
            $rules["dimensions.$i.lower_limit"] = 'required|numeric';
            $rules["dimensions.$i.upper_limit"] = [
                'required',
                'numeric',
                "gt:dimensions.$i.lower_limit",
            ];
            $rules["dimensions.$i.limit_uom"] = 'required|string';
            $rules["dimensions.$i.judgement"] = 'required|in:OK,NG';
            $rules["dimensions.$i.area"] = 'required|string';
            $rules["dimensions.$i.remarks"] = [
                'nullable',
                'string',
                Rule::requiredIf(($row['judgement'] ?? '') === 'NG'),
            ];

            $lower = $row['lower_limit'] ?? null;
            $upper = $row['upper_limit'] ?? null;
            $actualRules = ['required', 'numeric'];
            if (($row['judgement'] ?? '') === 'OK' && is_numeric($lower) && is_numeric($upper)) {
                $actualRules[] = "between:$lower,$upper";
            }
            $rules["dimensions.$i.actual_value"] = $actualRules;
        }

        return $rules;
    }

    protected $messages = [
        'dimensions.*.inspection_report_document_number.required' => 'The inspection report document number is required.',
        'dimensions.*.limit_uom.required' => 'The limit unit of measure is required.',
        'dimensions.*.lower_limit.required' => 'The lower limit is required.',
        'dimensions.*.lower_limit.numeric' => 'The lower limit must be a number.',
        'dimensions.*.upper_limit.required' => 'The upper limit is required.',
        'dimensions.*.upper_limit.numeric' => 'The upper limit must be a number.',
        'dimensions.*.upper_limit.gt' => 'The upper limit must be greater than lower limit.',
        'dimensions.*.actual_value.between' => 'The actual value must be between the lower and upper limits.',
        'dimensions.*.limit_uom.string' => 'The limit unit of measure must be a string.',
        'dimensions.*.actual_value.numeric' => 'The actual value must be a number.',
        'dimensions.*.judgement.enum' => 'The judgement must be either OK or NG.',
        'dimensions.*.area.required' => 'The area is required.',
        'dimensions.*.area.string' => 'The area must be a string.',
        'dimensions.*.remarks.string' => 'The area must be a string.',
        'dimensions.*.remarks.required' => 'Remarks are required when judgement is NG.',
        'start_time.required' => 'The start time is required.',
        'start_time.date_format' => 'The start time must be in the format HH:mm.',
        'start_time.fifteen' => 'The start time must be in 15-minute',
        'end_time.required' => 'The end time is required.',
        'end_time.date_format' => 'The end time must be in the format HH:mm.',
        'end_time.fifteen' => 'The end time must be in 15-minute',
        'dimensions.*.actual_value.required' => 'The actual value is required.',
        'dimensions.*.actual_value.numeric' => 'The actual value must be a number.',
    ];

    public function mount($inspection_report_document_number = null)
    {
        $this->inspection_report_document_number = $inspection_report_document_number;
        $this->periodKey = 'p'.session('stepDetailSaved.period');

        $this->dimensions = session("stepDetailSaved.dimensions.{$this->periodKey}", []);
        $this->sessionSaved = session("stepDetailSaved.dimensions.{$this->periodKey}", []);
        $this->savedAt = session("stepDetailSaved.dimensions_meta.{$this->periodKey}.savedAt");
        $this->isSaved = ! empty($this->dimensions);

        $this->ensureRowKeys();
        if (! empty($this->sessionSaved)) {
            foreach ($this->sessionSaved as $i => $row) {
                if (! isset($row['row_key']) || ! $row['row_key']) {
                    $this->sessionSaved[$i]['row_key'] = (string) Str::uuid();
                }
            }
        }
        $this->buildBaselineMap();

        if ($this->dimensions) {
            $first = $this->dimensions[0];
            if (! empty($first['start_datetime'])) {
                $this->start_time = Carbon::parse($first['start_datetime'])->format('H:i');
            }
            if (! empty($first['end_datetime'])) {
                $this->end_time = Carbon::parse($first['end_datetime'])->format('H:i');
            }
        }

        $part_code = session('stepHeaderSaved.part_number');
        $this->uploads = Upload::whereHas('tags', fn ($q) => $q->where('name', $part_code))->get();
        // if (empty($this->dimensions)) $this->addDimension();
    }

    public function addDimension()
    {
        $period = session('stepDetailSaved.period');

        $this->start_time = Carbon::parse(
            session('stepDetailSaved.details.'.'p'.$period.'.start_datetime'),
        )->format('H:i');

        $this->end_time = Carbon::parse(
            session('stepDetailSaved.details.'.'p'.$period.'.end_datetime'),
        )->format('H:i');

        $this->dimensions[] = [
            'row_key' => (string) Str::uuid(),
            'inspection_report_document_number' => $this->inspection_report_document_number,
            'lower_limit' => '',
            'upper_limit' => '',
            'limit_uom' => '',
            'actual_value' => '',
            'judgement' => '',
            'area' => '',
            'remarks' => '',
        ];
    }

    public function removeDimension($index)
    {
        unset($this->dimensions[$index]);
        $this->dimensions = array_values($this->dimensions);
    }

    public function updated($property, $value)
    {
        $this->validateOnly($property);

        if (Str::endsWith($property, '.judgement')) {
            $index = (int) Str::between($property, 'dimensions.', '.judgement');
            if ($value !== 'NG') {
                $this->dimensions[$index]['remarks'] = '';
            }
            $this->validate();
        }
    }

    public function saveStep()
    {
        $this->validate();

        foreach ($this->dimensions as $i => $row) {
            $this->dimensions[$i]['start_datetime'] = Carbon::parse($this->start_time)->format(
                'Y-m-d H:i:s',
            );
            $this->dimensions[$i]['end_datetime'] = Carbon::parse($this->end_time)->format(
                'Y-m-d H:i:s',
            );
            if ($this->dimensions[$i]['judgement'] === 'OK') {
                unset($this->dimensions[$i]['remarks']);
            }
        }

        session()->put("stepDetailSaved.dimensions.{$this->periodKey}", $this->dimensions);
        $this->savedAt = now()->toIso8601String();
        session()->put(
            "stepDetailSaved.dimensions_meta.{$this->periodKey}.savedAt",
            $this->savedAt,
        );

        $this->sessionSaved = $this->dimensions;
        $this->buildBaselineMap();
        $this->isSaved = ! empty($this->dimensions);

        $this->dispatch('toast', message: 'dimensions saved successfully!');
        $this->dispatch('dimensionsSaved', savedAt: $this->savedAt);
        $this->dispatch('dimensionsSaved')->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function resetStep()
    {
        $this->dimensions = [];
        $this->start_time = '';
        $this->end_time = '';
        $this->resetValidation();

        $this->forgetNestedKey('stepDetailSaved.dimensions', $this->periodKey);
        $this->forgetNestedKey('stepDetailSaved.dimensions_meta', $this->periodKey);

        $this->sessionSaved = [];
        $this->baselineByKey = [];
        $this->savedAt = null;

        $this->dispatch('toast', message: 'dimensions reset successfully!');
        $this->dispatch('dimensionsReset');
        $this->dispatch('dimensionsReset')->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function render()
    {
        return view('livewire.inspection-form.step-dimensions');
    }
}
