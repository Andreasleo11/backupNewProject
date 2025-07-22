<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('destinations');
        return view('delivery-note.show', compact('deliveryNote'));
    }
}
