<?php

namespace App\Http\Controllers;

use App\DataTables\DirectorPurchaseRequestDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\DetailPurchaseRequest;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Models\MonhtlyPR;
use Illuminate\Support\Facades\DB;
use App\Models\MasterDataPr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    public function index(DirectorPurchaseRequestDataTable $datatable, Request $request)
    {

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // if($startDate && $endDate){
        //     dd($startDate . '-' . $endDate);
        // }

        // Get user information
        $user = Auth::user();
        $userDepartmentName = $user->department->name;
        $isHRDHead = $userDepartmentName === "HRD" && $user->is_head === 1;

        // Determine conditions based on user department and role
        $purchaseRequestsQuery = PurchaseRequest::with('files', 'createdBy', 'createdBy.department');

        if ($isHRDHead) {
            // If the user is HRD Head, filter requests with specific conditions
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNull('autograph_3')
                ->where('status', 2);

            $purchaseRequestsQuery;
        } elseif ($userDepartmentName === "DIRECTOR") {
            // If the user is a director, filter requests with specific conditions
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNotNull('autograph_3')
                ->where('status', 3);
        } else {
            // Otherwise, filter requests based on user department
            $purchaseRequestsQuery->whereHas('createdBy.department', function ($query) use ($userDepartmentName) {
                $query->where('name', '=', $userDepartmentName);
            });
        }

        // Additional filtering based on startDate and endDate
        if ($startDate && $endDate) {
            $purchaseRequestsQuery->whereBetween('date_pr', [$startDate, $endDate]);
            $request->session()->put('start_date', $startDate);
            $request->session()->put('end_date', $endDate);
        } else {
            $request->session()->forget('start_date', $startDate);
            $request->session()->forget('end_date', $endDate);
        }

        $purchaseRequests = $purchaseRequestsQuery
            ->orderByRaw('CASE WHEN status != 5 THEN 0 ELSE 1 END')
            ->orderBy('updated_at', 'desc')
            ->orderBy('status', 'desc')
            ->paginate(10);

        // // Get department-wise purchase request counts for chart
        // $departments = PurchaseRequest::select('to_department', DB::raw('COUNT(*) as count'))
        //     ->groupBy('to_department')
        //     ->get();

        // // Prepare data for the chart
        // $labels = $departments->pluck('to_department');
        // $counts = $departments->pluck('count');

        // return view('purchaseRequest.index', compact('labels', 'counts', 'purchaseRequests'));
        return view('purchaseRequest.index', compact('purchaseRequests'));
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
        $userIdCreate = Auth::id();

        // Define common data
        $commonData = [
            'user_id_create' => $userIdCreate,
            'to_department' => $request->input('to_department'),
            'date_pr' => $request->input('date_of_pr'),
            'date_required' => $request->input('date_of_required'),
            'remark' => $request->input('remark'),
            'supplier' => $request->input('supplier'),
            'autograph_1' => strtoupper(Auth::user()->name) . '.png',
            'autograph_user_1' => Auth::user()->name,
        ];

        // Set status and additional autograph fields based on the user's specification
        if (Auth::user()->specification->name == 'PURCHASER') {
            $commonData['status'] = 6;
            $commonData['autograph_5'] = strtoupper(Auth::user()->name) . '.png';
            $commonData['autograph_user_5'] = Auth::user()->name;
        } else {
            $commonData['status'] = 1;
        }

        // Create the purchase request
        $purchaseRequest = PurchaseRequest::create($commonData);

        $prNo = substr($request->input('to_department'), 0, 4) . '-' . $purchaseRequest->id;
        $purchaseRequest->update(['pr_no' => $prNo]);

        // update revisi 26 februari
        $this->verifyAndInsertItems($request, $purchaseRequest->id);

        // update revisi 26 februari

        return redirect()->route('purchaserequest.home')->with('success', 'Purchase request created successfully');
    }

    private function verifyAndInsertItems($request, $id){
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $itemName = $itemData['item_name'];
                $quantity = $itemData['quantity'];
                $purpose = $itemData['purpose'];
                $price = (int) str_replace(['Rp. ', '.'], '', $itemData['price']);

                // Check if the item exists in MasterDataPr
                $existingItem = MasterDataPr::where('name', $itemName)->first();

                if (!$existingItem) {
                    // Case 1: Item not available in MasterDataPr
                    $newItem = MasterDataPr::create([
                        'name' => $itemName,
                        'price' => $price, // Store the initial price
                    ]);

                    // Create the DetailPurchaseRequest record
                    DetailPurchaseRequest::create([
                        'purchase_request_id' => $id,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'purpose' => $purpose,
                        'price' => $price,
                    ]);
                } else {
                    // Case 2: Item available in MasterDataPr

                    // ngecek harga yang sudah ada di latest price = null
                    if ($existingItem->latest_price === null){
                        // Check if the price is different
                        if ($existingItem->price != $price) {

                            if ($existingItem->latest_price === null) {
                                // Update the latest price if it's null
                                $existingItem->update(['latest_price' => $price]);

                                    // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'price' => $price,
                                ]);
                            } else {
                                // Move the latest price to the price column
                                $existingItem->update(['price' => $existingItem->latest_price]);

                                // Update the latest price
                                $existingItem->update(['latest_price' => $price]);

                                // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'price' => $price,
                                ]);
                            }
                        } else{
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'price' => $price,
                            ]);
                        }
                    }else{
                        // ngecek karena sudah ada latest price, maka acuan harga yang dilihat latest_price
                        if ($existingItem->latest_price != $price) {

                            if ($existingItem->latest_price === null) {
                                // Update the latest price if it's null
                                $existingItem->update(['latest_price' => $price]);

                                    // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'price' => $price,
                                ]);
                            } else {

                                // Move the latest price to the price column
                                $existingItem->update(['price' => $existingItem->latest_price]);

                                // Update the latest price
                                $existingItem->update(['latest_price' => $price]);

                                // Create the DetailPurchaseRequest record
                                DetailPurchaseRequest::create([
                                    'purchase_request_id' => $id,
                                    'item_name' => $itemName,
                                    'quantity' => $quantity,
                                    'purpose' => $purpose,
                                    'price' => $price,
                                ]);
                            }
                        } else {
                            DetailPurchaseRequest::create([
                                'purchase_request_id' => $id,
                                'item_name' => $itemName,
                                'quantity' => $quantity,
                                'purpose' => $purpose,
                                'price' => $price,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function detail($id)
    {
        $purchaseRequest = PurchaseRequest::with('itemDetail', 'itemDetail.master')->find($id);
        foreach ($purchaseRequest->itemDetail as $detail) {
            $priceBefore = MasterDataPr::where('name', $detail->item_name)->first()->price ?? 0;
        }
        // dd($priceBefore);
        $user =  Auth::user();
        $userCreatedBy = $purchaseRequest->createdBy;

        // dd($priceBefore);

           // Check if autograph_2 is filled
        if($purchaseRequest->status != 5){

            if ($purchaseRequest->autograph_5 !== null) {
                $purchaseRequest->status = 6;
            }

            if ($purchaseRequest->autograph_2 !== null) {
                $purchaseRequest->status = 2;
            }

            // Check if autograph_3 is also filled
            if ($purchaseRequest->autograph_3 !== null) {
                $purchaseRequest->status = 3;
            }

            // Check if autograph_4 is also filled
            if ($purchaseRequest->autograph_4 !== null) {
                $purchaseRequest->status = 4;
            }
        }

        // Save the updated status
        $purchaseRequest->save();


        $timestamp = strtotime($purchaseRequest->created_at);
        $formattedDate = date("Ymd", $timestamp);
        $doc_id = 'PR/' . $purchaseRequest->id . '/' .$formattedDate;

        $files = File::where('doc_id', $doc_id)->get();

        return view('purchaseRequest.detail', compact('purchaseRequest', 'user', 'userCreatedBy', 'files'));
    }

    public function saveImagePath(Request $request, $prId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $pr = PurchaseRequest::find($prId);

        if(Auth::user()->department->name === 'DIRECTOR'){
            $pr->update([
                "autograph_{$section}" => $imagePath,
                "autograph_user_{$section}" => $username,
                "approved_at" => now(),
            ]);
        } else {
            $pr->update([
                "autograph_{$section}" => $imagePath,
                "autograph_user_{$section}" => $username
            ]);
        }

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

    public function edit($id){
        $pr = PurchaseRequest::find($id);
        $details = DetailPurchaseRequest::where('purchase_request_id', $id)->get();
        return view('purchaseRequest.edit', compact('pr', 'details'));
    }

    public function update(Request $request, $id){
        $validated= $request->validate([
            'to_department' => 'string|max:255',
            'date_of_pr' => 'date',
            'date_required' => 'date',
            'remark' => 'string',
            'supplier' => 'string',
        ]);

        PurchaseRequest::find($id)->update($validated);

        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();

        $this->verifyAndInsertItems($request, $id);
        return redirect()->back()->with(['success' => 'Purchase request updated successfully!']);
    }

    public function destroy($id){
        $details = DetailPurchaseRequest::where('purchase_request_id', $id)->delete();
        PurchaseRequest::find($id)->delete();
        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();
        return redirect()->back()->with(['success' => 'Purchase request deleted succesfully!']);
    }


    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'string|max:255'
        ]);

        PurchaseRequest::find($id)->update([
            'status' => 5,
            'description' => $request->description
        ]);

        return redirect()->back()->with(['success' => 'Purchase Request rejected']);
    }

    public function updateEditable(Request $request)
    {
        if($request->ajax()){
            $pr = PurchaseRequest::find($request->pk);

            if($request->name == "supplier"){
                $pr->update(['supplier' => $request->value]);
            } else if($request->name == "date_required"){
                $pr->update(['date_required' => $request->value]);
            } else if($request->name == "remark"){
                $pr->update(['remark' => $request->value]);
            }
        }
    }

    public function updateEditModeSession(Request $request)
    {
        // Retrieve the isChecked value from the request data
        $isChecked = $request->input('isChecked');

        // Use the isChecked value in an if statement
        if ($isChecked == 1) {
            // Do something if isChecked is true
            Session::put('edit_mode', true);
            // $request->session()->put('edit_mode', true);
        } else {
            // Do something if isChecked is false
            $request->session()->forget('edit_mode');
        }

        return response()->json(['success' => true]);
    }

}
