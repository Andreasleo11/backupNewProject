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

    // Items (repeater)
    public array $items = [
        // ['part_name' => 'Engine Oil', 'action' => 'replaced', 'qty' => 4, 'uom' => 'L', 'unit_cost' => 150000, 'remarks' => null],
    ];

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
            $this->items = $record->items->map(fn ($i) => [
                'id' => $i->id,
                'part_name' => $i->part_name,
                'action' => $i->action,
                'qty' => $i->qty,
                'uom' => $i->uom,
                'unit_cost' => $i->unit_cost,
                'discount' => $i->discount,
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
            'discount' => '',
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
        foreach ($this->items as $idx => $row) {
            $this->validate([
                "items.$idx.part_name" => 'required|string|max:120',
                "items.$idx.action" => 'required|in:checked,replaced,repaired,topped_up,cleaned',
                "items.$idx.qty" => 'nullable|numeric|min:0',
                "items.$idx.uom" => 'nullable|string|max:20',
                "items.$idx.unit_cost" => 'nullable|numeric|min:0',
                "items.$idx.discount" => 'nullable|numeric|min:0',
                "items.$idx.remarks" => 'nullable|string',
            ]);
        }

        DB::transaction(function () {
            $payload = [
                'vehicle_id' => $this->vehicle->id,
                'service_date' => $this->service_date,
                'odometer' => $this->odometer ?: null,
                'workshop' => $this->workshop ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => auth()->id(),
            ];

            if ($this->record) {
                $this->record->update($payload);
            } else {
                $this->record = ServiceRecord::create($payload);
            }

            // sync items (upsert‑y)
            $keepIds = [];
            foreach ($this->items as $row) {
                $qty = (float) ($row['qty'] ?? 0);
                $uc = (float) ($row['unit_cost'] ?? 0);
                $disc = (float) ($row['discount'] ?? 0);

                $disc = max(0, min(100, $disc));
                $line_total = $qty * $uc * (1 - $disc / 100);

                $data = [
                    'part_name' => $row['part_name'],
                    'action' => $row['action'],
                    'qty' => $row['qty'],
                    'uom' => $row['uom'],
                    'unit_cost' => $row['unit_cost'] ?? 0,
                    'discount' => $row['discount'] ?? 0,
                    'line_total' => $line_total,
                    'remarks' => $row['remarks'] ?? null,
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
            $this->record->update(['total_cost' => $this->record->items()->sum('line_total')]);
        });

        session()->flash('success', 'Service saved.');

        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function render()
    {
        return view('livewire.services.form');
    }
}
