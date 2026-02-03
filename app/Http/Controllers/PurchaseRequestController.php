<?php

namespace App\Http\Controllers;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestDetail;
use App\Application\PurchaseRequest\Services\MasterPrItemService;
use App\Application\PurchaseRequest\Services\PurchaseRequestItemFilter;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest as ApprovePR;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest as RejectPR;
use App\DataTables\PurchaseRequestsDataTable;
use App\Exports\PurchaseRequestWithDetailsExport;
use App\Http\Requests\ApproveAllDetailItems;
use App\Http\Requests\ApprovePurchaseRequest;
use App\Http\Requests\RejectPurchaseRequest;
use App\Http\Requests\RejectPurchaseRequestManual;
use App\Http\Requests\SaveSignatureRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Department;
use App\Models\DetailPurchaseRequest;
use App\Models\MasterDataPr;
use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseRequestController extends Controller
{
    public function __construct(
        private Approvals $approvals,
        private PurchaseRequestItemFilter $itemFilter,
        private MasterPrItemService $masterPrService,
    ) {}

    public function index(
        Request $request,
        PurchaseRequestsDataTable $dataTable,
        \App\Application\PurchaseRequest\Queries\GetPurchaseRequestList $query,
        \App\Application\PurchaseRequest\Queries\GetPurchaseRequestStats $statsQuery
    ) {
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

        if ($startDate && $endDate) {
            $request->session()->put('start_date', $startDate);
            $request->session()->put('end_date', $endDate);
        }

        if ($status) {
            $request->session()->put('status', $status);
        }

        if ($branch) {
            $request->session()->put('branch', $branch);
        }

        $dto = new \App\Application\PurchaseRequest\DTOs\GetPurchaseRequestListDTO(
            userId: Auth::id(),
            startDate: $startDate,
            endDate: $endDate,
            status: $status,
            branch: $branch,
            perPage: 10
        );

        // Fetch purchase requests using the Query class
        $purchaseRequests = $query->handle($dto);

        // Append the filter parameters to the pagination links
        $purchaseRequests->appends([
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'branch' => $branch,
        ]);

        // Get stats for dashboard
        $stats = $statsQuery->execute();

        return $dataTable->render('purchase-requests.index', compact('stats'));
    }

    public function create()
    {
        $items = MasterDataPr::get();
        $departments = Department::all();

        return view('purchase-requests.create', compact('items', 'departments'));
    }

    public function store(
        StorePurchaseRequest $request,
        \App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest $useCase,
        \App\Domain\PurchaseRequest\Services\PriceSanitizer $priceSanitizer
    ) {
        $user = Auth::user();

        $items = array_map(function ($item) use ($priceSanitizer) {
            return new \App\Application\PurchaseRequest\DTOs\PurchaseRequestItemDTO(
                itemName: (string) $item['item_name'],
                quantity: (float) $item['quantity'],
                purpose: (string) $item['purpose'],
                price: $priceSanitizer->sanitize($item['price'] ?? 0),
                uom: (string) $item['uom'],
                currency: (string) $item['currency'],
            );
        }, $request->input('items', []));

        $dto = new \App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO(
            requestedByUserId: $user->id,
            fromDepartment: $request->input('from_department'),
            toDepartment: $request->input('to_department'), // already normalized
            branch: $request->branch,
            datePr: $request->input('date_of_pr'),
            dateRequired: $request->input('date_of_required'),
            remark: $request->input('remark'),
            supplier: $request->input('supplier'),
            pic: $request->input('pic'),
            isDraft: (bool) $request->is_draft,
            isImport: $request->has('is_import') ? $request->is_import === 'true' : null,
            items: $items
        );

        $useCase->handle($dto);

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase request created successfully');
    }

    public function show(int $id, GetPurchaseRequestDetail $query)
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Models\User $user */
        $user = auth()->user();

        $vm = $query->handle($id, $user);

        return view('purchase-requests.show', [
            'purchaseRequest' => $vm->purchaseRequest,
            'user' => $user,
            'userCreatedBy' => $vm->meta['userCreatedBy'],
            'files' => $vm->files,
            'filteredItemDetail' => $vm->filteredItemDetail,
            'departments' => $vm->departments,
            'fromDeptNo' => $vm->fromDeptNo,
            'approval' => $vm->approval,

            'flags' => $vm->flags,
            'totals' => $vm->totals,
        ]);
    }

    public function saveSignaturePath(
        SaveSignatureRequest $request,
        $prId,
        int $section,
        \App\Application\PurchaseRequest\UseCases\AddSignature $useCase
    ) {
        $dto = new \App\Application\PurchaseRequest\DTOs\AddSignatureDTO(
            purchaseRequestId: (int) $prId,
            signedByUserId: $request->user()->id,
            section: $section,
            imagePath: $request->input('imagePath')
        );

        $useCase->handle($dto);

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

    public function update(
        UpdatePurchaseRequest $request,
        $id,
        \App\Application\PurchaseRequest\UseCases\UpdatePurchaseRequest $useCase
    ) {
        $validated = $request->validated();

        // Build DTO
        $dto = new \App\Application\PurchaseRequest\DTOs\UpdatePurchaseRequestDTO(
            purchaseRequestId: (int) $id,
            updatedByUserId: Auth::id(),
            toDepartment: $validated['to_department'],
            branch: $validated['branch'],
            datePr: $validated['date_of_pr'],
            dateRequired: $validated['date_of_required'],
            remark: $validated['remark'] ?? null,
            supplier: $validated['supplier'] ?? null,
            pic: $validated['pic'] ?? null,
            items: array_map(fn ($item) => new \App\Application\PurchaseRequest\DTOs\PurchaseRequestItemDTO(
                itemName: $item['item_name'],
                quantity: (float) $item['quantity'],
                uom: $item['uom'],
                price: (float) $this->masterPrService->sanitizeCurrencyInput($item['price']),
                currency: $item['currency'] ?? 'IDR',
                purpose: $item['purpose'] ?? null
            ), $validated['items']),
            isImport: $request->is_import === 'true',
        );

        // Execute UseCase
        $useCase->handle($dto);

        return redirect()
            ->back()
            ->with(['success' => 'Purchase request updated successfully!']);
    }

    public function destroy(
        $id,
        \App\Application\PurchaseRequest\UseCases\DeletePurchaseRequest $useCase
    ) {
        try {
            // Execute UseCase
            $useCase->handle((int) $id, Auth::id());

            return redirect()
                ->back()
                ->with(['success' => 'Purchase request deleted successfully!']);
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(['error' => 'Failed to delete purchase request']);
        }
    }

    public function reject(
        RejectPurchaseRequestManual $request,
        $id,
        \App\Application\PurchaseRequest\UseCases\ManualRejectPurchaseRequest $useCase
    ) {
        $dto = new \App\Application\PurchaseRequest\DTOs\ManualRejectPurchaseRequestDTO(
            purchaseRequestId: (int) $id,
            description: $request->description
        );

        $useCase->handle($dto);

        return redirect()
            ->back()
            ->with(['success' => 'Purchase Request rejected']);
    }

    public function approveAllDetailItems($prId, ApproveAllDetailItems $request)
    {
        $type = $request->validated()['type'];

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

        // Format quantities before filtering
        $purchaseRequest->itemDetail->each(function ($detail) {
            $detail->quantity = $this->masterPrService->formatDecimal($detail->quantity);
        });

        // Filter items based on user role using the service
        $filteredItemDetail = $this->itemFilter->filterItemsForUser(
            $user,
            $purchaseRequest,
            $purchaseRequest->itemDetail
        );

        $pdf = Pdf::loadView(
            'pdf/pr-pdf',
            compact('purchaseRequest', 'user', 'userCreatedBy', 'filteredItemDetail'),
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'Purchase Request-' .
                $purchaseRequest->id .
                ' (' .
                $purchaseRequest->pr_no .
                ')' .
                '.pdf',
        );
    }

    public function cancel(
        Request $request,
        $id,
        \App\Application\PurchaseRequest\UseCases\CancelPurchaseRequest $useCase
    ) {
        try {
            // Build DTO
            $dto = new \App\Application\PurchaseRequest\DTOs\CancelPurchaseRequestDTO(
                purchaseRequestId: (int) $id,
                cancelledByUserId: Auth::id(),
                reason: $request->description
            );

            // Execute UseCase
            $useCase->handle($dto);

            return redirect()->back()->with('success', 'Purchase request canceled successfully!');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel purchase request');
        }
    }

    public function updatePoNumber(
        Request $request,
        $id,
        \App\Application\PurchaseRequest\UseCases\UpdatePoNumber $useCase
    ) {
        try {
            // Build DTO
            $dto = new \App\Application\PurchaseRequest\DTOs\UpdatePoNumberDTO(
                purchaseRequestId: (int) $id,
                poNumber: $request->po_number,
                updatedByUserId: Auth::id()
            );

            // Execute UseCase
            $useCase->handle($dto);

            return redirect()
                ->back()
                ->with('success', 'Purchase request PO Number updated successfully!');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update PO Number');
        }
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

    public function approve(ApprovePurchaseRequest $request, PurchaseRequest $purchaseRequest, ApprovePR $useCase)
    {
        $useCase->handle(new ApprovalActionDTO(
            purchaseRequestId: (int) $purchaseRequest->id,
            actorUserId: (int) auth()->id(),
            remarks: $request->input('remarks')
        ));

        return back()->with('success', 'Approved.');
    }

    public function rejectWorkflow(RejectPurchaseRequest $request, PurchaseRequest $purchaseRequest, RejectPR $useCase)
    {
        $useCase->handle(new ApprovalActionDTO(
            purchaseRequestId: (int) $purchaseRequest->id,
            actorUserId: (int) auth()->id(),
            remarks: $request->input('remarks')
        ));

        return back()->with('success', 'Rejected.');
    }
}
