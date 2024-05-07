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
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class PurchaseRequestController extends Controller
{
    public function index(DirectorPurchaseRequestDataTable $datatable, Request $request)
    {
        // Get user information
        $user = Auth::user();
        $userDepartmentName = $user->department->name;
        $isHRDHead = $userDepartmentName === "HRD" && $user->is_head === 1;
        $isPurchaser = $user->specification->name === "PURCHASER";
        $isGM = $user->is_gm === 1;


        // Determine conditions based on user department and role
        $purchaseRequestsQuery = PurchaseRequest::with('files', 'createdBy', 'createdBy.department');

        if ($isHRDHead) {
            // If the user is HRD Head, filter requests with specific conditions
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNotNull('autograph_5')
                ->whereNull('autograph_3')
                ->orWhereNotNull('autograph_3')
                ->where(function($query) {
                    $query->where(function($query) {
                            $query->where('to_department', 'Personnel')
                                  ->where('type', 'office');
                        })
                        ->orWhere(function($query) {
                            $query->where('to_department', 'Computer');
                        });
                });
        } elseif ($isGM) {
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNotNull('autograph_5')
                ->where(function ($query) {
                    $query->where('type', 'factory');
                    // Additional condition for users where is_gm is 1 and department is 'MOULDING'
                    if (auth()->user()->is_gm && auth()->user()->department->name === 'MOULDING') {
                        $query->orWhere(function ($query) {
                            $query->where('department', 'MOULDING');
                        });
                    }
                });
        } elseif ($isPurchaser) {
            // If the user is a purchaser, filter requests with specific conditions

            if($userDepartmentName === 'COMPUTER' || $userDepartmentName === 'PURCHASING'){
                $purchaseRequestsQuery->where('to_department', ucwords(strtolower($userDepartmentName)));
            } elseif ($user->email === 'nur@daijo.co.id'){
                $purchaseRequestsQuery->where('to_department', 'Maintenance');
            } elseif($userDepartmentName === 'PERSONALIA'){
                $purchaseRequestsQuery->where('to_department', 'Personnel');
            }

            $purchaseRequestsQuery->whereNotNull('autograph_1');

        } else {
            // Otherwise, filter requests based on user department
            $purchaseRequestsQuery->whereHas('createdBy.department', function ($query) use ($userDepartmentName) {
                $query->where('name', '=', $userDepartmentName);
            });
        }

        // Custom Filter
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $request->status;

        // Retrieve the stored session values for filter persistence
        $storedStartDate = $request->session()->get('start_date');
        $storedEndDate = $request->session()->get('end_date');
        $storedStatus = $request->session()->get('status');

        // Additional filtering based on startDate and endDate
        if ($startDate && $endDate) {
            $purchaseRequestsQuery->whereBetween('date_pr', [$startDate, $endDate]);
            $request->session()->put('start_date', $startDate);
            $request->session()->put('end_date', $endDate);
        } else {
            $request->session()->forget('start_date', $startDate);
            $request->session()->forget('end_date', $endDate);
        }

        // Apply stored session values for filter persistence
        if (!$startDate && !$endDate && $storedStartDate && $storedEndDate) {
            $startDate = $storedStartDate;
            $endDate = $storedEndDate;
        }

        if ($status != 0 && $storedStatus) {
            $status = $storedStatus;
        }

        // Filtering based on the status
        if($status){
            $request->session()->put('status', $status);
            switch ($status) {
                // Waiting for GM
                case 2:
                    $purchaseRequestsQuery->where('type', 'factory')->where('status', 2);
                    break;
                // Waiting for Verificator
                case 3:
                    $purchaseRequestsQuery->where(function ($query) {
                        $query->where('status', 2)->where('type', 'office')
                        ->orWhere('status', 3)->where('to_department', 'Computer')->where('type', 'factory');
                    });
                    break;
                // Waiting for Director
                case 7:
                    $purchaseRequestsQuery->where(function ($query) {
                        $query->where('status', 3)->whereNot(function($query){
                            $query->where('to_department', 'Computer')->where('type', 'factory');
                        });
                    });
                    break;

                default:
                    $purchaseRequestsQuery->where('status', $status);
                    break;
            }
        } else {
            $request->session()->forget('status', $status);
        }

        $purchaseRequests = $purchaseRequestsQuery
            ->orderBy('updated_at', 'desc')
            // ->orderByRaw("
            //             CASE
            //                 WHEN status = 1 THEN 0
            //                 WHEN status = 6 THEN 1
            //                 WHEN status = 2 THEN 2
            //                 WHEN status = 3 THEN 3
            //                 WHEN status = 4 THEN 4
            //                 ELSE 5
            //             END")
            ->paginate(10);

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
            'pic' => $request->input('pic'),
            'type' => $request->input('type'),
            'autograph_1' => strtoupper(Auth::user()->name) . '.png',
            'autograph_user_1' => Auth::user()->name,
            'status' => 1
        ];
        // dd($commonData);

        // Create the purchase request
        $purchaseRequest = PurchaseRequest::create($commonData);

        $prNo = substr($request->input('to_department'), 0, 4) . '-' . $purchaseRequest->id;
        $purchaseRequest->update(['pr_no' => $prNo]);

        // update revisi 26 februari
        $this->verifyAndInsertItems($request, $purchaseRequest->id);
        // $this->executeSendPRNotificationCommand();

        // update revisi 26 februari

        return redirect()->route('purchaserequest.home')->with('success', 'Purchase request created successfully');
    }

    private function executeSendPRNotificationCommand()
    {
        // Execute the email:send-pr-notification command
        Artisan::call('email:send-pr-notification');

        // Get the output of the command (optional)
        $output = Artisan::output();

        // You can handle the output or return a response as needed
        return response()->json(['message' => 'Command executed successfully', 'output' => $output]);
    }

    private function verifyAndInsertItems($request, $id){
        // $this->executeSendPRNotificationCommand();
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
        $user =  Auth::user();
        $userCreatedBy = $purchaseRequest->createdBy;

        $computerFactory = $purchaseRequest->type === 'factory' && $purchaseRequest->to_department === 'Computer';

        // If PR not Rejected
        if($purchaseRequest->status !== 5){
            // Dept Head Autograph
            if ($purchaseRequest->autograph_2 !== null) {
                // status when purchaser has not signed
                $purchaseRequest->status = 6;
            }

            // Purchaser Autograph
            if ($purchaseRequest->autograph_5 !== null) {
                if($purchaseRequest->type === 'factory' || $computerFactory){
                    // status when gm has not signed
                    $purchaseRequest->status = 7;
                } else {
                    // status when verificator has not signed
                    $purchaseRequest->status = 2;
                }
            }

            // GM Autograph
            if($purchaseRequest->type === 'factory' ){
                if ($purchaseRequest->autograph_6 !== null) {
                    // status when director has not signed
                    $purchaseRequest->status = 3;
                    if($computerFactory){
                        // status when verificator has not signed
                        $purchaseRequest->status = 2;
                    }
                }
            }

            // Verificator Autograph
            if($purchaseRequest->type === 'office' || $computerFactory){
                if ($purchaseRequest->autograph_3 !== null) {
                    // status when director has not signed
                    $purchaseRequest->status = 3;
                }
            }

            // Director Autograph
            if ($purchaseRequest->autograph_4 !== null) {
                // status when PR approved
                $purchaseRequest->status = 4;
            }
        }

        // Save the updated status
        $purchaseRequest->save();

        $timestamp = strtotime($purchaseRequest->created_at);
        $formattedDate = date("Ymd", $timestamp);
        $doc_id = 'PR/' . $purchaseRequest->id . '/' .$formattedDate;

        $files = File::where('doc_id', $doc_id)->get();

        // Filter itemDetail based on user role
        $filteredItemDetail = $purchaseRequest->itemDetail->filter(function ($detail) use ($user, $purchaseRequest) {
            if ($user->department->name === "DIRECTOR") {
                if($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory'){
                    return $detail->is_approve || ($detail->is_approve_by_verificator && $detail->is_approve_by_gm && $detail->is_approve_by_head);
                }
                return $detail->is_approve || ($detail->is_approve_by_verificator || $detail->is_approve_by_gm && $detail->is_approve_by_head);
            } elseif ($user->specification->name === "VERIFICATOR") {
                if($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory'){
                    return $detail->is_approve_by_head && $detail->is_approve_by_gm || $detail->is_approve_by_verificator;
                }
                return $detail->is_approve_by_head || $detail->is_approve_by_verificator;
            } else {
                return true; // Include all details for other roles
            }
        })->values(); // Ensure that the result is an array;

        return view('purchaseRequest.detail', compact('purchaseRequest', 'user', 'userCreatedBy', 'files', 'filteredItemDetail'));
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

    public function update(Request $request, $id){
        // dd($request->all());
        $validated = $request->validate([
            'to_department' => 'string|max:255',
            'date_pr' => 'date',
            'date_required' => 'date',
            'remark' => 'string',
            'supplier' => 'string',
            'pic' => 'string'
        ]);

        // Define the additional attribute and its value
        $additionalData = [
            'updated_at' => now(),
            'pic' => $request->pic,
        ];

        $pr = PurchaseRequest::find($id);
        $isPurchaser = Auth::user()->specification === "PURCHASER";
        $isHead = Auth::user()->is_head === 1;

        // dept head update
        if($pr->status === 1) {
            if($isHead){
                $additionalData['autograph_2'] = null;
                $additionalData['autograph_user_2'] = null;
            }
            $additionalData['status'] = 1;
            $dataToUpdate = array_merge($validated, $additionalData);

            $pr->update($dataToUpdate);
        } elseif($pr->status === 6) {
            if($isHead){
                $additionalData['autograph_2'] = null;
                $additionalData['autograph_user_2'] = null;
            } elseif($isPurchaser) {
                $additionalData['autograph_6'] = null;
                $additionalData['autograph_user_6'] = null;
            }
            $additionalData['status'] = 6;

            // Merge the validated data with the additional data
            $dataToUpdate = array_merge($validated, $additionalData);
            // dd($dataToUpdate);
            $pr->update($dataToUpdate);

        // verificator update
        } else if($pr->status === 3){
            $additionalData['autograph_3'] = null;
            $additionalData['autograph_user_3'] = null;
            $additionalData['status'] = 3;

            // Merge the validated data with the additional data
            $dataToUpdate = array_merge($validated, $additionalData);

            // dd($dataToUpdate);

            $pr->update($dataToUpdate);
        } else {
            $pr->update($additionalData);
        }

        // Delete and Store it again
        $oldDetails = DetailPurchaseRequest::where('purchase_request_id', $id)->get();
        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();

        $this->verifyAndInsertItems($request, $id);

        $details = DetailPurchaseRequest::where('purchase_request_id', $id)->get();

        foreach ($details as $detail) {
            foreach ($oldDetails as $oldDetail) {
                // If the current detail name equal with the old detail name than it will replaced with the old one
                if($detail->item_name === $oldDetail->item_name){
                    $detail->update([
                        'is_approve_by_head' => $oldDetail->is_approve_by_head,
                        'is_approve_by_gm' => $oldDetail->is_approve_by_gm,
                        'is_approve_by_verificator' => $oldDetail->is_approve_by_verificator,
                    ]);
                } else {
                    $detail->update([
                        'is_approve_by_head' => auth()->user()->specification->name === "PURCHASER" ? 1 : 0,
                    ]);
                }
            }
        }

        return redirect()->back()->with(['success' => 'Purchase request updated successfully!']);
    }

    public function destroy($id){
        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();
        PurchaseRequest::find($id)->delete();
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

    public function approveAllDetailItems($prId, $type){
        if ($type === 'GM') {
            $details = DetailPurchaseRequest::where('purchase_request_id', $prId)
                        ->where('is_approve_by_head', true)->get();

            foreach ($details as $detail) {
                $detail->update(['is_approve_by_gm' => true]);
            }
            return response()->json(['success' => 'All detail approved successfully!']);
        }
        return response()->json(['error' => 'Something went wrong!']);
    }
}
