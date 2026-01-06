<?php

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class ApprovePurchaseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_approves_and_syncs_pr()
    {
        // Arrange
        $approvals = Mockery::mock(Approvals::class);
        $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
        $repo = Mockery::mock(PurchaseRequestRepository::class);

        $useCase = new ApprovePurchaseRequest($approvals, $syncWorkflow, $repo);

        $prId = 123;
        $userId = 456;
        $remarks = 'Approved';

        $dto = new ApprovalActionDTO($prId, $userId, $remarks);

        $mockPr = Mockery::mock(PurchaseRequest::class);

        // Expectations
        $repo->shouldReceive('find')
            ->once()
            ->with($prId)
            ->andReturn($mockPr);

        $repo->shouldReceive('loadForApprovalContext')
            ->twice() // Once before approve, once after (for sync)
            ->with($mockPr)
            ->andReturn($mockPr);

        $approvals->shouldReceive('approve')
            ->once()
            ->with($mockPr, $userId, $remarks);

        $syncWorkflow->shouldReceive('sync')
            ->once()
            ->with($mockPr);

        // Act
        $useCase->handle($dto);

        // Assert
        $this->assertTrue(true); // Verification via Mockery expectations
    }

    public function test_handle_throws_exception_if_pr_not_found()
    {
        $approvals = Mockery::mock(Approvals::class);
        $syncWorkflow = Mockery::mock(SyncPrWorkflow::class);
        $repo = Mockery::mock(PurchaseRequestRepository::class);

        $useCase = new ApprovePurchaseRequest($approvals, $syncWorkflow, $repo);

        $dto = new ApprovalActionDTO(999, 1, 'remarks');

        $repo->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturnNull();

        $this->expectException(ModelNotFoundException::class);

        $useCase->handle($dto);
    }
}
