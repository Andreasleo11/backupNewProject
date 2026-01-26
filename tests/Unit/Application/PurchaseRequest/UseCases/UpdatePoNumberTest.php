<?php

use App\Application\PurchaseRequest\DTOs\UpdatePoNumberDTO;
use App\Application\PurchaseRequest\UseCases\UpdatePoNumber;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;

afterEach(function () {
    Mockery::close();
});

test('handle updates po number', function () {
    Event::fake();
    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());

    $repo = Mockery::mock(PurchaseRequestRepository::class);
    $useCase = new UpdatePoNumber($repo);
    $dto = new UpdatePoNumberDTO(1, 'PO-123', 1);

    $mockPr = Mockery::mock(PurchaseRequest::class)->makePartial();
    $mockPr->status = 4; // Approved
    $mockPr->shouldReceive('update')->once();
    $mockPr->shouldReceive('fresh')->andReturn($mockPr);

    $repo->shouldReceive('find')->with(1)->andReturn($mockPr);

    $useCase->handle($dto);

    expect(true)->toBeTrue();
});
