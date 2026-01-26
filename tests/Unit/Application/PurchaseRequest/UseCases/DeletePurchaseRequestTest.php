<?php

use App\Application\PurchaseRequest\UseCases\DeletePurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;

afterEach(function () {
    Mockery::close();
});

test('handle deletes pr', function () {
    Event::fake();
    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());

    $repo = Mockery::mock(PurchaseRequestRepository::class);
    $useCase = new DeletePurchaseRequest($repo);

    $mockPr = Mockery::mock(PurchaseRequest::class)->makePartial();
    $mockPr->status = 1;
    $mockPr->shouldReceive('delete')->once()->andReturn(true);

    $repo->shouldReceive('find')->with(1)->andReturn($mockPr);

    $result = $useCase->handle(1, 1);

    expect($result)->toBeTrue();
});
