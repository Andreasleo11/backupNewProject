<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DeliveryNoteForm extends Component
{
    public ?DeliveryNote $deliveryNote = null;
    public bool $is_draft = false;
    public $branch = 'JAKARTA';
    public $ritasi = 1;
    public $delivery_note_date;
    public $departure_time;
    public $return_time;
    public $vehicle_number;
    public $driver_name;
    public $approval_flow_id;
    public $destinationSuggestions = [];
    public $vehicleSuggestions = [];

    public $destinations = [
        [
            'destination' => '',
            'delivery_order_numbers' => [],
            'remarks' => '',
            'driver_cost' => null,
            'kenek_cost' => null,
            'balikan_cost' => null,
            'driver_cost_currency' => 'IDR',
            'kenek_cost_currency' => 'IDR',
            'balikan_cost_currency' => 'IDR',
        ]
    ];

    protected $rules = [
        'branch' => 'required|in:JAKARTA,KARAWANG',
        'ritasi' => 'required|integer|min:1|max:4',
        'delivery_note_date' => 'required|date',
        'departure_time' => 'nullable|date_format:H:i',
        'return_time' => 'nullable|date_format:H:i',
        'vehicle_number' => 'required|string',
        'driver_name' => 'required|string',
        'destinations' => 'required|array|min:1',
        'destinations.*.destination' => 'required|string',
        'destinations.*.delivery_order_numbers' => 'required|array|min:1',
        'destinations.*.remarks' => 'nullable|string',
        'destinations.*.driver_cost' => 'nullable|numeric|min:0',
        'destinations.*.kenek_cost' => 'nullable|numeric|min:0',
        'destinations.*.balikan_cost' => 'nullable|numeric|min:0',
        'destinations.*.driver_cost_currency' => 'nullable|string',
        'destinations.*.kenek_cost_currency' => 'nullable|string',
        'destinations.*.balikan_cost_currency' => 'nullable|string'
    ];

    public function mount(?DeliveryNote $deliveryNote)
    {
        if ($deliveryNote && $deliveryNote->exists) {
            $this->deliveryNote = $deliveryNote;
            $this->branch = $deliveryNote->branch;
            $this->ritasi = $deliveryNote->ritasi;
            $this->delivery_note_date = $deliveryNote->delivery_note_date;
            $this->departure_time = $deliveryNote->formatted_departure_time;
            $this->return_time = $deliveryNote->formatted_return_time;
            $this->vehicle_number = $deliveryNote->vehicle_number;
            $this->driver_name = $deliveryNote->driver_name;
            $this->approval_flow_id = $deliveryNote->approval_flow_id;
            $this->destinations = $deliveryNote->destinations->map(function ($d) {
                return [
                    'destination' => $d->destination,
                    'delivery_order_numbers' => $d->deliveryOrders->pluck('delivery_order_number')->toArray(),
                    'remarks' => $d->remarks,
                    'driver_cost' => $d->driver_cost,
                    'kenek_cost' => $d->kenek_cost,
                    'balikan_cost' => $d->balikan_cost,
                    'driver_cost_currency' => $d->driver_cost_currency,
                    'kenek_cost_currency' => $d->kenek_cost_currency,
                    'balikan_cost_currency' => $d->balikan_cost_currency,
                ];
            })->toArray();
        }
        $this->is_draft = $deliveryNote?->status === 'draft';
        $this->destinationSuggestions = Destination::select('name', 'city')->get()->toArray();
        $this->vehicleSuggestions = \App\Models\Vehicle::select('plate_number', 'driver_name')->get()->toArray();
    }


    public function addDestination()
    {
        if (count($this->destinations) > 10) {
            session()->flash('error', 'Maximum 10 destinations allowed.');
            return;
        }
        $this->destinations[] = ['destination' => '', 'delivery_number' => '', 'remarks' => ''];
    }

    public function removeDestination($index)
    {
        unset($this->destinations[$index]);
        $this->destinations = array_values($this->destinations);
    }

    public function updated($propertyName)
    {
        if (str($propertyName)->contains('delivery_order_numbers')) {
            $index = (int)explode('.', $propertyName)[1];
            $raw = $this->destinations[$index]['delivery_order_numbers'];
            if (is_string($raw)) {
                $this->destinations[$index]['delivery_order_numbers'] = array_map('trim', explode(',', $raw));
            }
        }
    }

    public function submit()
    {
        $this->validate();

        DB::transaction(function () {
            $note = $this->deliveryNote ?? new DeliveryNote();

            $note->fill([
                'branch' => $this->branch,
                'ritasi' => $this->ritasi,
                'delivery_note_date' => $this->delivery_note_date,
                'departure_time' => $this->departure_time,
                'return_time' => $this->return_time,
                'vehicle_number' => $this->vehicle_number,
                'driver_name' => $this->driver_name,
                'approval_flow_id' => \App\Models\ApprovalFlow::where('slug', 'creator-hrd')->first()->id ?? 1,
                'status' => $this->is_draft ? 'draft' : 'submitted',
            ])->save();

            // Clear old destinations
            $note->destinations()->each(function ($dest) {
                $dest->deliveryOrders()->delete(); // clear old DOs
                $dest->delete();
            });

            foreach ($this->destinations as $dest) {
                // dd($dest);
                $destination = $note->destinations()->create([
                    'destination' => $dest['destination'],
                    'remarks' => $dest['remarks'],
                    'driver_cost' => $dest['driver_cost'],
                    'kenek_cost' => $dest['kenek_cost'],
                    'balikan_cost' => $dest['balikan_cost'],
                    'driver_cost_currency' => $dest['driver_cost_currency'],
                    'kenek_cost_currency' => $dest['kenek_cost_currency'],
                    'balikan_cost_currency' => $dest['balikan_cost_currency'],
                ]);

                foreach ($dest['delivery_order_numbers'] as $doNumber) {
                    $destination->deliveryOrders()->create([
                        'delivery_order_number' => $doNumber,
                    ]);
                }
            }

            $this->deliveryNote = null;
        });

        session()->flash(
            'success',
            $this->is_draft ? 'Draft saved successfully!' : 'Delivery Note saved successfully!'
        );

        return redirect()->route('delivery-notes.index');
    }

    public function render()
    {
        return view('livewire.delivery-note-form');
    }
}
