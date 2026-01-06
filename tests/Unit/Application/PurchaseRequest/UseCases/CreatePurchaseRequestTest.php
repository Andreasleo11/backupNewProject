<?php

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Domain\PurchaseRequest\Services\PurchaseRequestNumberGenerator;
use App\Events\PurchaseRequestCreated;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class CreatePurchaseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_creates_pr_generates_number_and_dispatches_event()
    {
        Event::fake([PurchaseRequestCreated::class]);
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $repo = Mockery::mock(PurchaseRequestRepository::class);
        $approvals = Mockery::mock(Approvals::class);
        $generator = Mockery::mock(PurchaseRequestNumberGenerator::class);
        $statusCalculator = Mockery::mock(\App\Domain\PurchaseRequest\Services\PurchaseRequestStatusCalculator::class);
        $typeResolver = Mockery::mock(\App\Domain\PurchaseRequest\Services\PurchaseRequestTypeResolver::class);
        $contextBuilder = Mockery::mock(\App\Domain\PurchaseRequest\Services\PurchaseRequestApprovalContextBuilder::class);

        $dto = new CreatePurchaseRequestDTO(
            requestedByUserId: 1,
            fromDepartment: 'COMPUTER',
            toDepartment: 'MAINTENANCE',
            branch: 'JAKARTA',
            datePr: '2024-01-01',
            dateRequired: '2024-01-10',
            remark: 'Test Remark',
            supplier: 'Test Supplier',
            pic: 'Test PIC',
            isDraft: false,
            isImport: false,
            items: []
        );

        $mockPr = Mockery::mock(PurchaseRequest::class)->makePartial();
        $mockPr->id = 123;
        $mockPr->from_department = 'COMPUTER';
        $mockPr->to_department = \App\Enums\ToDepartment::MAINTENANCE; // Ensure enum is used if model casts it
        $mockPr->branch = 'JAKARTA';
        $mockPr->type = 'office';
        $mockPr->items = new \Illuminate\Database\Eloquent\Collection([]); // For item access
        $mockPr->approvalRequest = null;
        $mockPr->shouldReceive('saveQuietly')->once();

        // 1. Resolve Type
        $typeResolver->shouldReceive('resolve')
            ->once()
            ->with('COMPUTER')
            ->andReturn('office');

        // 2. Calculate Status
        $statusCalculator->shouldReceive('calculateInitialStatus')
            ->once()
            ->with('COMPUTER', 'JAKARTA', false)
            ->andReturn(1);

        // 3. Generate Doc Num
        $generator->shouldReceive('generateDocNum')
            ->once()
            ->andReturn('CP/PR/JKT/240101/001');

        // 4. Create in Repo
        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['doc_num'] === 'CP/PR/JKT/240101/001'
                    && $arg['status'] === 1
                    && $arg['type'] === 'office';
            }))
            ->andReturn($mockPr);

        // 5. Generate PR No and update
        $generator->shouldReceive('generatePrNo')
            ->once()
            ->with('MAINTENANCE', 123)
            ->andReturn('MAIN-123');

        $repo->shouldReceive('addItems')->once();

        // 6. Approval Context
        $repo->shouldReceive('loadForApprovalContext')->andReturn($mockPr);

        $contextBuilder->shouldReceive('build')
            ->once()
            ->andReturn(['some' => 'context']);

        $approvals->shouldReceive('submit')
            ->once()
            ->with($mockPr, 1, ['some' => 'context'])
            ->andReturn(new \App\Application\Approval\DTOs\ApprovalInfo(1, 'pending', 1));

        $useCase = new CreatePurchaseRequest(
            $repo,
            $approvals,
            $generator,
            $statusCalculator,
            $typeResolver,
            $contextBuilder
        );
        $result = $useCase->handle($dto);

        $this->assertSame($mockPr, $result);

        Event::assertDispatched(PurchaseRequestCreated::class);
    }
}
