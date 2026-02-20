<?php

namespace App\Http\Controllers;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\AddSignatureDTO;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\DTOs\ReturnPurchaseRequestDTO;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestDetail;
use App\Application\PurchaseRequest\Services\MasterPrItemService;
use App\Application\PurchaseRequest\Services\PurchaseRequestItemFilter;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest as ApprovePR;
use App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests;
use App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest as RejectPR;
use App\Application\PurchaseRequest\UseCases\ReturnPurchaseRequest;
use App\Application\Signature\UseCases\GetDefaultActiveUserSignature;
use App\DataTables\PurchaseRequestsDataTable;
use App\Exports\PurchaseRequestWithDetailsExport;
use App\Http\Requests\ApprovePurchaseRequest;
use App\Http\Requests\RejectPurchaseRequest;
use App\Http\Requests\SaveSignatureRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Department;
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
        private ReturnPurchaseRequest $returnUseCase,
        private GetDefaultActiveUserSignature $getDefaultSignature,
        private BatchApprovePurchaseRequests $batchApproveUseCase,
        private BatchRejectPurchaseRequests $batchRejectUseCase,
        private \App\Domain\PurchaseRequest\Services\PurchaseRequestApprovalContextBuilder $contextBuilder,
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

        // Determine if the user can batch-approve/reject PRs (separate from individual pr.approve)
        $canBatchApprove = Auth::user()->can('pr.batch-approve');

        return $dataTable->render('purchase-requests.index', compact('stats', 'canBatchApprove'));
    }

    public function create()
    {
        $items = MasterDataPr::get();
        $departments = Department::all();
        $defaultSig = $this->getDefaultSignature->execute((int) auth()->id());

        return view('purchase-requests.pr-form', [
            'items' => $items,
            'departments' => $departments,
            'hasDefaultSignature' => $defaultSig !== null,
            'signaturePreviewUrl' => $defaultSig ? route('signatures.show', $defaultSig->id) : null,
        ]);
    }

    public function edit(int $id)
    {
        $purchaseRequest = PurchaseRequest::with(['itemDetail', 'approvalRequest.steps.actedUser'])->findOrFail($id);

        // Only the creator can edit
        if (auth()->id() !== (int) $purchaseRequest->user_id_create) {
            abort(403, 'You are not authorized to edit this request.');
        }

        // Determine editable states using workflow_status (modern) with legacy fallback
        $workflowStatus = $purchaseRequest->workflow_status; // computed attribute
        $legacyStatus = $purchaseRequest->status;

        $editableWorkflowStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];
        $editableLegacyStatuses = [0, 'draft', 8]; // 0 = draft, 8 = draft (legacy)

        $isEditable = in_array($workflowStatus, $editableWorkflowStatuses)
            || in_array($legacyStatus, $editableLegacyStatuses);

        if (! $isEditable) {
            abort(403, 'This request cannot be edited in its current state (' . $workflowStatus . ').');
        }

        $items = MasterDataPr::get();
        $departments = Department::all();
        $defaultSig = $this->getDefaultSignature->execute((int) auth()->id());

        return view('purchase-requests.pr-form', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
            'departments' => $departments,
            'hasDefaultSignature' => $defaultSig !== null,
            'signaturePreviewUrl' => $defaultSig ? route('signatures.show', $defaultSig->id) : null,
        ]);
    }

    public function store(
        StorePurchaseRequest $request,
        \App\Domain\PurchaseRequest\Services\PriceSanitizer $priceSanitizer,
        Approvals $approvals,
        \App\Application\PurchaseRequest\UseCases\AddSignature $addSignature,
    ) {
        // Always create as draft — workflow is started explicitly via performSignAndSubmit
        $request->merge(['is_draft' => true]);
        $dto = \App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO::fromValidated($request, $priceSanitizer);
        $pr = app(\App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest::class)->handle($dto);

        if ($request->input('submit_action') === 'sign_and_submit') {
            $this->performSignAndSubmit($pr, auth()->id(), $approvals, $addSignature);

            return redirect()->route('purchase-requests.show', $pr->id)
                ->with('success', 'Purchase request submitted and signed successfully.');
        }

        return redirect()->route('purchase-requests.show', $pr->id)
            ->with('success', 'Purchase request saved as draft.');
    }

    public function show(int $id, GetPurchaseRequestDetail $query)
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Models\User $user */
        $user = auth()->user();

        $vm = $query->handle($id, $user);

        // Pass signature preview URL for the Sign & Submit modal on show page
        $canSignAndSubmit = $vm->flags['canSignAndSubmit'] ?? false;
        $signaturePreviewUrl = null;
        if ($canSignAndSubmit && ($vm->flags['defaultSignaturePath'] ?? null)) {
            $defaultSig = $this->getDefaultSignature->execute((int) $user->id);
            $signaturePreviewUrl = $defaultSig ? route('signatures.show', $defaultSig->id) : null;
        }

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
            'signaturePreviewUrl' => $signaturePreviewUrl,
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
                purpose: $item['purpose'] ?? ''
            ), $validated['items']),
            isImport: $request->is_import === 'true',
        );

        // Execute UseCase
        $useCase->handle($dto);

        if ($request->input('submit_action') === 'sign_and_submit') {
            $pr = PurchaseRequest::findOrFail((int) $id);
            $this->performSignAndSubmit(
                $pr,
                Auth::id(),
                app(Approvals::class),
                app(\App\Application\PurchaseRequest\UseCases\AddSignature::class)
            );

            return redirect()->route('purchase-requests.show', $id)
                ->with('success', 'Purchase request updated, signed, and submitted.');
        }

        return redirect()->route('purchase-requests.show', $id)
            ->with('success', 'Purchase request saved as draft.');
    }

    /**
     * Sign & Submit from the show page (DRAFT PR, creator only).
     */
    public function signAndSubmit(
        Request $request,
        PurchaseRequest $purchaseRequest,
        Approvals $approvals,
        \App\Application\PurchaseRequest\UseCases\AddSignature $addSignature,
    ) {
        // Only the creator can sign & submit their own DRAFT
        if ((int) auth()->id() !== (int) $purchaseRequest->user_id_create) {
            abort(403, 'Only the creator can sign and submit this request.');
        }
        $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];
        if (! in_array($purchaseRequest->workflow_status, $allowedStatuses)) {
            abort(422, 'Only DRAFT, RETURNED, or REJECTED requests can be submitted.');
        }

        $this->performSignAndSubmit($purchaseRequest, auth()->id(), $approvals, $addSignature);

        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', 'Purchase request signed and submitted for approval.');
    }

    /**
     * Shared: save MAKER signature + start approval workflow.
     */
    private function performSignAndSubmit(
        PurchaseRequest $pr,
        int $userId,
        Approvals $approvals,
        \App\Application\PurchaseRequest\UseCases\AddSignature $addSignature,
    ): void {
        $defaultSig = $this->getDefaultSignature->execute($userId);
        abort_unless($defaultSig !== null, 422, 'You must set up a signature before submitting.');

        // Save MAKER signature (section 1)
        $addSignature->handle(new AddSignatureDTO(
            purchaseRequestId: $pr->id,
            signedByUserId: $userId,
            section: 1,
            imagePath: $defaultSig->filePath,
        ));

        // Build approval context from the PR model (required by the rule resolver to match templates)
        $pr->loadMissing('items');
        $ctx = $this->contextBuilder->build(
            fromDepartment: $pr->from_department,
            toDepartment: $pr->to_department->value,
            branch: $pr->branch->value,
            isOffice: $pr->type === 'office',
            items: $pr->items->toArray(),
        );

        // Start approval workflow with context
        $approvals->submit($pr, $userId, $ctx);
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
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\DomainException $e) {
            // Domain exceptions contain user-friendly messages about business rules
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Deletion failed', ['pr_id' => $id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with(['error' => 'Failed to delete purchase request']);
        }
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
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\DomainException $e) {
            // Domain exceptions contain user-friendly messages
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Cancellation failed', ['pr_id' => $id, 'error' => $e->getMessage()]);

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
        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: (int) $purchaseRequest->id,
                actorUserId: (int) auth()->id(),
                remarks: $request->input('remarks')
            ));

            return back()->with('toast_success', 'Purchase Request approved successfully!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage())->setStatusCode(403);
        } catch (\DomainException|\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Approval failed', ['pr_id' => $purchaseRequest->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Failed to approve purchase request')->setStatusCode(500);
        }
    }

    public function reject(RejectPurchaseRequest $request, PurchaseRequest $purchaseRequest, RejectPR $useCase)
    {
        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: (int) $purchaseRequest->id,
                actorUserId: (int) auth()->id(),
                remarks: $request->input('remarks')
            ));

            return back()->with('toast_success', 'Purchase Request rejected.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage())->setStatusCode(403);
        } catch (\DomainException|\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Rejection failed', ['pr_id' => $purchaseRequest->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Failed to reject purchase request')->setStatusCode(500);
        }
    }

    public function returnForRevision(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->returnUseCase->handle(new ReturnPurchaseRequestDTO(
                purchaseRequestId: (int) $purchaseRequest->id,
                actorUserId: (int) auth()->id(),
                reason: $request->input('reason'),
            ));

            return redirect()
                ->back()
                ->with(['success' => 'PR returned to creator for revision.']);
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Batch approve multiple PRs at once.
     * Requires 'pr.batch-approve' permission — separate from individual 'pr.approve'.
     */
    public function batchApprove(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('batch-approve', PurchaseRequest::class);

        $ids = $request->input('ids', []);
        $userId = Auth::id();

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $result = $this->batchApproveUseCase->handle($ids, $userId);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'approved' => $result['approved'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }

    /**
     * Batch reject multiple PRs at once.
     * Requires 'pr.batch-approve' permission — separate from individual 'pr.approve'.
     */
    public function batchReject(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('batch-approve', PurchaseRequest::class);

        $ids = $request->input('ids', []);
        $rejectionReason = $request->input('rejection_reason', '');
        $userId = Auth::id();

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $result = $this->batchRejectUseCase->handle($ids, $userId, $rejectionReason);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'rejected' => $result['rejected'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }
}
