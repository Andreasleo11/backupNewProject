<?php

namespace App\Livewire\Services;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordItem;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    public ?Vehicle $vehicle = null; // for create

    public ?ServiceRecord $record = null; // for edit

    // Header Fields
    #[Validate('required|date')]
    public $service_date;

    public $odometer;

    public $workshop;

    public $notes;

    public ?float $global_tax_rate = null;

    // Items (repeater)
    public array $items = [
        // ['part_name' => 'Engine Oil', 'action' => 'replaced', 'qty' => 4, 'uom' => 'L', 'unit_cost' => 150000, 'remarks' => null],
    ];

    protected function rules(): array
    {
        return [
            'service_date' => ['required', 'date'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'workshop' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
            'global_tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.part_name' => ['required', 'string', 'max:255'],
            'items.*.action' => ['required', 'in:checked,replaced,repaired,topped_up,cleaned'],
            'items.*.qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom' => ['nullable', 'string', 'max:20'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'], // override % or null
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function mount(?Vehicle $vehicle, ?ServiceRecord $record)
    {
        if ($record?->exists) {
            $this->vehicle = $record->vehicle;

            if ($this->vehicle->is_sold) {
                abort(403, 'This vehicle has been sold and cannot receive new service records.');
            }

            $this->record = $record->load('items', 'vehicle');
            $this->service_date = $record->service_date->toDateString();
            $this->odometer = $record->odometer;
            $this->workshop = $record->workshop;
            $this->notes = $record->notes;
            $this->global_tax_rate = $record->global_tax_rate;
            $this->items = $record->items->map(fn ($i) => [
                'id' => $i->id,
                'part_name' => $i->part_name,
                'action' => $i->action,
                'qty' => $i->qty,
                'uom' => $i->uom,
                'unit_cost' => $i->unit_cost,
                'discount' => $i->discount ?? 0,
                'tax_rate' => $i->tax_rate,
                'remarks' => $i->remarks,
            ])->toArray();
        } else {
            $this->vehicle = $vehicle;
            $this->service_date = now()->toDateString();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'part_name' => '',
            'action' => 'checked',
            'qty' => null,
            'uom' => null,
            'unit_cost' => '',
            'discount' => 0,
            'tax_rate' => null,
            'remarks' => null,
        ];
    }

    public function removeItem($index): void
    {
        array_splice($this->items, (int) $index, 1);
    }

    public function save()
    {
        $this->validate();

        // Normalize percent ranges
        $gtr = $this->global_tax_rate;
        if ($gtr !== null) {
            $gtr = max(0, min(100, (float) $gtr));
        }

        DB::transaction(function () {
            $payload = [
                'vehicle_id' => $this->vehicle->id,
                'service_date' => $this->service_date,
                'odometer' => $this->odometer ?: null,
                'workshop' => $this->workshop ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => auth()->id(),
                'global_tax_rate' => $this->global_tax_rate,
            ];

            if ($this->record) {
                $this->record->update($payload);
            } else {
                $this->record = ServiceRecord::create($payload);
            }

            // sync items (upsert‑y)
            $keepIds = [];
            $total = 0.0;

            foreach ($this->items as $row) {
                $qty = (float) ($row['qty'] ?? 0);
                $uc = (float) ($row['unit_cost'] ?? 0);
                $disc = (float) ($row['discount'] ?? 0);
                $tr = $row['tax_rate'];
                $re = ($tr === '' || $tr === null) ? null : max(0, min(100, (float) $tr));

                $base = $qty * $uc * (1 - $disc / 100);
                $rate = $tr ?? ($gtr ?? 0.0);
                $tax = $base * ($rate / 100);
                $line = $base + $tax;
                $total += $line;

                $data = [
                    'part_name' => $row['part_name'],
                    'action' => $row['action'],
                    'qty' => $row['qty'],
                    'uom' => $row['uom'] ?: null,
                    'unit_cost' => $uc,
                    'discount' => $disc,
                    'tax_rate' => $tr,
                    'line_total' => $line,
                    'remarks' => $row['remarks'] ?: null,
                ];

                if (! empty($row['id'])) {
                    $item = ServiceRecordItem::query()->where('id', $row['id'])->where('service_record_id', $this->record->id)->first();
                    if ($item) {
                        $item->update($data);
                        $keepIds[] = $item->id;
                    }
                } else {
                    $item = $this->record->items()->create($data);
                    $keepIds[] = $item->id;
                }
            }

            // delete removed
            $this->record->items()->whereNotIn('id', $keepIds)->delete();

            // update total cost
            $this->record->update(['total_cost' => $total]);
        });

        session()->flash('success', 'Service saved.');

        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function render()
    {
        return view('livewire.services.form');
    }
}
