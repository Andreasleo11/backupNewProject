<?php

declare(strict_types=1);

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests;
use App\Jobs\ProcessPurchaseRequestApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BatchApprovePurchaseRequestsTest extends TestCase
{
    use RefreshDatabase;

    private BatchApprovePurchaseRequests $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->useCase = new BatchApprovePurchaseRequests();
    }

    /** @test */
    public function it_returns_error_when_no_ids_provided(): void
    {
        $result = $this->useCase->handle([], 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('No purchase requests selected for approval.', $result['message']);
        $this->assertNull($result['batch_id']);
        $this->assertEquals(0, $result['approved']);
    }

    /** @test */
    public function it_dispatches_a_batch_of_approval_jobs(): void
    {
        Bus::fake();

        $result = $this->useCase->handle([1, 2, 3], 10, 'Approved by director');

        $this->assertTrue($result['success']);
        $this->assertEquals('Batch approval started in the background.', $result['message']);
        $this->assertNotNull($result['batch_id']);

        Bus::assertBatched(function ($batch) {
            return $batch->name === 'Batch PR Approval' &&
                   $batch->jobs->count() === 3 &&
                   $batch->jobs[0] instanceof ProcessPurchaseRequestApproval &&
                   $batch->jobs[0]->purchaseRequestId === 1 &&
                   $batch->jobs[0]->remarks === 'Approved by director';
        });
    }
}
