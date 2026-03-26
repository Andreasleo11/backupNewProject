<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Models\DeliveryNote;

class DeliveryNoteController extends Controller
{
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('destinations');

        return view('delivery-note.show', compact('deliveryNote'));
    }
}
