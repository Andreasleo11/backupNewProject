<?php

declare(strict_types=1);

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest;
use App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BatchApprovePurchaseRequestsTest extends TestCase
{
    use RefreshDatabase;

    private BatchApprovePurchaseRequests $useCase;
    private ApprovePurchaseRequest $mockApprovePR;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApprovePR = $this->createMock(ApprovePurchaseRequest::class);
        $this->useCase = new BatchApprovePurchaseRequests($this->mockApprovePR);
    }

    /** @test */
    public function it_returns_error_when_no_ids_provided(): void
    {
        $result = $this->useCase->handle([], 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('No purchase requests selected for approval.', $result['message']);
        $this->assertEquals(0, $result['approved']);
        $this->assertEquals(0, $result['failed']);
    }

    /** @test */
    public function it_successfully_approves_single_purchase_request(): void
    {
        $this->mockApprovePR
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ApprovalActionDTO $dto) {
                return $dto->purchaseRequestId === 1 && $dto->actorUserId === 10;
            }));

        $result = $this->useCase->handle([1], 10);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['approved']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_successfully_approves_multiple_purchase_requests(): void
    {
        $this->mockApprovePR
            ->expects($this->exactly(3))
            ->method('handle');

        $result = $this->useCase->handle([1, 2, 3], 10);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['approved']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_handles_partial_failures_gracefully(): void
    {
        $this->mockApprovePR
            ->expects($this->exactly(3))
            ->method('handle')
            ->willReturnCallback(function (ApprovalActionDTO $dto) {
                if ($dto->purchaseRequestId === 2) {
                    throw new \DomainException('Cannot approve PR #2');
                }
            });

        $result = $this->useCase->handle([1, 2, 3], 10);

        $this->assertTrue($result['success']); // Still success because 2 out of 3 passed
        $this->assertEquals(2, $result['approved']);
        $this->assertEquals(1, $result['failed']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('PR #2', $result['errors'][0]);
    }

    /** @test */
    public function it_returns_failure_when_all_approvals_fail(): void
    {
        $this->mockApprovePR
            ->expects($this->exactly(2))
            ->method('handle')
            ->willThrowException(new \DomainException('Authorization failed'));

        $result = $this->useCase->handle([1, 2], 10);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['approved']);
        $this->assertEquals(2, $result['failed']);
        $this->assertCount(2, $result['errors']);
    }

    /** @test */
    public function it_rolls_back_transaction_on_error(): void
    {
        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockApprovePR
            ->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function (ApprovalActionDTO $dto) {
                if ($dto->purchaseRequestId === 2) {
                    throw new \Exception('Database error');
                }
            });

        $this->useCase->handle([1, 2], 10);
    }

    /** @test */
    public function it_includes_remarks_when_provided(): void
    {
        $this->mockApprovePR
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ApprovalActionDTO $dto) {
                return $dto->remarks === 'Approved by director';
            }));

        $this->useCase->handle([1], 10, 'Approved by director');
    }
}
