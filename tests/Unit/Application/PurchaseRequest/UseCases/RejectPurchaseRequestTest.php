<?php

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class RejectPurchaseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_rejects_and_syncs_pr()
    {
        // Arrange
        $approvals = Mockery::mock(Approvals::class);
        $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
        $repo = Mockery::mock(PurchaseRequestRepository::class);

        $useCase = new RejectPurchaseRequest($approvals, $syncWorkflow, $repo);

        $prId = 123;
        $userId = 456;
        $remarks = 'Rejected';

        $dto = new ApprovalActionDTO($prId, $userId, $remarks);

        $mockPr = Mockery::mock(PurchaseRequest::class);

        // Expectations
        $repo->shouldReceive('find')
            ->once()
            ->with($prId)
            ->andReturn($mockPr);

        $repo->shouldReceive('loadForApprovalContext')
            ->twice()
            ->with($mockPr)
            ->andReturn($mockPr);

        $approvals->shouldReceive('reject')
            ->once()
            ->with($mockPr, $userId, $remarks);

        $syncWorkflow->shouldReceive('sync')
            ->once()
            ->with($mockPr);

        // Act
        $useCase->handle($dto);

        // Assert
        $this->assertTrue(true);
    }

    public function test_handle_throws_exception_if_pr_not_found()
    {
        $approvals = Mockery::mock(Approvals::class);
        $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
        $repo = Mockery::mock(PurchaseRequestRepository::class);

        $useCase = new RejectPurchaseRequest($approvals, $syncWorkflow, $repo);

        $dto = new ApprovalActionDTO(999, 1, 'remarks');

        $repo->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturnNull();

        $this->expectException(ModelNotFoundException::class);

        $useCase->handle($dto);
    }
}
