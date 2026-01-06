<?php

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\CancelPurchaseRequestDTO;
use App\Application\PurchaseRequest\UseCases\CancelPurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class CancelPurchaseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_cancels_pr()
    {
        Event::fake();
        DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());

        $repo = Mockery::mock(PurchaseRequestRepository::class);
        $useCase = new CancelPurchaseRequest($repo);
        $dto = new CancelPurchaseRequestDTO(1, 1, 'Reason');

        $mockPr = Mockery::mock(PurchaseRequest::class)->makePartial();
        $mockPr->status = 1;
        $mockPr->shouldReceive('update')->once();
        $mockPr->shouldReceive('fresh')->andReturn($mockPr);

        $repo->shouldReceive('find')->with(1)->andReturn($mockPr);

        $useCase->handle($dto);

        $this->assertTrue(true);
    }
}
