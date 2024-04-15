<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseRequest;
use Illuminate\Http\Request;

class DetailPurchaseRequestController extends Controller
{
    public function approve($id){
        DetailPurchaseRequest::find($id)->update(['is_approve' => true]);
        return redirect()->back()->with(['success' => 'Detail approved successfully!']);
    }

    public function reject($id){
        DetailPurchaseRequest::find($id)->update(['is_approve' => false]);
        return redirect()->back()->with(['success' => 'Detail rejected successfully!']);
    }
}
