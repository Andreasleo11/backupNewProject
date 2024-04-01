<?php

namespace App\Http\Controllers;

use App\DataTables\DirectorPurchaseRequestDataTable;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectorPurchaseRequestController extends Controller
{
    public function index(DirectorPurchaseRequestDataTable $datatable)
    {
        return $datatable->render('director.purchaseRequest.index');
    }

    public function approveSelected(Request $request){
        $ids = $request->input('ids', []);
        $username = Auth::user()->name;
        $imageUrl = $username . '.png';

        if(empty($ids)) {
            return response()->json(['message' => 'No records selected for approval. (server)']);
        } else {
            try {
                foreach ($ids as $id) {
                    PurchaseRequest::find($id)->update([
                        'autograph_4' => $imageUrl,
                        'autograph_user_4' => $username,
                        'status' => 4
                    ]);
                }
                return response()->json(['message'=>'selected records approved successfully. (server)']);
            } catch (\Throwable $th) {
                return response()->json(['message'=>'failed to approve selected records. (server)']);
                throw $th;
            }
        }
    }

    public function rejectSelected(Request $request){
        $ids = $request->input('ids', []);
        $rejectionReason = $request->input('rejection_reason');

        if(empty($ids)) {
            return response()->json(['message' => 'No records selected for rejection. (server)']);
        }

        try {
            foreach ($ids as $id) {
                PurchaseRequest::find($id)->update([
                    'status' => 5,
                    'description' => $rejectionReason
                ]);
            }
            return response()->json(['message'=>'selected records rejected successfully. (server)']);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'failed to reject selected records. (server)']);
            throw $th;
        }
    }

    // public function updateAll()
    // {
    //     $prs = PurchaseRequest::where('status', -1)->get();
    //     foreach ($prs as $pr) {
    //         $pr->update(['status' => 5]);
    //     }
    // }
}
