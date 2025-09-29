<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;

class DeliveryNoteController extends Controller
{
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('destinations');

        return view('delivery-note.show', compact('deliveryNote'));
    }
}
