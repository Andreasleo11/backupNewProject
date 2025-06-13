<?php

namespace App\Livewire\InspectionForm;

use App\Models\InspectionForm\DetailInspectionReport;
use App\Models\InspectionForm\FirstInspection;
use App\Models\InspectionForm\InspectionMeasurement;
use App\Models\InspectionForm\InspectionPackaging;
use App\Models\InspectionForm\InspectionProblem;
use App\Models\InspectionForm\InspectionReport;
use App\Models\InspectionForm\InspectionSampling;
use App\Models\InspectionForm\SecondInspection;
use App\Models\InspectionJudgement;
use App\Models\InspectionQuantity;
use App\Services\PeriodValidator;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FinalSubmit extends Component
{
    public $headerData;
    public $detailData;
    public $firstData;
    public $measurementData;
    public $secondData;
    public $samplingData;
    public $packagingData;
    public $judgementData;
    public $problemData;
    public $quantityData;

    public array $holeReport = [];      // exposed to view

    /** Map backend keys → nice labels shown to the user */
    protected array $sectionLabels = [
        'details'            => 'Detail Inspection',
        'first_inspections'  => 'First Data',
        'second_inspections' => 'Second Data',
        'samples'            => 'Sampling Data',
        'packagings'         => 'Packaging Data',
        'judgements'         => 'Judgement Data',
        'problems'           => 'Problem Data',
        'quantities'         => 'Quantity Data',
    ];

    public function mount()
    {
        $this->headerData = session('stepHeaderSaved', []);

        $processedDetailData = [];

        if (!empty(session('stepDetailSaved.details'))) {
            foreach (session('stepDetailSaved.details') as $periodKey => $rawData) {
                $periodNumber = (int) substr($periodKey, 1);
                $processedDetailData[$periodKey] = [
                    'inspection_report_document_number' => $rawData['inspection_report_document_number'],
                    'document_number' => $rawData['document_number'],
                    'period' => $periodNumber,
                    'start_datetime' => $rawData['start_datetime'],
                    'end_datetime' => $rawData['end_datetime']
                ];
            }
        }

        $this->detailData = $processedDetailData;

        $this->firstData = session('stepDetailSaved.first_inspections', []);
        $this->measurementData = session('stepDetailSaved.measurements', []);
        $this->secondData = session('stepDetailSaved.second_inspections', []);
        $this->samplingData = session('stepDetailSaved.samples', []);
        $this->packagingData = session('stepDetailSaved.packagings', []);
        $this->judgementData = session('stepDetailSaved.judgements', []);
        $this->problemData = session('stepDetailSaved.problems', []);
        $this->quantityData = session('stepDetailSaved.quantities', []);

        $this->holeReport = $this->computeHoleReport();
    }

    protected function computeHoleReport(): array
    {
        $payload = [
            'details'            => session('stepDetailSaved.details'),
            'first_inspections'  => session('stepDetailSaved.first_inspections'),
            'second_inspections' => session('stepDetailSaved.second_inspections'),
            'samples'            => session('stepDetailSaved.samples'),
            'packagings'         => session('stepDetailSaved.packagings'),
            'judgements'         => session('stepDetailSaved.judgements'),
            'problems'           => session('stepDetailSaved.problems'),
            'quantities'         => session('stepDetailSaved.quantities'),
        ];

        return PeriodValidator::missing($payload);
    }

    /** ------------------------------------------------------------------
     *  Return two arrays:
     *    [$complete, $incomplete]
     *  where each item is the period number (int).
     */
    protected function splitCompletePeriods(): array
    {
        // gather all periods seen anywhere (p1-p4)
        $allP = collect($this->detailData)->keys()
            ->map(fn($k) => (int) substr($k, 1))
            ->unique()
            ->sort()
            ->values();

        $required = [
            'detailData',
            'firstData',
            'secondData',
            'samplingData',
            'packagingData',
            'judgementData',
            'problemData',
            'quantityData',
        ];

        $complete   = [];
        $incomplete = [];

        foreach ($allP as $p) {
            $hasAll = collect($required)->every(function ($prop) use ($p) {
                return isset($this->{$prop}["p{$p}"]) && !empty($this->{$prop}["p{$p}"]);
            });

            if ($hasAll) {
                $complete[] = $p;        // push into the “complete” bucket
            } else {
                $incomplete[] = $p;      // push into the “incomplete” bucket
            }
        }

        return [$complete, $incomplete];
    }

    public function submit()
    {
        [$completeP, $incompleteP] = $this->splitCompletePeriods();

        // no period fully filled?  -> hard-stop
        if (empty($completeP)) {
            $this->dispatch(
                'toast',
                message: 'No period is complete – nothing was saved.',
                type: 'error'
            );
            return;
        }

        // 1) trim every section array so it only keeps complete periods
        foreach (
            [
                'detailData',
                'firstData',
                'measurementData',
                'secondData',
                'samplingData',
                'packagingData',
                'judgementData',
                'problemData',
                'quantityData'
            ] as $prop
        ) {
            $this->{$prop} = collect($this->{$prop})
                ->only(array_map(fn($p) => "p{$p}", $completeP))
                ->all();
        }

        DB::transaction(function () {
            InspectionReport::create($this->headerData);
            foreach ($this->detailData as $data) {
                DetailInspectionReport::create($data);
            }
            foreach ($this->firstData as $data) {
                FirstInspection::create($data);
            }
            foreach ($this->measurementData as $value) {
                foreach ($value as $data) {
                    InspectionMeasurement::create($data);
                }
            }
            foreach ($this->secondData as $data) {
                SecondInspection::create($data);
            }
            foreach ($this->samplingData as $value) {
                foreach ($value as $data) {
                    InspectionSampling::create($data);
                }
            }
            foreach ($this->packagingData as $value) {
                foreach ($value as $data) {
                    InspectionPackaging::create($data);
                }
            }
            foreach ($this->judgementData as $data) {
                InspectionJudgement::create($data);
            }
            foreach ($this->problemData as $value) {
                foreach ($value as $data) {
                    InspectionProblem::create($data);
                }
            }
            foreach ($this->quantityData as $data) {
                InspectionQuantity::create($data);
            }
        });

        $ok  = implode(', ', array_map(fn($p) => "P{$p}", $completeP));
        $bad = implode(', ', array_map(fn($p) => "P{$p}", $incompleteP));

        $this->dispatch(
            'toast',
            message: "Saved periods: {$ok}" . ($bad ? ". Skipped: {$bad}" : ''),
            type: $bad ? 'warning' : 'success'
        );
        $this->dispatch('toast', message: 'Inspection report submitted successfully!');
        session()->forget([
            'stepHeaderSaved',
            'stepDetailSaved',
            'lastStepVisited'
        ]);
        redirect()->route('inspection-report.index');
    }

    public function render()
    {
        return view('livewire.inspection-form.final-submit');
    }
}
