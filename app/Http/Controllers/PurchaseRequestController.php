<?php

namespace App\Http\Controllers;

use App\Application\Approval\Contracts\Approvals;
use App\DataTables\PurchaseRequestsDataTable;
use App\Enums\ToDepartment;
use App\Exports\PurchaseRequestWithDetailsExport;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Department;
use App\Models\DetailPurchaseRequest;
use App\Models\File;
use App\Models\MasterDataPr;
use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseRequestController extends Controller
{
    public function __construct(
        private Approvals $approvals,
    ) {}

    public function index(Request $request, PurchaseRequestsDataTable $dataTable)
    {
        // Get user information
        $user = Auth::user();
        $userDepartmentName = $user->department->name;
        $isPersonaliaHead = $userDepartmentName === 'PERSONALIA' && $user->is_head === 1;
        $isHead = $user->is_head === 1;
        $isPurchaser = $user->specification->name === 'PURCHASER';
        $isGM = $user->is_gm === 1;

        // Determine conditions based on user department and role
        $purchaseRequestsQuery = PurchaseRequest::with('files', 'createdBy');

        if ($isPersonaliaHead) {
            $purchaseRequestsQuery->where(function ($query) {
                $query
                    ->whereNotNull('autograph_1')
                    ->whereNotNull('autograph_2')
                    ->whereNotNull('autograph_5')
                    ->where(function ($query) {
                        $query
                            ->whereNull('autograph_3')
                            ->orWhereNotNull('autograph_3')
                            ->where(function ($query) {
                                $query
                                    ->where('to_department', ToDepartment::PERSONALIA->value)
                                    ->where('type', 'office')
                                    ->orWhere('to_department', ToDepartment::COMPUTER->value);
                            });
                    })
                    ->orWhere('from_department', 'PERSONALIA');
            });
        } elseif ($isGM) {
            $purchaseRequestsQuery
                ->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNull('autograph_6')
                ->where(function ($query) use ($userDepartmentName) {
                    $query->where('type', 'factory');
                    if ($userDepartmentName === 'MOULDING') {
                        $query->where('from_department', 'MOULDING');
                    } else {
                        $query->where('from_department', '!=', 'MOULDING');
                    }
                });
        } elseif ($isHead) {
            $purchaseRequestsQuery->where(function ($query) use ($userDepartmentName) {
                $query->where('from_department', $userDepartmentName);
            });

            if ($userDepartmentName === 'PURCHASING') {
                $purchaseRequestsQuery->orWhere(
                    'to_department',
                    ToDepartment::PURCHASING->value,
                );
            } elseif ($userDepartmentName === 'LOGISTIC') {
                $purchaseRequestsQuery->orWhere('from_department', 'STORE');
            }
        } elseif ($isPurchaser) {
            $purchaseRequestsQuery->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('type', 'office')->orWhere('from_department', 'MOULDING');
                        });
                    })
                    ->orWhere(function ($query) {
                        $query->where('type', 'factory');
                    });
            });

            if ($userDepartmentName === 'COMPUTER' || $userDepartmentName === 'PURCHASING') {
                $purchaseRequestsQuery->where(
                    'to_department',
                    ToDepartment::COMPUTER->value,
                );
            } elseif ($user->email === 'nur@daijo.co.id') {
                $purchaseRequestsQuery->where('to_department', ToDepartment::MAINTENANCE->value);
            } elseif ($userDepartmentName === 'PERSONALIA') {
                $purchaseRequestsQuery->where('to_department', ToDepartment::PERSONALIA->value);
            }

            $purchaseRequestsQuery->whereNotNull('autograph_1');
        } elseif ($user->role->name === 'SUPERADMIN') {
            $purchaseRequestsQuery->whereNotNull('autograph_1');
        } else {
            $purchaseRequestsQuery->where('from_department', $userDepartmentName);
        }

        // Check if reset is requested
        if ($request->has('reset')) {
            // Clear session filters
            $request->session()->forget('start_date');
            $request->session()->forget('end_date');
            $request->session()->forget('status');
            $request->session()->forget('branch');

            // Redirect without any filters
            return redirect()->route('purchase-requests.index');
        }

        // Apply filters from request or session
        $startDate = $request->start_date ?: $request->session()->get('start_date');
        $endDate = $request->end_date ?: $request->session()->get('end_date');
        $status = $request->status ?: $request->session()->get('status');
        $branch = $request->branch ?: $request->session()->get('branch');

        // Filter query
        if ($startDate && $endDate) {
            $purchaseRequestsQuery->whereBetween('date_pr', [$startDate, $endDate]);
            $request->session()->put('start_date', $startDate);
            $request->session()->put('end_date', $endDate);
        }

        if ($status) {
            $purchaseRequestsQuery->where('status', $status);
            $request->session()->put('status', $status);
        }

        if ($branch) {
            $purchaseRequestsQuery->where('branch', $branch);
            $request->session()->put('branch', $branch);
        }

        // Fetch purchase requests with pagination
        $purchaseRequests = $purchaseRequestsQuery
            ->orderBy('created_at', 'desc')
            // ->where(function($query) use ($user) {
            //     $query->orWhere('user_id_create', $user->id);
            // })
            ->paginate(10);

        // Append the filter parameters to the pagination links
        $purchaseRequests->appends([
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'branch' => $branch,
        ]);

        // return view('purchase-requests.index', compact('purchaseRequests'));
        return $dataTable->render('purchase-requests.index');
    }

    public function create()
    {
        $items = MasterDataPr::get();
        $departments = Department::all();

        return view('purchase-requests.create', compact('items', 'departments'));
    }

    public function store(StorePurchaseRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $items = $request->input('items', []);
            $isDraft = (bool) $request->is_draft;
            $user = Auth::user();

            // ===== 1. Sanitize items =====
            $processedItems = array_map(function ($item) {
                $item['price'] = $this->sanitizeCurrencyInput($item['price']);

                return $item;
            }, $items);

            $userIdCreate = $user->id;

            // ===== 2. Common header data (legacy logic) =====
            $commonData = [
                'user_id_create' => $userIdCreate,
                'from_department' => $request->input('from_department'),
                'to_department' => $request->input('to_department'),
                'date_pr' => $request->input('date_of_pr'),
                'date_required' => $request->input('date_of_required'),
                'remark' => $request->input('remark'),
                'supplier' => $request->input('supplier'),
                'pic' => $request->input('pic'),
                'type' => $request->input('type'), // nanti di-override di bawah
                'autograph_1' => strtoupper($user->name).'.png',
                'autograph_user_1' => $user->name,
                'status' => 1,         // WAITING FOR DEPT HEAD
                'branch' => $request->branch,
            ];

            // plastic injection / maintenance machine karawang → langsung WAITING GM
            if (
                $commonData['from_department'] === 'PLASTIC INJECTION' ||
                ($commonData['from_department'] === 'MAINTENANCE MACHINE'
                    && $commonData['branch'] === 'KARAWANG')
            ) {
                $commonData['status'] = 7;
            }

            // Draft: tidak ada autograph_1 + status draft
            if ($isDraft) {
                $commonData['autograph_1'] = null;
                $commonData['autograph_user_1'] = null;
                $commonData['status'] = 8; // DRAFT
            }

            // Moulding import flag
            if ($commonData['from_department'] === 'MOULDING' && $request->has('is_import')) {
                $commonData['is_import'] = $request->is_import === 'true';
            }
            // Personalia: auto Bernadett + status waiting purchaser (legacy)
            elseif ($commonData['from_department'] === 'PERSONALIA') {
                $commonData['autograph_2'] = 'Bernadett.png';
                $commonData['autograph_user_2'] = 'Bernadett';
                $commonData['status'] = 6;
            }

            // ===== 3. Office / factory type (legacy logic) =====
            $officeDepartments = Department::where('is_office', true)->pluck('name')->toArray();

            if (in_array($commonData['from_department'], $officeDepartments, true)) {
                $commonData['type'] = 'office';
                if ($commonData['from_department'] === 'PE') {
                    $commonData['type'] = 'factory';
                }
            } else {
                $commonData['type'] = 'factory';
            }

            // ===== 4. Create PurchaseRequest (header) =====
            /** @var PurchaseRequest $purchaseRequest */
            $purchaseRequest = PurchaseRequest::create($commonData);

            // ===== 5. Insert detail items (legacy logic) =====
            $this->verifyAndInsertItems($processedItems, $purchaseRequest);

            // ===== 6. Submit ke ApprovalEngine (BARU) =====
            // Hanya kalau BUKAN draft dan belum punya approvalRequest
            if (! $isDraft && ! $purchaseRequest->approvalRequest) {

                // Pastikan relasi untuk context sudah tersedia
                $purchaseRequest->loadMissing(['items', 'fromDepartment']);

                // Build context buat RuleResolver
                $ctx = $purchaseRequest->buildApprovalContext();

                // Submit ke engine: akan buat approval_requests + approval_steps
                $this->approvals->submit($purchaseRequest, $user->id, $ctx);
            }

            // ===== 7. Redirect seperti biasa =====
            return redirect()
                ->route('purchase-requests.index')
                ->with('success', 'Purchase request created successfully');
        });
    }

    private function verifyAndInsertItems($items, PurchaseRequest $purchaseRequest)
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
                    'currency' => $currency,
                ];

                if (
                    $purchaseRequest->from_department === 'PERSONALIA' ||
                    $purchaseRequest->from_department === 'PLASTIC INJECTION' ||
                    $purchaseRequest->from_department === 'MAINTENANCE MACHINE'
                ) {
                    $commonData['is_approve_by_head'] = 1;
                }

                DetailPurchaseRequest::create($commonData);
            }
        }
    }

    private function sanitizeCurrencyInput($input)
    {
        $input = preg_replace('/[Rp$¥]\.?\s*/', '', $input);
        $input = str_replace(',', '', $input);

        return $input;
    }

    public function show($id)
    {
        $departments = Department::all();
        $purchaseRequest = PurchaseRequest::with('itemDetail', 'itemDetail.master', 'approvalRequest.steps')->find($id);
        $approval = $purchaseRequest->approvalRequest;

        if (! $purchaseRequest) {
            // Handle the case where the purchase request is not found
            abort(404, 'Purchase request not found');
        }

        foreach ($purchaseRequest->itemDetail as $detail) {
            $priceBefore = MasterDataPr::where('name', $detail->item_name)->first()->price ?? 0;
        }

        $fromDepartment = Department::where('name', $purchaseRequest->from_department)->first();
        if (! $fromDepartment) {
            // Handle the case where the department is not found
            abort(404, 'Department not found');
        }
        $fromDeptNo = $fromDepartment->dept_no;
        $user = Auth::user();
        $userCreatedBy = $purchaseRequest->createdBy;

        // $this->updateStatus($purchaseRequest);

        $timestamp = strtotime($purchaseRequest->created_at);
        $formattedDate = date('Ymd', $timestamp);
        $doc_id = $purchaseRequest->doc_num;

        $files = File::where('doc_id', $doc_id)->get();

        // Filter itemDetail based on user role
        $filteredItemDetail = $purchaseRequest->itemDetail
            ->filter(function ($detail) use ($user, $purchaseRequest) {
                $detail->quantity = $this->formatDecimal($detail->quantity);
                if ($user->specification->name === 'DIRECTOR') {
                    if ($purchaseRequest->type === 'factory') {
                        if ($purchaseRequest->to_department->value === ToDepartment::COMPUTER->value) {
                            return $detail->is_approve_by_head &&
                                $detail->is_approve_by_gm &&
                                $detail->is_approve_by_verificator;
                        }

                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    } else {
                        return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
                    }
                } elseif ($user->specification->name === 'VERIFICATOR') {
                    if (
                        $purchaseRequest->to_department->value === ToDepartment::COMPUTER->value &&
                        $purchaseRequest->type === 'factory'
                    ) {
                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    }

                    return $detail->is_approve_by_head;
                } else {
                    return true; // Include all details for other roles
                }
            })
            ->values(); // Ensure that the result is an array

        if ($purchaseRequest->status == 4) {
            // dd($filteredItemDetail);
            $this->updateMasterPRItems($filteredItemDetail);
        }

        return view(
            'purchase-requests.show',
            compact(
                'purchaseRequest',
                'user',
                'userCreatedBy',
                'files',
                'filteredItemDetail',
                'departments',
                'fromDeptNo',
                'approval',
            ),
        );
    }

    private function updateStatus($purchaseRequest)
    {
        // If PR not Rejected
        if ($purchaseRequest->status !== 5) {
            if ($purchaseRequest->autograph_1 !== null) {
                $purchaseRequest->status = 1;
            }

            // After Dept Head Autograph
            if ($purchaseRequest->autograph_2 !== null) {
                if (
                    $purchaseRequest->from_department === 'MOULDING' ||
                    $purchaseRequest->from_department === 'QA' ||
                    $purchaseRequest->from_department === 'QC' ||
                    $purchaseRequest->type === 'office'
                ) {
                    // If it's moulding/qa/qc then direct to purchaser
                    $purchaseRequest->status = 6;
                } elseif ($purchaseRequest->type === 'factory') {
                    // When GM has not signed
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
                if (
                    ($purchaseRequest->to_department->value === ToDepartment::PURCHASING->value &&
                        $purchaseRequest->type === 'factory') ||
                    $purchaseRequest->to_department->value === ToDepartment::MAINTENANCE->value
                ) {
                    if (
                        $purchaseRequest->from_department === 'COMPUTER' ||
                        $purchaseRequest->from_department === 'PERSONALIA'
                    ) {
                        // To verificator
                        $purchaseRequest->status = 2;
                    } else {
                        // Direct to Director
                        $purchaseRequest->status = 3;
                    }
                } elseif (
                    $purchaseRequest->to_department->value === ToDepartment::COMPUTER->value ||
                    $purchaseRequest->to_department->value === ToDepartment::PERSONALIA->value
                ) {
                    // When verificator has not signed
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
    }

    private function updateMasterPRItems($items)
    {
        if (isset($items)) {
            foreach ($items as $itemData) {
                $itemName = $itemData['item_name'];
                $price = $this->sanitizeCurrencyInput($itemData['price']);
                $currency = $itemData['currency'];

                // Check if the item exists in MasterDataPr
                $existingItem = MasterDataPr::where('name', $itemName)->first();
                // dd($existingItems);
                if (! $existingItem) {
                    // Case 1: Item not available in MasterDataPr
                    MasterDataPr::create([
                        'name' => $itemName,
                        'currency' => $currency,
                        'price' => $price, // Store the initial price
                    ]);
                } else {
                    // Case 2: Item available in MasterDataPr
                    $existingItem->update([
                        'price' => $existingItem->latest_price,
                        'latest_price' => $price,
                    ]);
                }
            }
        }
    }

    private function formatDecimal($value)
    {
        // Check if the number has no decimal part (i.e., is an integer)
        if (floor($value) == $value) {
            // If it's an integer, cast it to int to remove the decimal point
            return (int) $value;
        } else {
            // If it has a decimal part, return it as is
            return $value;
        }
    }

    public function saveSignaturePath(Request $request, $prId, int $section)
    {
        $pr = PurchaseRequest::findOrFail($prId);

        // Fetching the user from the request
        $user = $request->user();
        $imagePath = $request->input('imagePath');

        // Define the step-to-role mapping
        $stepMap = [
            1 => 'MAKER',
            2 => 'DEPT_HEAD',
            3 => 'VERIFICATOR',
            4 => 'DIRECTOR',
            5 => 'PURCHASER',
            6 => 'GM',
            7 => 'HEAD_DESIGN',
        ];

        $stepCode = $stepMap[$section] ?? null;

        // If stepCode exists, update the signatures table
        if ($stepCode) {
            $pr->signatures()->updateOrCreate(
                ['step_code' => $stepCode],
                [
                    'signed_by_user_id' => $user->id,
                    'image_path' => $imagePath,
                    'signed_at' => now(),
                ]
            );
        }

        // If the role is DIRECTOR, mark the approval timestamp
        if ($stepCode === 'DIRECTOR') {
            $pr->update([
                'approved_at' => now(),
            ]);
        }

        // Return success message
        return response()->json(['success' => 'Autograph saved successfully!']);
    }

    // REVISI PR DROPDOWN ITEM + PRICE
    public function getItemNames(Request $request)
    {
        $itemName = $request->query('itemName');
        // info('AJAX request received for item name: ' . $itemName);

        // Fetch item names and prices from the database based on user input
        $items = MasterDataPr::where('name', 'like', "%$itemName%")->get([
            'id',
            'name',
            'currency',
            'price',
            'latest_price',
        ]);

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
        $isPurchaser = Auth::user()->specification->name === 'PURCHASER';
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
        } elseif ($pr->status === 3) {
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
                        'is_approve_by_head' => auth()->user()->specification->name === 'PURCHASER'
                                ? 1
                                : $oldDetail->is_approve_by_head,
                    ]);

                    if ($pr->type === 'factory') {
                        $detail->update([
                            'is_approve_by_gm' => auth()->user()->specification->name === 'PURCHASER'
                                    ? 1
                                    : $oldDetail->is_approve_by_gm,
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->back()
            ->with(['success' => 'Purchase request updated successfully!']);
    }

    public function destroy($id)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DetailPurchaseRequest::where('purchase_request_id', $id)->delete();
        PurchaseRequest::find($id)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        return redirect()
            ->back()
            ->with(['success' => 'Purchase request deleted succesfully!']);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'string|max:255',
        ]);

        PurchaseRequest::find($id)->update([
            'status' => 5,
            'description' => $request->description,
        ]);

        return redirect()
            ->back()
            ->with(['success' => 'Purchase Request rejected']);
    }

    public function approveAllDetailItems($prId, $type)
    {
        if ($type === 'GM') {
            $details = DetailPurchaseRequest::where('purchase_request_id', $prId)
                ->where('is_approve_by_head', true)
                ->get();

            foreach ($details as $detail) {
                $detail->update(['is_approve_by_gm' => true]);
            }

            return response()->json(['success' => 'All detail approved successfully!']);
        }

        return response()->json(['error' => 'Something went wrong!']);
    }

    public function exportToPdf($id)
    {
        $user = Auth::user();
        $purchaseRequest = PurchaseRequest::with('itemDetail', 'createdBy')->find($id);
        $userCreatedBy = $purchaseRequest->createdBy;

        // Filter itemDetail based on user role
        $filteredItemDetail = $purchaseRequest->itemDetail
            ->filter(function ($detail) use ($user, $purchaseRequest) {
                $detail->quantity = $this->formatDecimal($detail->quantity);
                if ($user->specification->name === 'DIRECTOR') {
                    if ($purchaseRequest->type === 'factory') {
                        if ($purchaseRequest->to_department->value === ToDepartment::COMPUTER->value) {
                            return $detail->is_approve_by_head &&
                                $detail->is_approve_by_gm &&
                                $detail->is_approve_by_verificator;
                        }

                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    } else {
                        return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
                    }
                } elseif ($user->specification->name === 'VERIFICATOR') {
                    if (
                        $purchaseRequest->to_department->value === ToDepartment::COMPUTER->value &&
                        $purchaseRequest->type === 'factory'
                    ) {
                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    }

                    return $detail->is_approve_by_head;
                } else {
                    return true; // Include all details for other roles
                }
            })
            ->values(); // Ensure that the result is an array

        $pdf = Pdf::loadView(
            'pdf/pr-pdf',
            compact('purchaseRequest', 'user', 'userCreatedBy', 'filteredItemDetail'),
        )->setPaper('a4', 'landscape');

        // return view('pdf.pr-pdf', compact('purchaseRequest', 'user', 'userCreatedBy', 'filteredItemDetail'));
        return $pdf->download(
            'Purchase Request-'.
                $purchaseRequest->id.
                ' ('.
                $purchaseRequest->pr_no.
                ')'.
                '.pdf',
        );
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

        return redirect()
            ->back()
            ->with('success', 'Purchase request PO Number updated successfully!');
    }

    public function exportExcel()
    {
        $authDepartment = ucwords(strtolower(auth()->user()->department->name));
        $purchaseRequestIds = PurchaseRequest::where('from_department', $authDepartment)->pluck(
            'id',
        );

        return Excel::download(
            new PurchaseRequestWithDetailsExport($purchaseRequestIds),
            "purchase requests for $authDepartment .xlsx",
        );
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = $request->user();

        // pastikan PR sudah di-submit ke engine
        if (! $purchaseRequest->approvalRequest) {
            abort(400, 'Approval request not initialized.');
        }

        // optional remarks
        $remarks = $request->input('remarks');

        // core: approve step aktif untuk user ini
        $this->approvals->approve($purchaseRequest, $user->id, $remarks);

        return back()->with('success', 'Approved.');
    }

    public function rejectApproval(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate(['remarks' => ['nullable', 'string', 'max:255']]);

        $user = $request->user();

        if (! $purchaseRequest->approvalRequest) {
            abort(400, 'Approval request not initialized.');
        }

        $this->approvals->reject($purchaseRequest, $user->id, $request->remarks);

        return back()->with('success', 'Rejected.');
    }
}
