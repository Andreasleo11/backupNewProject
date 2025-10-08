<?php

namespace App\Livewire;

use App\Models\MasterDataPart;
use App\Models\MasterDataPartPriceLog;
use App\Models\MasterDataRogPartName;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ReportWizardDetails extends Component
{
    public $reportId;

    public $details = [];

    public $partSuggestions = [];

    public $suggestionPages = [];

    public array $priceCache = []; // [part_code => float]

    public function mount($reportId = null)
    {
        $this->reportId = $reportId;
        $this->details = session('report.details', []);

        // If editing and details are loaded from DB, format prices
        if ($this->details) {
            foreach ($this->details as &$detail) {
                // Make sure keys exists
                $detail['part_code'] = $detail['part_code'] ?? null;

                if (isset($detail['price']) && is_numeric($detail['price'])) {
                    // Format as thousand-separated string without decimals
                    $detail['price'] = number_format((float) $detail['price'], 2);
                } else {
                    $detail['price'] = '';
                }

                if (! $detail['part_code'] && ! empty($detail['part_name'])) {
                    $parts = explode('/', $detail['part_name']);
                    $part_name = $parts[1];
                    // dd($part_name);
                    $part = MasterDataPart::select('item_no')
                        ->where('description', $part_name)
                        ->first();
                    if ($part) {
                        $detail['part_code'] = $part->code;

                        if (empty($detail['price'])) {
                            $latest = $this->getLatestPriceForCode($part->code);
                            if ($latest !== null) {
                                $detail['price'] = number_format($latest, 2);
                            }
                        }
                    }
                }
            }
            unset($detail);
        }

        $this->partSuggestions = [];
        if (! $this->details) {
            $this->addDetailRow();
        }
    }

    private function getLatestPriceForCode(?string $partCode): ?float
    {
        if (! $partCode) {
            return null;
        }

        if (array_key_exists($partCode, $this->priceCache)) {
            return $this->priceCache[$partCode];
        }

        $price = MasterDataPartPriceLog::where('part_code', $partCode)
            ->where('currency', 'IDR')
            ->orderByDesc('created_at')
            ->value('price');

        if ($price !== null) {
            // dd($price);
            return $this->priceCache[$partCode] = (float) $price;
        }

        return null;
    }

    private function resolvePartByCode(?string $args): ?array
    {
        if (! $args) {
            return null;
        }
        $parts = explode('/', $args);
        $part_no = $parts[0];

        $row = MasterDataPart::select('item_no', 'description')
            ->where('item_no', $part_no)
            ->first();

        return $row ? ['code' => $row->item_no, 'name' => $row->description] : null;
    }

    public function updatedDetails($value, $name)
    {
        if (str_ends_with($name, 'part_name')) {
            $parts = explode('.', $name);
            $index = $parts[0];
            $this->suggestionPages[$index] = 1; // reset to page 1
            $this->loadSuggestions($index, $value);

            // If exact match -> set part_name & price
            if ($value && ($part = $this->resolvePartByCode($value))) {
                $latestPrice = $this->getLatestPriceForCode($part['code']);
                if ($latestPrice !== null) {
                    $this->details[$index]['price'] = number_format($latestPrice, 2);
                }
            } else {
                $this->details[$index]['price'] = '0';
            }
        }
    }

    public function loadMoreSuggestions($index)
    {
        $this->suggestionPages[$index] = ($this->suggestionPages[$index] ?? 1) + 1;
        $search = $this->details[$index]['part_name'] ?? '';
        $this->loadSuggestions($index, $search);
    }

    private function loadSuggestions($index, $search)
    {
        if (strlen($search) > 0) {
            $limit = ($this->suggestionPages[$index] ?? 1) * 10;

            $this->partSuggestions[$index] = MasterDataRogPartName::where(
                'name',
                'like',
                "%{$search}%",
            )
                ->orderBy('name')
                ->limit($limit)
                ->pluck('name')
                ->toArray();
        } else {
            $this->partSuggestions[$index] = [];
        }
    }

    public function selectPartSuggestion($index, $value)
    {
        $this->details[$index]['part_name'] = $value;
        $this->partSuggestions[$index] = [];

        if ($part = $this->resolvePartByCode($value)) {
            $latestPrice = $this->getLatestPriceForCode($part['code']);
            if ($latestPrice !== null) {
                $this->details[$index]['price'] = number_format($latestPrice, 2);
            } else {
                $this->details[$index]['price'] = '0';
            }
        }
    }

    public function addDetailRow()
    {
        $this->details[] = [
            'part_name' => '',
            'part_code' => null, // <-- add this
            'rec_quantity' => '',
            'verify_quantity' => '',
            'can_use' => '',
            'cant_use' => '',
            'price' => '',
        ];
    }

    public function removeDetailRow($index)
    {
        if (count($this->details) <= 1) {
            $this->dispatch('showAlert', 'At least one detail must remain.');

            return;
        }

        unset($this->details[$index]);
        $this->details = array_values($this->details);
    }

    public function saveDetails()
    {
        // Validate that at least one row exists
        $this->validate(
            [
                'details' => 'required|array|min:1',
            ],
            [
                'details.required' => 'You must add at least one detail row.',
                'details.min' => 'You must add at least one detail row.',
            ],
        );

        foreach ($this->details as $index => &$detail) {
            $this->validate(
                [
                    "details.$index.part_name" => 'required|string',
                    "details.$index.rec_quantity" => 'required|integer|min:0',
                    "details.$index.verify_quantity" => 'required|integer|min:0',
                    "details.$index.can_use" => 'required|integer|min:0',
                    "details.$index.cant_use" => 'required|integer|min:0',
                    "details.$index.price" => 'required|string',
                ],
                [
                    "details.$index.part_name.required" => 'Part name in row #'.($index + 1).' is required.',
                    "details.$index.rec_quantity.required" => 'Received quantity in row #'.($index + 1).' is required.',
                    "details.$index.rec_quantity.integer" => 'Received quantity in row #'.($index + 1).' must be a number.',
                    "details.$index.rec_quantity.min" => 'Received quantity in row #'.($index + 1).' cannot be negative.',

                    "details.$index.verify_quantity.required" => 'Verified quantity in row #'.($index + 1).' is required.',
                    "details.$index.verify_quantity.integer" => 'Verified quantity in row #'.($index + 1).' must be a number.',
                    "details.$index.verify_quantity.min" => 'Verified quantity in row #'.($index + 1).' cannot be negative.',

                    "details.$index.can_use.required" => 'Can-use quantity in row #'.($index + 1).' is required.',
                    "details.$index.can_use.integer" => 'Can-use quantity in row #'.($index + 1).' must be a number.',
                    "details.$index.can_use.min" => 'Can-use quantity in row #'.($index + 1).' cannot be negative.',

                    "details.$index.cant_use.required" => "Can't-use quantity in row #".($index + 1).' is required.',
                    "details.$index.cant_use.integer" => "Can't-use quantity in row #".($index + 1).' must be a number.',
                    "details.$index.cant_use.min" => "Can't-use quantity in row #".($index + 1).' cannot be negative.',

                    "details.$index.price.required" => 'Price in row #'.($index + 1).' is required.',
                ],
            );
            $detail['price'] = (float) str_replace(',', '', $detail['price']);
        }
        unset($detail); // avoid accidental reference carry-over

        Session::put('report.details', $this->details);

        $this->dispatch('stepCompleted')->to('report-wizard');
    }

    public function goBack()
    {
        $this->dispatch('goBack')->to('report-wizard');
    }

    public function render()
    {
        return view('livewire.report-wizard-details');
    }
}
