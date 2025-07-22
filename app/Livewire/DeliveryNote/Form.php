<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Form extends Component
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
    public $customerNames = [];

    public $destinations = [
        [
            'destination' => '',
            'delivery_order_number' => '',
            'remarks' => '',
            'cost' => null,
            'cost_currency' => null,
        ]
    ];

    protected $rules = [
        'branch' => 'required|in:JAKARTA,KARAWANG',
        'ritasi' => 'required|integer|min:1|max:4',
        'delivery_note_date' => 'required|date',
        'departure_time' => 'required|date_format:H:i',
        'return_time' => 'required|date_format:H:i',
        'vehicle_number' => 'required|string',
        'driver_name' => 'required|string',
        'destinations' => 'required|array|min:1',
        'destinations.*.destination' => 'required|string',
        'destinations.*.delivery_order_number' => 'required|string',
        'destinations.*.remarks' => 'nullable|string',
        'destinations.*.cost' => 'nullable|numeric|min:0',
        'destinations.*.cost_currency' => 'nullable|string'
    ];

    public function mount(?DeliveryNote $deliveryNote)
    {
        if ($deliveryNote && $deliveryNote->exists) {
            $this->deliveryNote = $deliveryNote;
            $this->branch = $deliveryNote->branch;
            $this->ritasi = $deliveryNote->ritasi;
            $this->delivery_note_date = \Carbon\Carbon::parse($deliveryNote->delivery_note_date)->format('Y-m-d');
            $this->departure_time = \Carbon\Carbon::parse($deliveryNote->departure_time)->format('H:i');
            $this->return_time = \Carbon\Carbon::parse($deliveryNote->return_time)->format('H:i');
            $this->vehicle_number = $deliveryNote->vehicle_number;
            $this->driver_name = $deliveryNote->driver_name;
            $this->approval_flow_id = $deliveryNote->approval_flow_id;
            $this->destinations = $deliveryNote->destinations->map(function ($d) {
                return [
                    'destination' => $d->destination,
                    'delivery_order_number' => $d->delivery_order_number,
                    'remarks' => $d->remarks,
                    'cost' => $d->cost,
                    'cost_currency' => $d->cost_currency,
                ];
            })->toArray();
        }
        $this->is_draft = $deliveryNote?->status === 'draft';
        $this->customerNames = Destination::pluck('name')->toArray();
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

            $note->destinations()->delete();

            foreach ($this->destinations as $dest) {
                $note->destinations()->create($dest);
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
