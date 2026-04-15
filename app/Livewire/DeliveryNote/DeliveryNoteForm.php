<?php

namespace App\Livewire\DeliveryNote;

use App\Domains\Operations\Actions\CreateDeliveryNote;
use App\Domains\Operations\Actions\UpdateDeliveryNote;
use App\Infrastructure\Persistence\Eloquent\Models\DeliveryNote;
use App\Infrastructure\Persistence\Eloquent\Models\Destination;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('new.layouts.app')]
class DeliveryNoteForm extends Component
{
    public ?DeliveryNote $deliveryNote = null;

    public bool $is_draft = false;

    public $branch = 'JAKARTA';

    public $ritasi = 1;

    public $delivery_note_date;

    public $departure_time;

    public $return_time;

    public $vehicle_id;

    public $approval_flow_id;

    public $destinationSuggestions = [];

    public $vehicleSuggestions = [];

    public $destinations = [
        [
            'destination' => '',
            'delivery_order_numbers' => [],
            'remarks' => '',
            'driver_cost' => 0,
            'kenek_cost' => 0,
            'balikan_cost' => 0,
            'driver_cost_currency' => 'IDR',
            'kenek_cost_currency' => 'IDR',
            'balikan_cost_currency' => 'IDR',
        ],
    ];

    protected $rules = [
        'branch' => 'required|in:JAKARTA,KARAWANG',
        'ritasi' => 'required|integer|min:1|max:4',
        'delivery_note_date' => 'required|date',
        'departure_time' => 'nullable|date_format:H:i',
        'return_time' => 'nullable|date_format:H:i',
        'vehicle_id' => 'required|exists:vehicles,id',
        'destinations' => 'required|array|min:1',
        'destinations.*.destination' => 'nullable|string',
        'destinations.*.delivery_order_numbers' => 'nullable|array',
        'destinations.*.remarks' => 'nullable|string',
        'destinations.*.driver_cost' => 'nullable|numeric|min:0',
        'destinations.*.kenek_cost' => 'nullable|numeric|min:0',
        'destinations.*.balikan_cost' => 'nullable|numeric|min:0',
        'destinations.*.driver_cost_currency' => 'nullable|string',
        'destinations.*.kenek_cost_currency' => 'nullable|string',
        'destinations.*.balikan_cost_currency' => 'nullable|string',
    ];

    protected $messages = [
        'destinations.*.delivery_order_numbers.array' => 'Delivery Order numbers must be one or more items.',
        'destinations.*.remarks.string' => 'Remarks must be a string.',
        'destinations.*.driver_cost.numeric' => 'Driver cost must be a number.',
        'destinations.*.kenek_cost.numeric' => 'Kenek cost must be a number.',
        'destinations.*.balikan_cost.numeric' => 'Balikan cost must be a number.',
    ];

    public function mount(?DeliveryNote $deliveryNote)
    {
        if ($deliveryNote && $deliveryNote->exists) {
            // If not logged in and this is not the latest note, restrict
            if (! auth()->check()) {
                $latestId = DeliveryNote::max('id');
                if ($deliveryNote->id !== $latestId) {
                    abort(403, 'Guests can only edit the latest delivery note.');
                }
            }

            $this->deliveryNote = $deliveryNote;
            $this->branch = $deliveryNote->branch;
            $this->ritasi = $deliveryNote->ritasi;
            $this->delivery_note_date = $deliveryNote->delivery_note_date;
            $this->departure_time = $deliveryNote->departure_time;
            $this->return_time = $deliveryNote->return_time;
            $this->vehicle_id = $deliveryNote->vehicle_id;
            $this->approval_flow_id = $deliveryNote->approval_flow_id;
            $this->destinations = $deliveryNote->destinations
                ->map(function ($d) {
                    return [
                        'destination' => $d->destination,
                        'delivery_order_numbers' => $d->deliveryOrders
                            ->pluck('delivery_order_number')
                            ->toArray(),
                        'remarks' => $d->remarks,
                        'driver_cost' => $d->driver_cost,
                        'kenek_cost' => $d->kenek_cost,
                        'balikan_cost' => $d->balikan_cost,
                        'driver_cost_currency' => $d->driver_cost_currency,
                        'kenek_cost_currency' => $d->kenek_cost_currency,
                        'balikan_cost_currency' => $d->balikan_cost_currency,
                    ];
                })
                ->toArray();
        }
        $this->is_draft = $deliveryNote?->status === 'draft';
        $this->destinationSuggestions = Destination::select('name', 'city')->get()->toArray();
        $this->vehicleSuggestions = \App\Infrastructure\Persistence\Eloquent\Models\Vehicle::select('id', 'plate_number', 'driver_name')
            ->get()
            ->toArray();
    }

    public function addDestination()
    {
        if (count($this->destinations) > 10) {
            session()->flash('error', 'Maximum 10 destinations allowed.');

            return;
        }

        $this->destinations[] = [
            'destination' => '',
            'delivery_order_numbers' => [],
            'remarks' => '',
            'driver_cost' => 0,
            'driver_cost_currency' => 'IDR',
            'kenek_cost' => 0,
            'kenek_cost_currency' => 'IDR',
            'balikan_cost' => 0,
            'balikan_cost_currency' => 'IDR',
        ];
    }

    public function removeDestination($index)
    {
        unset($this->destinations[$index]);
        $this->destinations = array_values($this->destinations);
    }

    public function updated($propertyName)
    {
        if (str($propertyName)->contains('delivery_order_numbers')) {
            $index = (int) explode('.', $propertyName)[1];
            $raw = $this->destinations[$index]['delivery_order_numbers'];
            if (is_string($raw)) {
                $this->destinations[$index]['delivery_order_numbers'] = array_map(
                    'trim',
                    explode(',', $raw),
                );
            }
        }
    }

    public function getTotalCostProperty()
    {
        $total = 0;
        foreach ($this->destinations as $dest) {
            $total += (float) ($dest['driver_cost'] ?? 0);
            $total += (float) ($dest['kenek_cost'] ?? 0);
            $total += (float) ($dest['balikan_cost'] ?? 0);
        }

        return $total;
    }

    public function submit(CreateDeliveryNote $createAction, UpdateDeliveryNote $updateAction)
    {
        $this->validate();

        $data = [
            'branch' => $this->branch,
            'ritasi' => $this->ritasi,
            'delivery_note_date' => $this->delivery_note_date,
            'departure_time' => $this->departure_time,
            'return_time' => $this->return_time,
            'vehicle_id' => $this->vehicle_id,
        ];

        if ($this->deliveryNote?->exists) {
            $note = $updateAction->execute($this->deliveryNote, $data, $this->destinations, $this->is_draft);
        } else {
            $note = $createAction->execute($data, $this->destinations, $this->is_draft);
        }

        $this->deliveryNote = null;

        session()->flash(
            'success',
            $this->is_draft ? 'Draft saved successfully!' : 'Delivery Note saved successfully!',
        );

        return redirect()->route('delivery-notes.show', $note->id);
    }

    public function render()
    {
        if (! auth()->check()) {
            return view('livewire.delivery-note.form');
        }

        return view('livewire.delivery-note.form');
    }
}
