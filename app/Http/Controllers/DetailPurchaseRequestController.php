<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseRequest;
use Illuminate\Http\Request;
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

    public function update(Request $request){
        if($request->ajax()){
            $detail = DetailPurchaseRequest::find($request->pk);

            if($request->name == "item_name"){
                $detail->update([
                    'item_name' => $request->value,
                ]);
            } elseif ($request->name == "quantity") {
                $detail->update([
                    'quantity' => $request->value,
                ]);
            } elseif ($request->name == "purpose") {
                $detail->update([
                    'purpose' => $request->value,
                ]);
            } elseif ($request->name == "price") {
                $value = $request->value;
                // Remove all dots to handle multiple thousand separators
                $numericValue = str_replace('.', '', $value);

                // Extract numeric part using regular expression
                preg_match('/\d+(\.\d+)?/', $numericValue, $matches);

                // Get the first match which should be "9000000"
                $numericValue = $matches[0];

                // Convert to integer
                $numericValue = (int) str_replace('.', '', $numericValue);

                $detail->update([
                    'price' => $numericValue,
                ]);
            }
            return response()->json(['success' => 'Detail updated successfully!']);
        }
        // return redirect()->back()->with(['success' => 'Detail updated successfully!']);
    }
}
