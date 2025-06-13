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
        $this->period = session('stepDetailSaved.period') ?? 1;

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
        $this->periodKey = "q{$this->period}";
        if (session("stepDetailSaved.details.$this->periodKey.document_number")) {
            $this->document_number = session("stepDetailSaved.details.$this->periodKey.document_number");
        } else {
            $this->document_number = 'DETAIL-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
        }
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

    public function resetStep(): void
    {
        $this->reset([
            'start_datetime',
            'end_datetime',
            'start_time',
            'end_time',
        ]);

        $this->period = 1;
        $this->updatedPeriod();
        session()->forget('stepDetailSaved');
        $this->dispatch('toast', message: 'Step reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-detail');
    }
}
