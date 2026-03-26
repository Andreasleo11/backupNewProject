<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Domain\PurchaseRequest\Services\PurchaseRequestNumberGenerator;
use App\Http\Resources\V1\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

final class PurchaseRequestController extends Controller
{
    public function __construct(
        private readonly PurchaseRequestRepository $repository,
        private readonly PurchaseRequestNumberGenerator $numberGenerator
    ) {}

    /**
     * List purchase requests with pagination and filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PurchaseRequest::with(['fromDepartment', 'items', 'creator'])
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $purchaseRequests = $query->paginate($perPage);

        return PurchaseRequestResource::collection($purchaseRequests);
    }

    /**
     * Create a new purchase request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expected_date' => ['required', 'date', 'after:today'],
            'cost_reduce_idea' => ['nullable', 'string'],
            'to_department' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.uom' => ['required', 'string'],
            'items.*.purpose' => ['required', 'string'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Generate document number
            $user = $request->user();
            $docNum = $this->numberGenerator->generate($user->department);

            // Create PR
            $pr = $this->repository->create([
                'doc_num' => $docNum,
                'expected_date' => $validated['expected_date'],
                'cost_reduce_idea' => $validated['cost_reduce_idea'] ?? null,
                'to_department' => $validated['to_department'],
                'department_id' => $user->department_id,
                'created_by' => $user->id,
                'status' => 'pending',
            ]);

            // Add items
            $this->repository->addItems($pr, $validated['items']);

            DB::commit();

            $pr->load(['fromDepartment', 'items', 'creator']);

            return $this->successResponse(
                new PurchaseRequestResource($pr),
                'Purchase request created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to create purchase request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Show a single purchase request.
     */
    public function show(int $id): JsonResponse
    {
        $pr = PurchaseRequest::with([
            'fromDepartment',
            'items',
            'creator',
            'approvalRequest.steps',
        ])->find($id);

        if (! $pr) {
            return $this->errorResponse('Purchase request not found', 404);
        }

        return $this->successResponse(
            new PurchaseRequestResource($pr)
        );
    }

    /**
     * Update purchase request (only if pending).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pr = PurchaseRequest::find($id);

        if (! $pr) {
            return $this->errorResponse('Purchase request not found', 404);
        }

        if ($pr->status !== 'pending') {
            return $this->errorResponse(
                'Cannot update purchase request that is not pending',
                422
            );
        }

        $validated = $request->validate([
            'expected_date' => ['sometimes', 'date', 'after:today'],
            'cost_reduce_idea' => ['nullable', 'string'],
            'to_department' => ['sometimes', 'string'],
        ]);

        $pr->update($validated);
        $pr->load(['fromDepartment', 'items', 'creator']);

        return $this->successResponse(
            new PurchaseRequestResource($pr),
            'Purchase request updated successfully'
        );
    }

    /**
     * Delete/cancel purchase request.
     */
    public function destroy(int $id): JsonResponse
    {
        $pr = PurchaseRequest::find($id);

        if (! $pr) {
            return $this->errorResponse('Purchase request not found', 404);
        }

        if ($pr->status !== 'pending') {
            return $this->errorResponse(
                'Cannot delete purchase request that is not pending',
                422
            );
        }

        $pr->update(['status' => 'cancelled']);

        return $this->successResponse(
            null,
            'Purchase request cancelled successfully'
        );
    }

    /**
     * Approve purchase request.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        // This is a simplified version - actual approval logic would use
        // the approval workflow system
        return $this->successResponse(
            ['message' => 'Approval endpoint - integrate with approval service'],
            'This endpoint needs integration with approval workflow service'
        );
    }

    /**
     * Reject purchase request.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        return $this->successResponse(
            ['message' => 'Rejection endpoint - integrate with approval service'],
            'This endpoint needs integration with approval workflow service'
        );
    }

    /**
     * Get approval history.
     */
    public function history(int $id): JsonResponse
    {
        $pr = PurchaseRequest::with(['approvalRequest.steps.approver'])
            ->find($id);

        if (! $pr) {
            return $this->errorResponse('Purchase request not found', 404);
        }

        return $this->successResponse([
            'purchase_request_id' => $pr->id,
            'approval_steps' => $pr->approvalRequest?->steps ?? [],
        ]);
    }
}
