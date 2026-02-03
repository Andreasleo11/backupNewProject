<?php

declare(strict_types=1);

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BatchRejectPurchaseRequestsTest extends TestCase
{
    use RefreshDatabase;

    private BatchRejectPurchaseRequests $useCase;

    private RejectPurchaseRequest $mockRejectPR;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRejectPR = $this->createMock(RejectPurchaseRequest::class);
        $this->useCase = new BatchRejectPurchaseRequests($this->mockRejectPR);
    }

    /** @test */
    public function it_returns_error_when_no_ids_provided(): void
    {
        $result = $this->useCase->handle([], 1, 'Test reason');

        $this->assertFalse($result['success']);
        $this->assertEquals('No purchase requests selected for rejection.', $result['message']);
        $this->assertEquals(0, $result['rejected']);
        $this->assertEquals(0, $result['failed']);
    }

    /** @test */
    public function it_returns_error_when_rejection_reason_is_empty(): void
    {
        $result = $this->useCase->handle([1], 1, '');

        $this->assertFalse($result['success']);
        $this->assertEquals('Rejection reason is required.', $result['message']);
        $this->assertCount(1, $result['errors']);
    }

    /** @test */
    public function it_returns_error_when_rejection_reason_is_whitespace(): void
    {
        $result = $this->useCase->handle([1], 1, '   ');

        $this->assertFalse($result['success']);
        $this->assertEquals('Rejection reason is required.', $result['message']);
    }

    /** @test */
    public function it_successfully_rejects_single_purchase_request(): void
    {
        $this->mockRejectPR
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ApprovalActionDTO $dto) {
                return $dto->purchaseRequestId === 1
                    && $dto->actorUserId === 10
                    && $dto->remarks === 'Budget exceeded';
            }));

        $result = $this->useCase->handle([1], 10, 'Budget exceeded');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['rejected']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_successfully_rejects_multiple_purchase_requests(): void
    {
        $this->mockRejectPR
            ->expects($this->exactly(3))
            ->method('handle');

        $result = $this->useCase->handle([1, 2, 3], 10, 'Not approved');

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['rejected']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_handles_partial_failures_gracefully(): void
    {
        $this->mockRejectPR
            ->expects($this->exactly(3))
            ->method('handle')
            ->willReturnCallback(function (ApprovalActionDTO $dto) {
                if ($dto->purchaseRequestId === 2) {
                    throw new \DomainException('Cannot reject PR #2');
                }
            });

        $result = $this->useCase->handle([1, 2, 3], 10, 'Test reason');

        $this->assertTrue($result['success']); // Still success because 2 out of 3 passed
        $this->assertEquals(2, $result['rejected']);
        $this->assertEquals(1, $result['failed']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('PR #2', $result['errors'][0]);
    }

    /** @test */
    public function it_returns_failure_when_all_rejections_fail(): void
    {
        $this->mockRejectPR
            ->expects($this->exactly(2))
            ->method('handle')
            ->willThrowException(new \DomainException('Authorization failed'));

        $result = $this->useCase->handle([1, 2], 10, 'Test reason');

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['rejected']);
        $this->assertEquals(2, $result['failed']);
        $this->assertCount(2, $result['errors']);
    }

    /** @test */
    public function it_rolls_back_transaction_on_error(): void
    {
        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->once();

        $this->mockRejectPR
            ->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function (ApprovalActionDTO $dto) {
                if ($dto->purchaseRequestId === 2) {
                    throw new \Exception('Database error');
                }
            });

        $this->useCase->handle([1, 2], 10, 'Test reason');
    }

    /** @test */
    public function it_provides_detailed_error_messages(): void
    {
        $this->mockRejectPR
            ->method('handle')
            ->willThrowException(new \DomainException('Specific error message'));

        $result = $this->useCase->handle([5], 10, 'Test reason');

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('PR #5', $result['errors'][0]);
        $this->assertStringContainsString('Specific error message', $result['errors'][0]);
    }
}
