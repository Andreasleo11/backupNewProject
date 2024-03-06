<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\DetailPurchaseRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\MonhtlyPR;
use Illuminate\Support\Facades\DB;
use App\Models\MasterDataPr;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $departments = PurchaseRequest::select('to_department', DB::raw('COUNT(*) as count'))
        ->groupBy('to_department')
        ->get();

        // Prepare data for the chart
        $labels = $departments->pluck('to_department');
        $counts = $departments->pluck('count');

        $purchaseRequests = PurchaseRequest::get();

        return view('purchaseRequest.index', compact('labels', 'counts', 'purchaseRequests'));
    }

    public function getChartData(Request $request, $year, $month)
    {
        $purchaseRequests = PurchaseRequest::select('to_department', DB::raw('COUNT(*) as count'))
            ->whereYear('date_pr', $year)
            ->whereMonth('date_pr', $month)
            ->groupBy('to_department')
            ->get();

        $labels = $purchaseRequests->pluck('to_department');
        $counts = $purchaseRequests->pluck('count');

        return response()->json(['labels' => $labels, 'counts' => $counts]);
    }

    public function create()
    {
        $master = MasterDataPr::get();
        // dd($master);
        return view('purchaseRequest.create', compact('master'));
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

    //    // Check if 'items' key exists in the request
    //     if ($request->has('items') && is_array($request->input('items'))) {
    //         // Create detail records for each item
    //         foreach ($request->input('items') as $itemData) {
    //             DetailPurchaseRequest::create([
    //                 'purchase_request_id' => $purchaseRequest->id,
    //                 'item_name' => $itemData['item_name'],
    //                 'quantity' => $itemData['quantity'],
    //                 'purpose' => $itemData['purpose'],
    //                 'unit_price' => $itemData['unit_price'],
    //             ]);
    //         }
    //     }

        // update revisi 26 februari 
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $itemName = $itemData['item_name'];
                $quantity = $itemData['quantity'];
                $purpose = $itemData['purpose'];
                $unitPrice = $itemData['unit_price'];
        
                // Check if the item exists in MasterDataPr
                $existingItem = MasterDataPr::where('name', $itemName)->first();
        
                if (!$existingItem) {
                    // Case 1: Item not available in MasterDataPr
                    $newItem = MasterDataPr::create([
                        'name' => $itemName,
                        'price' => $unitPrice, // Store the initial price
                    ]);
        
                    // Create the DetailPurchaseRequest record
                    DetailPurchaseRequest::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'purpose' => $purpose,
                        'unit_price' => $unitPrice,
                    ]);
                } else {
                    // Case 2: Item available in MasterDataPr
        
                    // ngecek harga yang sudah ada di latest price = null 
                    if ($existingItem->latest_price === null){
                        // Check if the price is different
                        if ($existingItem->price != $unitPrice) {

                            if ($existingItem->latest_price === null) {
                                // Update the latest price if it's null
                                $existingItem->update(['latest_price' => $unitPrice]);

                                    // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $purchaseRequest->id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'unit_price' => $unitPrice,
                                ]);
                            } else {

                                // Move the latest price to the price column
                                $existingItem->update(['price' => $existingItem->latest_price]);

                                // Update the latest price
                                $existingItem->update(['latest_price' => $unitPrice]);
                        
                                

                            // Create the DetailPurchaseRequest record
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $purchaseRequest->id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'unit_price' => $unitPrice,
                            ]);
                        }
                        }else{
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $purchaseRequest->id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'unit_price' => $unitPrice,
                            ]);
                        }
                    }else{
                        // ngecek karena sudah ada latest price, maka acuan harga yang dilihat latest_price
                        if ($existingItem->latest_price != $unitPrice) {

                            if ($existingItem->latest_price === null) {
                                // Update the latest price if it's null
                                $existingItem->update(['latest_price' => $unitPrice]);

                                    // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $purchaseRequest->id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'unit_price' => $unitPrice,
                                ]);
                            } else {

                                // Move the latest price to the price column
                                $existingItem->update(['price' => $existingItem->latest_price]);

                                // Update the latest price
                                $existingItem->update(['latest_price' => $unitPrice]);
                        
                                

                            // Create the DetailPurchaseRequest record
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $purchaseRequest->id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'unit_price' => $unitPrice,
                            ]);
                        }
                        }else{
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $purchaseRequest->id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'unit_price' => $unitPrice,
                            ]);
                        }
                    }
                }
            }
        }

        // update revisi 26 februari
        return redirect()->route('purchaserequest.home')->with('success', 'Purchase request created successfully');
    }

    public function viewAll()
    {
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



// REVISI PR DROPDOWN ITEM + PRICE
    public function getItemNames(Request $request)
    {
        $itemName = $request->query('itemName');
        info('AJAX request received for item name: ' . $itemName);

        // Fetch item names and prices from the database based on user input
        $items = MasterDataPr::where('name', 'like', "%$itemName%")
            ->select('name', 'price','latest_price')
            ->get();

        return response()->json($items);
    }
    

}
