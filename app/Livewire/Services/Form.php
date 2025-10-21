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

    protected function messages(): array
    {
        return [
            'service_date.required' => 'Service date is required.',
            'service_date.date' => 'Service date must be a valid date.',

            'odometer.integer' => 'Odometer must be an integer.',
            'odometer.min' => 'Odometer must be at least :min.',

            'workshop.string' => 'Workshop must be a string.',
            'workshop.max' => 'Workshop may not be greater than :max characters.',

            'notes.string' => 'Notes must be a string.',

            'global_tax_rate.numeric' => 'Global tax rate must be a number.',
            'global_tax_rate.min' => 'Global tax rate must be at least :min.',

            'items.*.part_name.required' => 'Item name is required.',
            'items.*.part_name.string' => 'Item name must be a string.',
            'items.*.part_name.max' => 'Item name may not be greater than :max characters.',

            'items.*.action.required' => 'Item action is required.',
            'items.*.action.in' => 'Item action must be one of: checked, replaced, repaired, topped_up, cleaned.',

            'items.*.qty.required' => 'Item quantity is required.',
            'items.*.qty.numeric' => 'Item quantity must be a number.',
            'items.*.qty.min' => 'Item quantity must be at least :min.',

            'items.*.uom.required' => 'Item unit of measure is required.',
            'items.*.uom.string' => 'Item unit of measure must be a string.',
            'items.*.uom.max' => 'Item unit of measure may not be greater than :max characters.',

            'items.*.unit_cost.required' => 'Item unit cost is required.',
            'items.*.unit_cost.numeric' => 'Item unit cost must be a number.',
            'items.*.unit_cost.min' => 'Item unit cost must be at least :min.',

            'items.*.discount.required' => 'Item discount is required.',
            'items.*.discount.numeric' => 'Item discount must be a number.',
            'items.*.discount.min' => 'Item discount must be at least :min.',
            'items.*.discount.max' => 'Item discount may not be greater than :max.',

            'items.*.tax_rate.numeric' => 'Item tax rate must be a number.',
            'items.*.tax_rate.min' => 'Item tax rate must be at least :min.',
            'items.*.tax_rate.max' => 'Item tax rate may not be greater than :max.',

            'items.*.remarks.string' => 'Item remarks must be a string.',
            'items.*.remarks.max' => 'Item remarks may not be greater than :max characters.',
        ];
    }

    public function mount(?Vehicle $vehicle, ?ServiceRecord $record)
    {
        if ($record?->exists) {
            $this->vehicle = $record->vehicle;

            // only allow edit if vehicle sold
            if (! $record && $this->vehicle->is_sold) {
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

    public function updatedGlobalTaxRate($value): void
    {
        $val = $value === null || $value === '' ? null : max(0, min(100, (float) $value));
        $this->global_tax_rate = $val;

        foreach ($this->items as $i => $row) {
            if (array_key_exists('tax_rate', $row) || $row['tax_rate'] === '' || $row['tax_rate'] === null) {
                $this->items[$i]['tax_rate'] = $val;
            }
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'part_name' => '',
            'action' => 'checked',
            'qty' => null,
            'uom' => null,
            'unit_cost' => null,
            'discount' => null,
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

        DB::transaction(function () use ($gtr) {
            $payload = [
                'vehicle_id' => $this->vehicle->id,
                'service_date' => $this->service_date,
                'odometer' => $this->odometer ?: null,
                'workshop' => $this->workshop ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => auth()->id(),
                'global_tax_rate' => $gtr,
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
                $disc = max(0, min(100, (float) ($row['discount'] ?? 0)));
                $trRaw = $row['tax_rate'] ?? null;

                // normalized per-item tax rate or null (inherit)
                $tr = ($trRaw === '' || $trRaw === null) ? null : max(0, min(100, (float) $trRaw));

                $base = $qty * $uc * (1 - $disc / 100);
                $rate = $tr ?? ($gtr ?? 0.0);
                $tax = $base * ($rate / 100);

                $base = round($base, 2);
                $tax = round($tax, 2);
                $line = round($base + $tax, 2);

                $total += $line;

                $data = [
                    'part_name' => $row['part_name'],
                    'action' => $row['action'],
                    'qty' => $row['qty'],
                    'uom' => $row['uom'] ?: null,
                    'unit_cost' => $uc,
                    'discount' => $disc,
                    'tax_rate' => $tr ?? 0,
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
