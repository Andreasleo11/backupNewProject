<?php

namespace App\Livewire\InspectionForm;

use Livewire\Component;
use Illuminate\Support\Str;

class StepDetail extends Component
{
    public $inspection_report_document_number;
    public $document_number;
    public $period;
    public $start_datetime;
    public $end_datetime;

    // Additional Properties
    public $start_time;
    public $end_time;
    public $operator;
    public $shift;

    public $part_name;
    public $part_number;

    public $periodKey;

    // Parent Livewire
    public int $reloadToken = 0;

    protected $rules = [
        'inspection_report_document_number' => 'required|string',
        'document_number' => 'required|string',
        'period' => 'required|integer|min:1|max:4',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i',
    ];

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function mount()
    {
        $saved = session('stepDetailSaved');
        // dd($saved);
        $this->period = data_get($saved, 'period', 1);

        $this->generateDocumentNumber();

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }

                if (!empty($saved['start_datetime'])) {
                    $this->start_time = \Carbon\Carbon::parse($saved['start_datetime'])->format('H:i');
                }

                if (!empty($saved['end_datetime'])) {
                    $this->end_time = \Carbon\Carbon::parse($saved['end_datetime'])->format('H:i');
                }
            }
        }

        $this->shift = session('stepHeaderSaved.shift') ?? null;
        $this->operator = session('stepHeaderSaved.operator') ?? 'N/A';
        $this->part_name = session('stepHeaderSaved.part_name') ?? 'N/A';
        $this->part_number = session('stepHeaderSaved.part_number') ?? 'N/A';
        // dd($this->period);

        if ($this->shift) $this->updatedPeriod();
    }

    public function updatedPeriod()
    {
        $shift = $this->shift;
        $period = $this->period;

        if (!$shift || !$period) return;

        // Define shift base times
        $shiftTimes = [
            1 => ['07:30', '15:30'],
            2 => ['15:30', '23:30'],
            3 => ['23:30', '07:30'], // special handling for next-day wrap
        ];

        $periods = [
            '1' => 0,
            '2' => 1,
            '3' => 2,
            '4' => 3,
        ];

        $periodOffset = $periods[$period] ?? 0;
        [$shiftStart, $shiftEnd] = $shiftTimes[$shift];

        // Use Carbon to calculate time
        $start = \Carbon\Carbon::createFromTimeString($shiftStart);
        $end = \Carbon\Carbon::createFromTimeString($shiftEnd);

        // Handle Shift 3 (spans over midnight)
        if ($shift == 3 && $end->lessThan($start)) {
            $end->addDay(); // set to next day
        }

        // Calculate time per period (2 hours)
        $startPeriod = $start->copy()->addHours($periodOffset * 2);
        $endPeriod = $startPeriod->copy()->addHours(2);

        // Clamp end to shift end
        if ($endPeriod->greaterThan($end)) {
            $endPeriod = $end;
        }

        // Set formatted times
        $this->start_datetime = $startPeriod->format('Y-m-d H:i:s');
        $this->end_datetime = $endPeriod->format('Y-m-d H:i:s');
        $this->start_time = $startPeriod->format('H:i');
        $this->end_time = $endPeriod->format('H:i');

        // $this->persistPeriod();
    }

    public function selectPeriod(int $q): void
    {
        $this->period = $q;               // triggers updatedPeriod()
        $this->updatedPeriod();           // (ensures time calc runs)
        $this->generateDocumentNumber();
        // $this->persistPeriod();           // save to session
    }

    private function generateDocumentNumber()
    {
        $this->periodKey = $this->periodKey();
        if (session("stepDetailSaved.details.$this->periodKey.document_number")) {
            $this->document_number = session("stepDetailSaved.details.$this->periodKey.document_number");
        } else {
            $this->document_number = 'DETAIL-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
        }
    }

    private function periodKey(?int $period = null): string
    {
        $p = $period ?? $this->period ?? 1;
        return 'p' . $p;
    }

    public function saveStep(): void
    {
        $this->validate();
        $data = session('stepDetailSaved', []);
        // keep the shift straight from StepHeader
        $data['shift'] = session('stepHeaderSaved.shift') ?? $data['shift'];
        $data['period'] = $this->period ?? $data['period'];

        $this->periodKey = 'p' . $this->period;

        // stash whatever fields you need for this period
        $data['details'][$this->periodKey] = [
            'document_number' => $this->document_number,
            'inspection_report_document_number' => $this->inspection_report_document_number,
            'start_datetime' => $this->start_datetime,
            'end_datetime'   => $this->end_datetime,
        ];

        session(['stepDetailSaved' => $data]);

        session()->put('stepDetailSaved.details', $data['details']);
        $this->reloadToken++;
        $this->dispatch('toast', message: 'Detail saved successfully!');
    }

    public function resetStep(bool $clearOnlyCurrentPeriod = true): void
    {
        // 1) Determine which period to reset 
        $saved = session('stepDetailSaved', []);
        // $targetPeriod = data_get($saved, 'period', $this->period ?? 1);
        // $this->period = (int) $targetPeriod;

        // 2) Clear current form fields (for the selected period)
        $this->reset([
            'start_datetime',
            'end_datetime',
            'start_time',
            'end_time',
        ]);

        // 3) Optionally clear only this period’s data in the session
        if ($clearOnlyCurrentPeriod) {
            $pk = $this->periodKey($this->period);

            // Remove this period from each section if it exists
            $sections = [
                'details',
                'first_inspections',
                'measurements',
                'second_inspections',
                'samples',
                'packagings',
                'judgements',
            ];

            foreach ($sections as $section) {
                if (isset($saved[$section][$pk])) {
                    unset($saved[$section][$pk]);
                }
            }

            // Keep shift & period in the session
            $saved['period'] = $this->period;
            session(['stepDetailSaved' => $saved]);
        } else {
            // Or wipe all detail state (full reset)
            session()->forget('stepDetailSaved');
        }

        // 4) Recompute default time window for the (saved) period & regenerate doc number
        $this->updatedPeriod();
        $this->generateDocumentNumber();

        // 5) UI refresh token + toast
        $this->reloadToken++;
        $this->dispatch('toast', message: 'Step reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-detail');
    }
}
