<?php

declare(strict_types=1);

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests;
use App\Jobs\ProcessPurchaseRequestRejection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BatchRejectPurchaseRequestsTest extends TestCase
{
    use RefreshDatabase;

    private BatchRejectPurchaseRequests $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = new BatchRejectPurchaseRequests;
    }

    /** @test */
    public function it_returns_error_when_no_ids_provided(): void
    {
        $result = $this->useCase->handle([], 1, 'Too expensive');

        $this->assertFalse($result['success']);
        $this->assertEquals('No purchase requests selected for rejection.', $result['message']);
        $this->assertNull($result['batch_id']);
        $this->assertEquals(0, $result['rejected']);
    }

    /** @test */
    public function it_returns_error_when_reason_is_empty(): void
    {
        $result = $this->useCase->handle([1], 1, '   ');

        $this->assertFalse($result['success']);
        $this->assertEquals('Rejection reason is required.', $result['message']);
        $this->assertNull($result['batch_id']);
    }

    /** @test */
    public function it_dispatches_a_batch_of_rejection_jobs(): void
    {
        Bus::fake();

        $result = $this->useCase->handle([1, 2], 10, 'Out of budget');

        $this->assertTrue($result['success']);
        $this->assertEquals('Batch rejection started in the background.', $result['message']);
        $this->assertNotNull($result['batch_id']);

        Bus::assertBatched(function ($batch) {
            return $batch->name === 'Batch PR Rejection' &&
                   $batch->jobs->count() === 2 &&
                   $batch->jobs[0] instanceof ProcessPurchaseRequestRejection &&
                   $batch->jobs[0]->purchaseRequestId === 1 &&
                   $batch->jobs[0]->rejectionReason === 'Out of budget';
        });
    }
}
