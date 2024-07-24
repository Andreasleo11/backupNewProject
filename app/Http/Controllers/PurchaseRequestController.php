<?php

namespace App\Http\Controllers;

use App\DataTables\DirectorPurchaseRequestDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\DetailPurchaseRequest;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Models\MonhtlyPR;
use Illuminate\Support\Facades\DB;
use App\Models\MasterDataPr;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        // Get user information
        $user = Auth::user();
        $userDepartmentName = $user->department->name;
        $isHRDHead = $userDepartmentName === "HRD" && $user->is_head === 1;
        $isHead = $user->is_head === 1;
        $isPurchaser = $user->specification->name === "PURCHASER";
        $isGM = $user->is_gm === 1;

        // Determine conditions based on user department and role
        $purchaseRequestsQuery = PurchaseRequest::with('files', 'createdBy');

        if ($isHRDHead) {
            // If the user is HRD Head, filter requests with specific conditions
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNotNull('autograph_5')
                ->where(function ($query) {
                    $query->whereNull('autograph_3')
                        ->orWhereNotNull('autograph_3')
                        ->where(function ($query) {
                            $query->where('to_department', 'Personnel')
                                ->where('type', 'office')
                                ->orWhere('to_department', 'Computer');
                        });
                })->orWhere('from_department', 'PERSONALIA');
        } elseif ($isGM) {
            $purchaseRequestsQuery->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNull('autograph_6')
                ->where(function ($query) use ($userDepartmentName) {
                    $query->where('type', 'factory');
                    // Additional condition for users where is_gm is 1 and department is 'MOULDING'
                    if ($userDepartmentName === 'MOULDING') {
                        $query->where('from_department', 'MOULDING');
                    } else {
                        $query->where('from_department', '!=', 'MOULDING');
                    }
                });
        } elseif ($isHead) {
            // same as else
            $purchaseRequestsQuery->where(function ($query) use ($userDepartmentName) {
                $query->where('from_department', $userDepartmentName);
            });

            if ($userDepartmentName === 'PURCHASING') {
                $purchaseRequestsQuery->orWhere('to_department', ucwords(strtolower($userDepartmentName)));
            } elseif ($userDepartmentName === 'LOGISTIC') {
                $purchaseRequestsQuery->orWhere('from_department', 'STORE');
            }
        } elseif ($isPurchaser) {
            // If the user is a purchaser, filter requests with specific conditions
            $purchaseRequestsQuery->where(function ($query) {
                $query->where('from_department', '!=', 'MOULDING')
                    ->where('type', 'factory')
                    ->whereNotNull('autograph_6')
                    ->orWhere(function ($query) {
                        $query->where('from_department', 'MOULDING')
                            ->orWhere('type', '!=', 'factory');
                    });
            });

            // $purchaseRequestsQuery->where(function ($query) use ($user) {
            //     $query->orWhere('user_id_create', $user->id); // Assuming 'created_by' is the foreign key for the user who created the request
            // });

            if ($userDepartmentName === 'COMPUTER' || $userDepartmentName === 'PURCHASING') {
                $purchaseRequestsQuery->where('to_department', ucwords(strtolower($userDepartmentName)));
            } elseif ($user->email === 'nur@daijo.co.id') {
                $purchaseRequestsQuery->where(function ($query) {
                    $query->where('to_department', 'Maintenance');
                });
            } elseif ($userDepartmentName === "PERSONALIA") {
                $purchaseRequestsQuery->where('to_department', 'Personnel');
            }

            $purchaseRequestsQuery->whereNotNull('autograph_1');
        } else {
            // Otherwise, filter requests based on user department
            $purchaseRequestsQuery->where('from_department', $userDepartmentName);
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
        if ($status) {
            $request->session()->put('status', $status);
            $purchaseRequestsQuery->where('status', $status);
        } else {
            $request->session()->forget('status', $status);
        }

        $purchaseRequests = $purchaseRequestsQuery
            ->orderBy('created_at', 'desc')
            ->orWhere('user_id_create', $user->id)
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
        $items = MasterDataPr::get();
        $departments = Department::all();
        return view('purchaseRequest.create', compact('items', 'departments'));
    }

    public function insert(StorePurchaseRequest $request)
    {
        $items = $request->input('items', []);

        // Process each item
        $processedItems = array_map(function ($item) {
            $item['price'] = $this->sanitizeCurrencyInput($item['price']);
            return $item;
        }, $items);

        $userIdCreate = Auth::id();
        // Define common data
        $commonData = [
            'user_id_create' => $userIdCreate,
            'from_department' => $request->input('from_department'),
            'to_department' => $request->input('to_department'),
            'date_pr' => $request->input('date_of_pr'),
            'date_required' => $request->input('date_of_required'),
            'remark' => $request->input('remark'),
            'supplier' => $request->input('supplier'),
            'pic' => $request->input('pic'),
            'type' => $request->input('type'),
            'autograph_1' => strtoupper(Auth::user()->name) . '.png',
            'autograph_user_1' => Auth::user()->name,
            'status' => 1,
            'branch' => $request->branch,
        ];

        if ($commonData['from_department'] === 'MOULDING' && $request->has('is_import')) {
            if ($request->is_import === 'true') {
                $commonData['is_import'] = true;
            } else {
                $commonData['is_import'] = false;
            }
        } elseif ($commonData['from_department'] === 'PERSONALIA') {
            $commonData['autograph_2'] = 'Bernadett.png';
            $commonData['autograph_user_2'] = 'Bernadett';
        }

        $officeDepartments = Department::where('is_office', true)->pluck('name')->toArray();
        if (in_array($request->from_department, $officeDepartments)) {
            $commonData['type'] = "office";
            if ($request->from_department === 'PE') {
                $commonData['type'] = "factory";
            }
        } else {
            $commonData['type'] = "factory";
        }

        // Create the purchase request
        $purchaseRequest = PurchaseRequest::create($commonData);

        $this->verifyAndInsertItems($processedItems, $purchaseRequest);
        // $this->executeSendPRNotificationCommand();

        return redirect()->route('purchaserequest.home')->with('success', 'Purchase request created successfully');
    }

    private function verifyAndInsertItems($items, $purchaseRequest)
    {
        if (isset($items) && is_array($items)) {
            foreach ($items as $itemData) {
                $itemName = $itemData['item_name'];
                $quantity = $itemData['quantity'];
                $purpose = $itemData['purpose'];
                $price = $this->sanitizeCurrencyInput($itemData['price']);
                $uom = strtoupper($itemData['uom']);
                $currency = $itemData['currency'];

                $commonData = [
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_name' => $itemName,
                    'quantity' => $quantity,
                    'purpose' => $purpose,
                    'price' => $price,
                    'uom' => $uom,
                    'currency' => $currency
                ];

                if ($purchaseRequest->from_department == 'PERSONALIA') {
                    $commonData['is_approve_by_head'] = 1;
                }

                DetailPurchaseRequest::create($commonData);
            }
        }
    }

    private function sanitizeCurrencyInput($input)
    {
        // Remove possible currency prefixes
        $input = preg_replace('/[Rp$¥]\.?\s*/', '', $input);

        // Remove commas
        $input = str_replace(',', '', $input);

        // Return the sanitized input
        return $input;
    }

    public function detail($id)
    {
        $departments = Department::all();
        $purchaseRequest = PurchaseRequest::with('itemDetail', 'itemDetail.master')->find($id);

        if (!$purchaseRequest) {
            // Handle the case where the purchase request is not found
            abort(404, 'Purchase request not found');
        }

        foreach ($purchaseRequest->itemDetail as $detail) {
            $priceBefore = MasterDataPr::where('name', $detail->item_name)->first()->price ?? 0;
        }

        $fromDepartment = Department::where('name', $purchaseRequest->from_department)->first();
        if (!$fromDepartment) {
            // Handle the case where the department is not found
            abort(404, 'Department not found');
        }
        $fromDeptNo = $fromDepartment->dept_no;
        $user = Auth::user();
        $userCreatedBy = $purchaseRequest->createdBy;

        // If PR not Rejected
        if ($purchaseRequest->status !== 5) {
            // After Dept Head Autograph
            if ($purchaseRequest->autograph_2 !== null) {
                if ($purchaseRequest->from_department === 'MOULDING' || $purchaseRequest->type === 'office') {
                    // If it's moulding then direct to purchaser
                    $purchaseRequest->status = 6;
                } elseif ($purchaseRequest->type === 'factory') {
                    // Status when GM has not signed
                    $purchaseRequest->status = 7;
                }
            }

            // After GM Autograph
            if ($purchaseRequest->autograph_6 !== null) {
                // Waiting for purchaser
                $purchaseRequest->status = 6;
            }

            // After Purchaser Autograph
            if ($purchaseRequest->autograph_5 !== null) {
                if (($purchaseRequest->to_department === 'Purchasing' && $purchaseRequest->type === 'factory') ||
                    $purchaseRequest->to_department === 'Maintenance'
                ) {
                    // Direct to Director
                    $purchaseRequest->status = 3;
                } elseif ($purchaseRequest->to_department === 'Computer' || $purchaseRequest->to_department === 'Personnel') {
                    // Status when verificator has not signed
                    $purchaseRequest->status = 2;
                }
            }

            // After Verificator Autograph
            if ($purchaseRequest->autograph_3 !== null) {
                // Status when director has not signed
                $purchaseRequest->status = 3;
            }

            // After Director Autograph
            if ($purchaseRequest->autograph_4 !== null) {
                // Status when PR approved
                $purchaseRequest->status = 4;
            }
        }

        // Save the updated status
        $purchaseRequest->save();

        $timestamp = strtotime($purchaseRequest->created_at);
        $formattedDate = date("Ymd", $timestamp);
        $doc_id = $purchaseRequest->doc_num;

        $files = File::where('doc_id', $doc_id)->get();

        // Filter itemDetail based on user role
        $filteredItemDetail = $purchaseRequest->itemDetail->filter(function ($detail) use ($user, $purchaseRequest) {
            $detail->quantity = $this->formatDecimal($detail->quantity);
            if ($user->department->name === "DIRECTOR") {
                if ($purchaseRequest->type === 'factory') {
                    if ($purchaseRequest->to_department === 'Computer') {
                        return $detail->is_approve_by_head && $detail->is_approve_by_gm && $detail->is_approve_by_verificator;
                    }
                    return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                } else {
                    return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
                }
            } elseif ($user->specification->name === "VERIFICATOR") {
                if ($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory') {
                    return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                }
                return $detail->is_approve_by_head;
            } else {
                return true; // Include all details for other roles
            }
        })->values(); // Ensure that the result is an array

        $this->updateMasterPRItems($filteredItemDetail);

        return view('purchaseRequest.detail', compact('purchaseRequest', 'user', 'userCreatedBy', 'files', 'filteredItemDetail', 'departments', 'fromDeptNo'));
    }

    private function updateMasterPRItems($items)
    {
        if (isset($items) && is_array($items)) {
            foreach ($items as $itemData) {
                $itemName = $itemData['item_name'];
                $price = $this->sanitizeCurrencyInput($itemData['price']);
                $currency = $itemData['currency'];

                // Check if the item exists in MasterDataPr
                $existingItem = MasterDataPr::where('name', $itemName)->first();

                if (!$existingItem) {
                    // Case 1: Item not available in MasterDataPr
                    MasterDataPr::create([
                        'name' => $itemName,
                        'currency' => $currency,
                        'price' => $price, // Store the initial price
                    ]);
                } else {
                    // Case 2: Item available in MasterDataPr
                    if ($existingItem->latest_price !== $price) {
                        $existingItem->update([
                            'price' => $existingItem->latest_price,
                            'latest_price' => $price,
                        ]);
                    }
                }
            }
        }
    }

    private function formatDecimal($value)
    {
        // Check if the number has no decimal part (i.e., is an integer)
        if (floor($value) == $value) {
            // If it's an integer, cast it to int to remove the decimal point
            return (int)$value;
        } else {
            // If it has a decimal part, return it as is
            return $value;
        }
    }

    public function saveImagePath(Request $request, $prId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $pr = PurchaseRequest::find($prId);

        if (Auth::user()->department->name === 'DIRECTOR') {
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

        return view('purchaseRequest.monthlylist', compact('monthlist'));
        return view('purchaseRequest.monthlylist', compact('monthlist'));
    }


    public function monthlydetail($id)
    {
        $monthdetail = MonhtlyPR::find($id);

        // Extract year and month from the selected month input
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
        $monthpr->update([
            "autograph_{$section}" => $imagePath
        ]);
        $monthpr->update([
            "autograph_user_{$section}" => $username
        ]);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }

    // REVISI PR DROPDOWN ITEM + PRICE
    // REVISI PR DROPDOWN ITEM + PRICE
    public function getItemNames(Request $request)
    {
        $itemName = $request->query('itemName');
        info('AJAX request received for item name: ' . $itemName);

        // Fetch item names and prices from the database based on user input
        $items = MasterDataPr::where('name', 'like', "%$itemName%")
            ->get(['id', 'name', 'currency', 'price', 'latest_price']);

        return response()->json($items);
    }

    public function update(UpdatePurchaseRequest $request, $id)
    {
        $validated = $request->validated();
        // Define the additional attribute and its value
        $additionalData = [
            'updated_at' => now(),
        ];

        if ($request->is_import === 'true') {
            $additionalData['is_import'] = true;
        } else {
            $additionalData['is_import'] = false;
        }

        $pr = PurchaseRequest::find($id);
        $isPurchaser = Auth::user()->specification->name === "PURCHASER";
        $isHead = Auth::user()->is_head === 1;

        // dept head update
        if ($pr->status === 1) {
            if ($isHead) {
                $additionalData['autograph_2'] = null;
                $additionalData['autograph_user_2'] = null;
            }
            $additionalData['status'] = 1;
            $dataToUpdate = array_merge($validated, $additionalData);

            $pr->update($dataToUpdate);
        } elseif ($pr->status === 6) {
            if ($isPurchaser) {
                $additionalData['autograph_5'] = null;
                $additionalData['autograph_user_5'] = null;
            } elseif ($isHead) {
                $additionalData['autograph_2'] = null;
                $additionalData['autograph_user_2'] = null;
            }
            $additionalData['status'] = 6;

            // Merge the validated data with the additional data
            $dataToUpdate = array_merge($validated, $additionalData);
            // dd($dataToUpdate);
            $pr->update($dataToUpdate);

            // verificator update
        } else if ($pr->status === 3) {
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

        $this->verifyAndInsertItems($request->items, $pr);

        $details = DetailPurchaseRequest::where('purchase_request_id', $id)->get();

        foreach ($details as $detail) {
            foreach ($oldDetails as $oldDetail) {
                // If the current detail name equal with the old detail name than it will replaced with the old one
                if ($detail->item_name === $oldDetail->item_name) {
                    $detail->update([
                        'is_approve_by_head' => $oldDetail->is_approve_by_head,
                        'is_approve_by_gm' => $oldDetail->is_approve_by_gm,
                        'is_approve_by_verificator' => $oldDetail->is_approve_by_verificator,
                    ]);
                } else {
                    $detail->update([
                        'is_approve_by_head' => auth()->user()->specification->name === "PURCHASER" ? 1 : $oldDetail->is_approve_by_head,
                    ]);

                    if ($pr->type === 'factory') {
                        $detail->update([
                            'is_approve_by_gm' => auth()->user()->specification->name === "PURCHASER" ? 1 : $oldDetail->is_approve_by_gm,
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with(['success' => 'Purchase request updated successfully!']);
    }

    public function destroy($id)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();
        PurchaseRequest::find($id)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
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

    public function approveAllDetailItems($prId, $type)
    {
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

    public function exportToPdf($id)
    {
        $user =  Auth::user();
        $purchaseRequest = PurchaseRequest::with('itemDetail', 'createdBy')->find($id);
        $userCreatedBy = $purchaseRequest->createdBy;

        // Filter itemDetail based on user role
        $filteredItemDetail = $purchaseRequest->itemDetail->filter(function ($detail) use ($user, $purchaseRequest) {
            $detail->quantity = $this->formatDecimal($detail->quantity);
            if ($user->department->name === "DIRECTOR") {
                if ($purchaseRequest->type === 'factory') {
                    if ($purchaseRequest->to_department === 'Computer') {
                        return $detail->is_approve_by_head && $detail->is_approve_by_gm && $detail->is_approve_by_verificator;
                    }
                    return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                } else {
                    return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
                }
            } elseif ($user->specification->name === "VERIFICATOR") {
                if ($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory') {
                    return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                }
                return $detail->is_approve_by_head;
            } else {
                return true; // Include all details for other roles
            }
        })->values(); // Ensure that the result is an array

        $pdf = Pdf::loadView('pdf/pr-pdf', compact('purchaseRequest', 'user', 'userCreatedBy', 'filteredItemDetail'))
            ->setPaper('a4', 'landscape');

        // return view('pdf.pr-pdf', compact('purchaseRequest', 'user', 'userCreatedBy', 'filteredItemDetail'));
        return $pdf->download('Purchase Request-' . $purchaseRequest->id . ' (' . $purchaseRequest->pr_no . ')' . '.pdf');
    }

    public function cancel(Request $request, $id)
    {
        PurchaseRequest::find($id)->update([
            'is_cancel' => true,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Purchase request canceled successfully!');
    }

    public function updatePoNumber(Request $request, $id)
    {
        PurchaseRequest::find($id)->update([
            'po_number' => $request->po_number,
        ]);

        return redirect()->back()->with('success', 'Purchase request PO Number updated successfully!');
    }
}
