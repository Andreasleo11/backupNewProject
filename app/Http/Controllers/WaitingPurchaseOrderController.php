<?php

namespace App\Http\Controllers;

use App\Models\WaitingPurchaseOrder;
use Illuminate\Http\Request;

class WaitingPurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = WaitingPurchaseOrder::all();

        return view('waiting_purchase_orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('waiting_purchase_orders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mold_name' => 'required|string|max:255',
            'capture_photo_path' => 'required|string',
            'process' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quotation_number' => 'required|string|max:255',
            'remark' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        WaitingPurchaseOrder::create($request->all());

        return redirect()
            ->route('waiting_purchase_orders.index')
            ->with('success', 'Purchase Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('waiting_purchase_orders.show', compact('waitingPurchaseOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('waiting_purchase_orders.edit', compact('waitingPurchaseOrder'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'mold_name' => 'required|string|max:255',
            'capture_photo_path' => 'required|string',
            'process' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quotation_number' => 'required|string|max:255',
            'remark' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $waitingPurchaseOrder = WaitingPurchaseOrder::find($id);

        $waitingPurchaseOrder->update($request->all());

        return redirect()
            ->route('waiting_purchase_orders.index')
            ->with('success', 'Purchase Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $waitingPurchaseOrder = WaitingPurchaseOrder::find($id);
        $waitingPurchaseOrder->delete();

        return redirect()
            ->route('waiting_purchase_orders.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }
}
