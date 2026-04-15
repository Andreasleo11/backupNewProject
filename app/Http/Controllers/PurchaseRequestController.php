<?php

namespace App\Http\Controllers;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
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
use App\Exports\PurchaseRequestWithDetailsExport;
use App\Http\Requests\ApprovePurchaseRequest;
use App\Http\Requests\RejectPurchaseRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\MasterDataPr;
use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
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
        private \App\Domain\PurchaseRequest\Services\PurchaseRequestSecurityService $securityService,
    ) {}


    public function create()
    {
        $items = MasterDataPr::get();
        $departments = Department::all();
        $defaultSig = $this->getDefaultSignature->execute((int) auth()->id());
        $user = auth()->user();

        return view('purchase-requests.pr-form', [
            'items' => $items,
            'departments' => $departments,
            'hasDefaultSignature' => $defaultSig !== null,
            'signaturePreviewUrl' => $defaultSig ? route('signatures.show', $defaultSig->id) : null,
            'flags' => [
                'userCanEverSelectImport' => $this->securityService->canUserSelectImportPath($user),
                'targetDepartmentPurchasing' => \App\Enums\ToDepartment::PURCHASING->value,
                'isOwner' => true,
            ]
        ]);
    }

    public function edit(int $id)
    {
        $user = auth()->user();
        $purchaseRequest = PurchaseRequest::byRole($user)
            ->with(['itemDetail', 'approvalRequest.steps.actedUser'])
            ->findOrFail($id);

        // Authorize using PurchaseRequestPolicy@update
        $this->authorize('update', $purchaseRequest);

        $items = MasterDataPr::get();
        $departments = Department::all();
        $defaultSig = $this->getDefaultSignature->execute((int) auth()->id());

        return view('purchase-requests.pr-form', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
            'departments' => $departments,
            'hasDefaultSignature' => $defaultSig !== null,
            'signaturePreviewUrl' => $defaultSig ? route('signatures.show', $defaultSig->id) : null,
            'flags' => [
                'userCanEverSelectImport' => $this->securityService->canUserSelectImportPath($user),
                'targetDepartmentPurchasing' => \App\Enums\ToDepartment::PURCHASING->value,
                'isOwner' => (int) $user->id === (int) $purchaseRequest->user_id_create,
            ]
        ]);
    }

    public function store(
        StorePurchaseRequest $request,
        \App\Domain\PurchaseRequest\Services\PriceSanitizer $priceSanitizer,
        Approvals $approvals,
    ) {
        // Always create as draft — workflow is started explicitly via performSignAndSubmit
        $request->merge(['is_draft' => true]);
        $dto = \App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO::fromValidated($request, $priceSanitizer);
        $pr = app(\App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest::class)->handle($dto);

        if ($request->input('submit_action') === 'sign_and_submit') {
            $this->performSignAndSubmit($pr, auth()->id(), $approvals);

            return redirect()->route('purchase-requests.show', $pr->id)
                ->with('success', 'Purchase request submitted and signed successfully.');
        }

        if ($request->input('submit_action') === 'save_and_setup_signature') {
            return redirect()->route('signatures.manage', ['return_to' => route('purchase-requests.edit', $pr->id)])
                ->with('success', 'Purchase request saved as draft. Please set up your signature before submitting.');
        }

        return redirect()->route('purchase-requests.show', $pr->id)
            ->with('success', 'Purchase request saved as draft.');
    }

    public function show(int $id, GetPurchaseRequestDetail $query)
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Models\User $user */
        $user = auth()->user();
        $vm = $query->handle($id, $user);

        // Explicit Policy Check
        $this->authorize('view', $vm->purchaseRequest);

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

    /**
     * Render a partial view containing key details of a PR for quick inspection.
     */
    public function quickView(int $id, GetPurchaseRequestDetail $query)
    {
        /** @var \App\Infrastructure\Persistence\Eloquent\Models\User $user */
        $user = auth()->user();
        $vm = $query->handle($id, $user);

        // Explicit Policy Check
        $this->authorize('view', $vm->purchaseRequest);

        return view('purchase-requests.partials.quick-view-content', [
            'pr' => $vm->purchaseRequest,
            'filteredItemDetail' => $vm->filteredItemDetail,
            'totals' => $vm->totals,
            'flags' => $vm->flags,
        ]);
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
        $purchaseRequest = PurchaseRequest::byRole(auth()->user())->findOrFail((int) $id);
        $this->authorize('update', $purchaseRequest);

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
            isImport: $request->has('is_import') ? filter_var($request->is_import, FILTER_VALIDATE_BOOLEAN) : null,
        );

        // Execute UseCase
        $useCase->handle($dto);

        if ($request->input('submit_action') === 'sign_and_submit') {
            $pr = PurchaseRequest::findOrFail((int) $id);
            $this->performSignAndSubmit(
                $pr,
                Auth::id(),
                app(Approvals::class)
            );

            return redirect()->route('purchase-requests.show', $id)
                ->with('success', 'Purchase request updated, signed, and submitted.');
        }

        $msg = ($purchaseRequest->workflow_status === 'DRAFT') 
            ? 'Purchase request saved as draft.' 
            : 'Purchase request changes saved successfully.';

        if ($request->input('submit_action') === 'save_and_setup_signature') {
            return redirect()->route('signatures.manage', ['return_to' => route('purchase-requests.edit', $id)])
                ->with('success', $msg . ' Please set up your signature before submitting.');
        }

        return redirect()->route('purchase-requests.show', $id)
            ->with('success', $msg);
    }

    /**
     * Sign & Submit from the show page (DRAFT PR, creator only).
     */
    public function signAndSubmit(
        Request $request,
        PurchaseRequest $purchaseRequest,
        Approvals $approvals,
    ) {
        // Only the creator can sign & submit their own DRAFT
        if ((int) auth()->id() !== (int) $purchaseRequest->user_id_create) {
            abort(403, 'Only the creator can sign and submit this request.');
        }
        $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];
        if (! in_array($purchaseRequest->workflow_status, $allowedStatuses)) {
            abort(422, 'Only DRAFT, RETURNED, or REJECTED requests can be submitted.');
        }

        $this->performSignAndSubmit($purchaseRequest, auth()->id(), $approvals);

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
    ): void {
        $defaultSig = $this->getDefaultSignature->execute($userId);
        abort_unless($defaultSig !== null, 422, 'You must set up a signature before submitting.');

        // NOTE: We no longer save the MAKER signature to the legacy 'purchase_request_signatures' table 
        // for new PRs. It is only kept for legacy data compatibility.
        // The requester is now tracked via $approvals->submit() into 'approval_requests.submitted_by'.

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

    public function exportToPdf(int $id, GetPurchaseRequestDetail $query)
    {
        $user = Auth::user();
        
        // Use the centralized view model to fetch the exact same data structure as the 'show' detail page
        $vm = $query->handle($id, $user);

        // Map digital signatures for the PDF footer
        $signatures = $vm->purchaseRequest->signatures->mapWithKeys(function ($sig) {
            return [$sig->section => $sig->signature_path];
        })->toArray();

        $pdf = Pdf::loadView(
            'pdf/pr-pdf',
            [
                'purchaseRequest' => $vm->purchaseRequest,
                'user' => $user,
                'userCreatedBy' => $vm->meta['userCreatedBy'],
                'filteredItemDetail' => $vm->filteredItemDetail,
                'totals' => $vm->totals,
                'departments' => $vm->departments,
                'approval' => $vm->approval,
                'signatures' => $signatures,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'Purchase Request-' .
                $vm->purchaseRequest->id .
                ' (' .
                $vm->purchaseRequest->pr_no .
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
        $isAjax = $request->ajax() || $request->wantsJson();

        // AJAX quick-view sends `auto_approve_items: true` explicitly.
        // Regular form submissions rely on the DTO default (true).
        $autoApproveItems = $request->boolean('auto_approve_items', true);

        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: (int) $purchaseRequest->id,
                actorUserId: (int) auth()->id(),
                remarks: $request->input('remarks'),
                autoApproveItems: $autoApproveItems,
            ));

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase Request approved successfully! All pending items have been auto-approved.',
                ]);
            }

            return back()->with('toast_success', 'Purchase Request approved successfully!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            }
            return back()->with('error', $e->getMessage())->setStatusCode(403);
        } catch (\DomainException|\RuntimeException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Approval failed', ['pr_id' => $purchaseRequest->id, 'error' => $e->getMessage()]);

            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Failed to approve purchase request'], 500);
            }

            return back()->with('error', 'Failed to approve purchase request')->setStatusCode(500);
        }
    }


    public function reject(RejectPurchaseRequest $request, PurchaseRequest $purchaseRequest, RejectPR $useCase)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        // AJAX quick-view sends `auto_approve_items: true` — reuse the same flag to also
        // auto-reject any pending item-level approvals before rejecting the workflow step.
        $autoApproveItems = $request->boolean('auto_approve_items', true);

        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: (int) $purchaseRequest->id,
                actorUserId: (int) auth()->id(),
                remarks: $request->input('remarks'),
                autoApproveItems: $autoApproveItems,
            ));

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase Request rejected. All pending items have been marked as rejected.',
                ]);
            }

            return back()->with('toast_success', 'Purchase Request rejected.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            }
            return back()->with('error', $e->getMessage())->setStatusCode(403);
        } catch (\DomainException|\RuntimeException $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Rejection failed', ['pr_id' => $purchaseRequest->id, 'error' => $e->getMessage()]);

            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Failed to reject purchase request'], 500);
            }

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
            'batch_id' => $result['batch_id'] ?? null,
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
            'batch_id' => $result['batch_id'] ?? null,
            'rejected' => $result['rejected'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }

    /**
     * Poll the status of specific job batches.
     * Used by the frontend Alpine component to render progress bars.
     */
    public function batchStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('batch-approve', PurchaseRequest::class);
        
        $ids = $request->input('ids', []);
        $statuses = [];

        foreach ((array) $ids as $id) {
            $batch = Bus::findBatch($id);
            if ($batch) {
                $statuses[] = [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'totalJobs' => $batch->totalJobs,
                    'pendingJobs' => $batch->pendingJobs,
                    'failedJobs' => $batch->failedJobs,
                    'progress' => $batch->progress(),
                    'finished' => $batch->finished(),
                    'canceled' => $batch->canceled(),
                ];
            }
        }

        return response()->json(['success' => true, 'batches' => $statuses]);
    }
}
