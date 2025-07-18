<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function index()
    {
        return view('delivery-note.index');
    }

    public function create()
    {
        return view('delivery-note.form');
    }

    public function edit(DeliveryNote $deliveryNote)
    {
        return view('delivery-note.form', compact('deliveryNote'));
    }

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('destinations');
        return view('delivery-note.show', compact('deliveryNote'));
    }

    public function destroy(DeliveryNote $deliveryNote)
    {
        $deliveryNote->delete();
        return redirect()->route('delivery-notes.index')->with('success', 'Delivery Note deleted successfully.');
    }
}
