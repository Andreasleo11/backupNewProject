<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailPurchaseRequestController extends Controller
{
    public function approve($id, Request $request){
        $type = $request->type;

        if($type == 'head'){
            DetailPurchaseRequest::find($id)->update(['is_approve_by_head' => true]);
        } else if($type == 'verificator'){
            DetailPurchaseRequest::find($id)->update(['is_approve_by_verificator' => true]);
        } else if($type == 'director'){
            DetailPurchaseRequest::find($id)->update(['is_approve' => true]);
        }

        return redirect()->back()->with(['success' => 'Detail approved successfully!']);
    }

    public function reject($id, Request $request){
        $type = $request->type;

        if($type == 'head'){
            DetailPurchaseRequest::find($id)->update(['is_approve_by_head' => false]);
        } else if($type == 'verificator'){
            DetailPurchaseRequest::find($id)->update(['is_approve_by_verificator' => false]);
        } else if($type == 'director'){
            DetailPurchaseRequest::find($id)->update(['is_approve' => false]);
        }

        return redirect()->back()->with(['success' => 'Detail rejected successfully!']);
    }
}
