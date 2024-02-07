<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest; 
use App\Models\DetailPurchaseRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\MonhtlyPR;

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
        // dd($userCreatedBy);

           // Check if autograph_2 is filled
        if ($purchaseRequests->autograph_2 !== null) {
            $purchaseRequests->status = 2;
        }

        // Check if autograph_3 is also filled
        if ($purchaseRequests->autograph_3 !== null) {
            $purchaseRequests->status = 3;
        }

        // Check if autograph_4 is also filled
        if ($purchaseRequests->autograph_4 !== null) {
            $purchaseRequests->status = 4;
        }

        // Save the updated status
        $purchaseRequests->save();

        return view('purchaseRequest.detail', compact('purchaseRequests', 'user', 'userCreatedBy'));
    }

    public function saveImagePath(Request $request, $prId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $pr = PurchaseRequest::find($prId);
            $pr->update([
                "autograph_{$section}" => $imagePath
            ]);
            $pr->update([
                "autograph_user_{$section}" => $username
            ]);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }


    public function monthlyview()
    {
        $purchaseRequests = PurchaseRequest::with('itemDetail')->get();
        
        return view('purchaseRequest.monthly', compact('purchaseRequests'));
        
    }


        public function monthlyviewmonth(Request $request)
    {
       
        // Get the month inputted by the user
        $selectedMonth = $request->input('month');
        

        // Extract year and month from the selected month input
        $year = date('Y', strtotime($selectedMonth));
        $month = date('m', strtotime($selectedMonth));


        // Save the year and month to the MonhtlyPR model
        MonhtlyPR::create([
            'month' => $month,
            'year' => $year,
            // Add other fields as needed
        ]);

        // Fetch purchase requests for the selected month
        $purchaseRequests = PurchaseRequest::with('itemDetail')
            ->whereYear('date_pr', $year)
            ->whereMonth('date_pr', $month)
            ->get();

        // Pass the filtered data to the view
        
        return view('purchaseRequest.monthly', compact('purchaseRequests'));
    }


    public function monthlyprlist()
    {
        $monthlist = MonhtlyPR::get();

        return view ('purchaseRequest.monthlylist', compact('monthlist'));
    }

    public function monthlydetail($id)
    {
        $monthdetail = MonhtlyPR::find($id);
        
         // Extract year and month from the selected month input
        // $year = date('Y', strtotime($monthdetail->year));
        // $month = date('m', strtotime($monthdetail->month));

        $year = $monthdetail->year;
        $month = $monthdetail->month;

        $purchaseRequests = PurchaseRequest::with('itemDetail')
        ->whereYear('date_pr', $year)
        ->whereMonth('date_pr', $month)
        ->get();

        // dd($monthdetail);
         return view('purchaseRequest.monthlydetail', compact('purchaseRequests', 'monthdetail'));
    }


    public function saveImagePathMonthly(Request $request, $monthprId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $monthpr = MonhtlyPR::find($monthprId);
            $monthpr->update([
                "autograph_{$section}" => $imagePath
            ]);
            $monthpr->update([
                "autograph_user_{$section}" => $username
            ]);

        return response()->json(['success' => 'Autograph saved successfully!']);
        
    }


}
