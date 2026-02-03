<?php

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


afterEach(function () {
    Mockery::close();
});

test('handle approves and syncs pr', function () {
    $approvals = Mockery::mock(Approvals::class);
    $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
    $repo = Mockery::mock(PurchaseRequestRepository::class);
    $itemValidator = Mockery::mock(\App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService::class);

    $useCase = new ApprovePurchaseRequest($approvals, $syncWorkflow, $repo, $itemValidator);

    $prId = 123;
    $userId = 456;
    $remarks = 'Approved';

    $dto = new ApprovalActionDTO($prId, $userId, $remarks);

    $mockPr = Mockery::mock(PurchaseRequest::class);

    $repo->shouldReceive('find')
        ->once()
        ->with($prId)
        ->andReturn($mockPr);

    $repo->shouldReceive('loadForApprovalContext')
        ->twice()
        ->with($mockPr)
        ->andReturn($mockPr);

    // Mock item validator to bypass item validation (returns null approver type)
    $itemValidator->shouldReceive('getApproverTypeFromStep')->andReturn(null);

    $approvals->shouldReceive('approve')
        ->once()
        ->with($mockPr, $userId, $remarks);

    $syncWorkflow->shouldReceive('sync')
        ->once()
        ->with($mockPr);

    $useCase->handle($dto);

    expect(true)->toBeTrue();
});

test('handle throws exception if pr not found', function () {
    $approvals = Mockery::mock(Approvals::class);
    $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
    $repo = Mockery::mock(PurchaseRequestRepository::class);
    $itemValidator = Mockery::mock(\App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService::class);

    $useCase = new ApprovePurchaseRequest($approvals, $syncWorkflow, $repo, $itemValidator);

    $dto = new ApprovalActionDTO(999, 1, 'remarks');

    $repo->shouldReceive('find')
        ->once()
        ->with(999)
        ->andReturnNull();

    $useCase->handle($dto);
})->throws(ModelNotFoundException::class);
