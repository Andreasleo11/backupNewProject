<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest; 
use App\Models\DetailPurchaseRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        return view('purchaseRequest.index');
    }

    public function create()
    {
        return view('purchaseRequest.create');
    }

   

    public function insert(Request $request)
    {
        $data = $request->all();
        // dd($data);

        $userIdCreate = Auth::id();



        $purchaseRequest = PurchaseRequest::create([
            'user_id_create' => $userIdCreate,
            'to_department' => $request->input('to_department'),
            'date_pr' => $request->input('date_of_pr'),
            'date_required' => $request->input('date_of_required'),
            'remark' => $request->input('remark'),
            'supplier' => $request->input('supplier'),
            'autograph_1' => Auth::user()->name . '.png',
            'autograph_user_1' => Auth::user()->name,
            'status' => 1,

        ]);

        $prNo = substr($request->input('to_department'), 0, 4) . '-' . $purchaseRequest->id;

        $purchaseRequest->update(['pr_no' => $prNo]);

       // Check if 'items' key exists in the request
    if ($request->has('items') && is_array($request->input('items'))) {
        // Create detail records for each item
        foreach ($request->input('items') as $itemData) {
            DetailPurchaseRequest::create([
                'purchase_request_id' => $purchaseRequest->id,
                'item_name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'purpose' => $itemData['purpose'],
                'unit_price' => $itemData['unit_price'],
            ]);
        }
    }

        return redirect()->route('purchaserequest.home')->with('success', 'Purchase request created successfully');
    }

    public function viewAll()
    {
        $purchaseRequests = PurchaseRequest::get();
        // dd($purchaseRequest);
        return view('purchaseRequest.viewAll', compact('purchaseRequests'));
    }


    public function detail($id)
    {
        $purchaseRequests = PurchaseRequest::with('itemDetail')->find($id);
        $user =  Auth::user();
        $userCreatedBy = $purchaseRequests->createdBy;  
        // dd($purchaseRequests);

        return view('purchaseRequest.detail', compact('purchaseRequests', 'user', 'userCreatedBy'));
    }


}
