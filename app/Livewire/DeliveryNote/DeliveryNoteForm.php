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
        ]
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
        'destinations.*.balikan_cost_currency' => 'nullable|string'
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
            if (!auth()->check()) {
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
        $this->vehicleSuggestions = \App\Models\Vehicle::select('id', 'plate_number', 'driver_name')->get()->toArray();
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

        $note = DB::transaction(function () {
            $note = $this->deliveryNote ?? new DeliveryNote();

            $note->fill([
                'branch' => $this->branch,
                'ritasi' => $this->ritasi,
                'delivery_note_date' => $this->delivery_note_date,
                'departure_time' => $this->departure_time,
                'return_time' => $this->return_time,
                'vehicle_id' => $this->vehicle_id,
                'approval_flow_id' => \App\Models\ApprovalFlow::where('slug', 'creator-hrd')->first()->id ?? 1,
                'status' => $this->is_draft ? 'draft' : 'submitted',
            ])->save();

            // Clear old destinations
            $note->destinations()->each(function ($dest) {
                $dest->deliveryOrders()->delete(); // clear old DOs
                $dest->delete();
            });

            foreach ($this->destinations as $dest) {
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

                if (!empty($dest['delivery_order_numbers']) && is_array($dest['delivery_order_numbers'])) {
                    foreach ($dest['delivery_order_numbers'] as $doNumber) {
                        $destination->deliveryOrders()->create([
                            'delivery_order_number' => $doNumber,
                        ]);
                    }
                }
            }

            $this->deliveryNote = null;

            return $note;
        });

        session()->flash(
            'success',
            $this->is_draft ? 'Draft saved successfully!' : 'Delivery Note saved successfully!'
        );

        return redirect()->route('delivery-notes.show', $note->id);
    }

    public function render()
    {
        if (!auth()->check()) {
            return view('livewire.delivery-note.form')
                ->layout('layouts.guest');
        }
        return view('livewire.delivery-note.form');
    }
}
